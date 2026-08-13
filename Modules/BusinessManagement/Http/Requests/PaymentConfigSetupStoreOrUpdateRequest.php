<?php

namespace Modules\BusinessManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PaymentConfigSetupStoreOrUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'gateway' => 'required|in:azampesa,selcom,cash_after_service,digital_payment',
            'mode' => 'required|in:live,test',
            'gateway_image' => 'nullable|mimes:png|max:' . convertBytesToKiloBytes(maxUploadSize('image')),
            'gateway_title' => Rule::requiredIf(function () {
                return $this->input('status') == 1;
            }),
            'status' => [
                'required_if:gateway,azampesa,selcom,cash_after_service,digital_payment',
                Rule::in([1, 0])
            ],
            #Azampesa
            'app_name' => [
                Rule::requiredIf(function () {
                    return ($this->input('status') == 1 && $this->input('gateway') == 'azampesa');
                })
            ],
            'client_id' => [
                Rule::requiredIf(function () {
                    return ($this->input('status') == 1 && $this->input('gateway') == 'azampesa');
                })
            ],
            'client_secret' => [
                Rule::requiredIf(function () {
                    return ($this->input('status') == 1 && $this->input('gateway') == 'azampesa');
                })
            ],
            'provider' => [
                Rule::requiredIf(function () {
                    return ($this->input('status') == 1 && $this->input('gateway') == 'azampesa');
                })
            ],
            #Selcom
            'api_key' => [
                Rule::requiredIf(function () {
                    return ($this->input('status') == 1 && $this->input('gateway') == 'selcom');
                })
            ],
            'api_secret' => [
                Rule::requiredIf(function () {
                    return ($this->input('status') == 1 && $this->input('gateway') == 'selcom');
                })
            ],
            'vendor' => [
                Rule::requiredIf(function () {
                    return ($this->input('status') == 1 && $this->input('gateway') == 'selcom');
                })
            ],
        ];
    }

    public function messages()
    {
        return [
            'gateway_image.max' => translate(key: 'The Gateway Image must be less than {maxSize}', replace: ['maxSize' => readableUploadMaxFileSize('image')])
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

    protected function prepareForValidation()
    {
        showValidationMessageForUploadMaxSize(files: $this->allFiles(), isAjax: $this->ajax(), doesExpectJson: $this->expectsJson());
    }
}
