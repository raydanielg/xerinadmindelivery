<?php

namespace Modules\Gateways\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Validator;
use Modules\Gateways\Traits\Processor;
use Modules\Gateways\Entities\PaymentRequest;

class SelcomController extends Controller
{
    use Processor;

    private mixed $config_values;
    private string $base_url;
    private PaymentRequest $payment;

    public function __construct(PaymentRequest $payment)
    {
        $config = $this->paymentConfig('selcom', PAYMENT_CONFIG);
        if (!is_null($config) && $config->mode == 'live') {
            $this->config_values = json_decode($config->live_values);
            $this->base_url = 'https://apigw.selcommobile.com';
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->config_values = json_decode($config->test_values);
            $this->base_url = 'https://apisandbox.selcommobile.com';
        }
        $this->payment = $payment;
    }

    private function generateDigest(array $data, string $apiSecret): string
    {
        $fields = array_keys($data);
        $signedFields = implode(',', $fields);

        $stringToSign = '';
        foreach ($fields as $field) {
            $stringToSign .= $data[$field] . ',';
        }
        $stringToSign = rtrim($stringToSign, ',');

        return base64_encode(hash_hmac('sha256', $stringToSign, $apiSecret, true));
    }

    private function generateHeaders(array $data): array
    {
        $apiKey = $this->config_values->api_key ?? '';
        $apiSecret = $this->config_values->api_secret ?? '';

        $timestamp = date('Y-m-d\TH:i:sP');
        $digest = $this->generateDigest($data, $apiSecret);
        $signedFields = implode(',', array_keys($data));

        $authorization = 'SELCOM ' . base64_encode($apiKey);

        return [
            'Content-Type: application/json',
            'Authorization: ' . $authorization,
            'Digest-Method: HS256',
            'Digest: ' . $digest,
            'Timestamp: ' . $timestamp,
            'Signed-Fields: ' . $signedFields,
        ];
    }

    public function index(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json($this->responseFormatter(DEFAULT_400, null, $this->errorProcessor($validator)), 400);
        }

        $data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (!isset($data)) {
            return response()->json($this->responseFormatter(DEFAULT_204), 200);
        }

        if (!isset($this->config_values)) {
            return response()->json($this->responseFormatter(DEFAULT_204), 200);
        }

        $payer_information = json_decode($data['payer_information']);
        $payment_amount = $data['payment_amount'];
        $currency = $data['currency_code'] ?? 'TZS';
        $vendor = $this->config_values->vendor ?? 'VENDORTILL';
        $order_id = $data['id'];

        $order_data = [
            'vendor' => $vendor,
            'order_id' => $order_id,
            'buyer_email' => $payer_information->email ?? 'customer@example.com',
            'buyer_name' => $payer_information->name ?? 'Customer',
            'buyer_phone' => $payer_information->phone ?? '255000000000',
            'amount' => (string) round($payment_amount, 2),
            'currency' => $currency,
            'redirect_url' => url('/') . '/payment/selcom/callback?payment_id=' . $order_id,
            'cancel_url' => url('/') . '/payment/selcom/cancel?payment_id=' . $order_id,
            'webhook' => url('/') . '/payment/selcom/webhook?payment_id=' . $order_id,
            'buyer_remarks' => 'None',
            'merchant_remarks' => 'None',
            'no_of_items' => '1',
        ];

        $headers = $this->generateHeaders($order_data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/v1/checkout/create-order-minimal');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            \Log::error('Selcom API curl error', ['error' => curl_error($ch)]);
            curl_close($ch);
            return response()->json($this->responseFormatter(DEFAULT_204), 200);
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        \Log::info('Selcom create-order response', [
            'http_code' => $httpCode,
            'response' => $response,
        ]);

        if (isset($result['resultcode']) && $result['resultcode'] === '000') {
            $payment_gateway_url = $result['data'][0]['payment_gateway_url'] ?? null;
            if ($payment_gateway_url) {
                return redirect()->away($payment_gateway_url);
            }
        }

        $data = $this->payment::where(['id' => $request['payment_id']])->first();
        return $this->paymentResponse($data, 'fail');
    }

    public function callback(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        $payment_id = $request['payment_id'] ?? null;
        $data = $this->payment::where(['id' => $payment_id])->first();

        if ($data) {
            $order_status = $this->getOrderStatus($payment_id);
            if ($order_status && isset($order_status['resultcode']) && $order_status['resultcode'] === '000') {
                $this->payment::where(['id' => $payment_id])->update([
                    'payment_method' => 'selcom',
                    'is_paid' => 1,
                    'transaction_id' => $order_status['reference'] ?? $payment_id,
                ]);

                $data = $this->payment::where(['id' => $payment_id])->first();
                if (isset($data) && function_exists($data->hook)) {
                    call_user_func($data->hook, $data);
                }
                return $this->paymentResponse($data, 'success');
            }
        }

        return $this->paymentResponse($data, 'fail');
    }

    public function cancel(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        $payment_id = $request['payment_id'] ?? null;
        $data = $this->payment::where(['id' => $payment_id])->first();
        return $this->paymentResponse($data, 'cancel');
    }

    public function webhook(Request $request): JsonResponse
    {
        $payment_id = $request['payment_id'] ?? $request['order_id'] ?? null;
        $data = $this->payment::where(['id' => $payment_id])->first();

        if ($data) {
            $order_status = $this->getOrderStatus($payment_id);
            if ($order_status && isset($order_status['resultcode']) && $order_status['resultcode'] === '000') {
                $this->payment::where(['id' => $payment_id])->update([
                    'payment_method' => 'selcom',
                    'is_paid' => 1,
                    'transaction_id' => $order_status['reference'] ?? $payment_id,
                ]);

                $data = $this->payment::where(['id' => $payment_id])->first();
                if (isset($data) && function_exists($data->hook)) {
                    call_user_func($data->hook, $data);
                }
            }
        }

        return response()->json(['result' => 'SUCCESS']);
    }

    private function getOrderStatus(string $order_id): ?array
    {
        if (!isset($this->config_values)) {
            return null;
        }

        $data = ['order_id' => $order_id];
        $headers = $this->generateHeaders($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/v1/checkout/order-status?order_id=' . $order_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            return null;
        }
        curl_close($ch);

        return json_decode($response, true);
    }
}
