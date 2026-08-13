<?php

namespace Modules\Gateways\Traits;

use InvalidArgumentException;
use Modules\Gateways\Entities\PaymentRequest;

trait Payment
{
    public function generate_link(object $payer, object $paymentInfo, Object $receiver)
    {
        if ($paymentInfo->getPaymentAmount() === 0) {
            throw new InvalidArgumentException('Payment amount can not be 0');
        }

        if (!in_array(strtoupper($paymentInfo->getCurrencyCode()), array_column(CURRENCIES, 'code'))) {
            throw new InvalidArgumentException('Need a valid currency code');
        }

        if (!in_array($paymentInfo->getPaymentMethod(), array_column(PAYMENT_METHODS, 'key'))) {
            throw new InvalidArgumentException('Need a valid payment gateway');
        }

        if (!is_array($paymentInfo->getAdditionalData())) {
            throw new InvalidArgumentException('Additional data should be in a valid array');
        }

        $payment = new PaymentRequest();
        $payment->payment_amount = $paymentInfo->getPaymentAmount();
        $payment->hook = $paymentInfo->getHook();
        $payment->payer_id = $paymentInfo->getPayerId();
        $payment->receiver_id = $paymentInfo->getReceiverId();
        $payment->currency_code = strtoupper($paymentInfo->getCurrencyCode());
        $payment->payment_method = $paymentInfo->getPaymentMethod();
        $payment->additional_data = json_encode($paymentInfo->getAdditionalData());
        $payment->payer_information = json_encode($payer->information());
        $payment->receiver_information = json_encode($receiver->information());
        $payment->external_redirect_link = $paymentInfo->getExternalRedirectLink();
        $payment->attribute = $paymentInfo->getAttribute();
        $payment->attribute_id = $paymentInfo->getAttributeId();
        $payment->payment_platform = $paymentInfo->getPaymentPlatForm();
        $payment->save();

        if ($payment->payment_method == 'azampesa') {
            return url("payment/azampesa/pay/?payment_id={$payment->id}");
        }else if($payment->payment_method == 'selcom'){
            return url("payment/selcom/pay/?payment_id={$payment->id}");
        }
        return false;
    }
}
