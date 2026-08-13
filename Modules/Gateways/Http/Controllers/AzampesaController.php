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

class AzampesaController extends Controller
{
    use Processor;

    private mixed $config_values;
    private string $base_url;
    private string $token_url;
    private PaymentRequest $payment;

    public function __construct(PaymentRequest $payment)
    {
        $config = $this->paymentConfig('azampesa', PAYMENT_CONFIG);
        if (!is_null($config) && $config->mode == 'live') {
            $this->config_values = json_decode($config->live_values);
            $this->base_url = 'https://api.azampay.co.tz';
            $this->token_url = 'https://api.azampay.co.tz/azampay-jwt/v1/auth/login';
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->config_values = json_decode($config->test_values);
            $this->base_url = 'https://sandbox.azampay.co.tz';
            $this->token_url = 'https://sandbox.azampay.co.tz/azampay-jwt/v1/auth/login';
        }
        $this->payment = $payment;
    }

    private function getAccessToken(): ?string
    {
        if (!isset($this->config_values)) {
            return null;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->token_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'appName' => $this->config_values->app_name,
            'clientId' => $this->config_values->client_id,
            'clientSecret' => $this->config_values->client_secret,
        ]));

        $headers = [];
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            return null;
        }
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['data']['accessToken'] ?? null;
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

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return response()->json($this->responseFormatter(DEFAULT_204), 200);
        }

        $payer_information = json_decode($data['payer_information']);
        $payment_amount = $data['payment_amount'];
        $currency = $data['currency_code'] ?? 'TZS';

        $provider = $this->config_values->provider ?? 'Azampesa';
        $account_number = $payer_information->phone ?? '0000000000';
        $external_id = $data['id'];

        $post_data = [
            'accountNumber' => $account_number,
            'amount' => round($payment_amount, 2),
            'currency' => $currency,
            'externalId' => $external_id,
            'provider' => $provider,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/azampay/mno/checkout');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));

        $headers = [];
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer ' . $accessToken;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            return response()->json($this->responseFormatter(DEFAULT_204), 200);
        }
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['success']) && $result['success']) {
            $this->payment::where(['id' => $request['payment_id']])->update([
                'payment_method' => 'azampesa',
                'is_paid' => 1,
                'transaction_id' => $result['transactionReference'] ?? $external_id,
            ]);

            $data = $this->payment::where(['id' => $request['payment_id']])->first();
            if (isset($data) && function_exists($data->hook)) {
                call_user_func($data->hook, $data);
            }
            return $this->paymentResponse($data, 'success');
        }

        $data = $this->payment::where(['id' => $request['payment_id']])->first();
        return $this->paymentResponse($data, 'fail');
    }

    public function callback(Request $request): JsonResponse|Redirector|RedirectResponse|Application
    {
        $payment_id = $request['payment_id'] ?? $request['externalId'];
        $data = $this->payment::where(['id' => $payment_id])->first();

        if ($data && isset($request['success']) && $request['success']) {
            $this->payment::where(['id' => $payment_id])->update([
                'payment_method' => 'azampesa',
                'is_paid' => 1,
                'transaction_id' => $request['transactionReference'] ?? $payment_id,
            ]);

            $data = $this->payment::where(['id' => $payment_id])->first();
            if (isset($data) && function_exists($data->hook)) {
                call_user_func($data->hook, $data);
            }
            return $this->paymentResponse($data, 'success');
        }

        return $this->paymentResponse($data, 'fail');
    }
}
