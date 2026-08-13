<?php

namespace Modules\BusinessManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SmsSettingSetupStoreOrUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'gateway' => 'required|in:twilio,mshastra_sms',
            'mode' => 'required|in:live,test',
            'status' => [
                'required_if:gateway,twilio,mshastra_sms',
                Rule::in([1, 0])
            ],
            #twilio
            'sid' => [
                Rule::requiredIf(function () {
                    return ($this->input('status') == 1 && $this->input('gateway') == 'twilio');
                })
            ],
            'messaging_service_sid' => [
                Rule::requiredIf(function () {
                    return ($this->input('status') == 1 && $this->input('gateway') == 'twilio');
                })
            ],
            'token' => [
                Rule::requiredIf(function () {
                    return ($this->input('status') == 1 && $this->input('gateway') == 'twilio');
                })
            ],
            'from' => [
                Rule::requiredIf(function () {
                    return ($this->input('status') == 1 && $this->input('gateway') == 'twilio');
                })
            ],
            'otp_template' => [
                Rule::requiredIf(function () {
                    return ($this->input('status') == 1 && $this->input('gateway') == 'twilio');
                })
            ],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }
}
