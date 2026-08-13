<?php

namespace Modules\BusinessManagement\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\BusinessManagement\Service\Interfaces\BusinessSettingServiceInterface;
use Modules\BusinessManagement\Service\Interfaces\ExternalConfigurationServiceInterface;

class ConfigurationController extends Controller
{
    protected $externalConfigurationService;
    protected $businessSettingService;

    public function __construct(ExternalConfigurationServiceInterface $externalConfigurationService,BusinessSettingServiceInterface $businessSettingService)
    {
        $this->externalConfigurationService = $externalConfigurationService;
        $this->businessSettingService = $businessSettingService;
    }

    public function getConfiguration()
    {
        $cta = $this->businessSettingService->findOneBy(criteria: ['key_name' => CTA, 'settings_type' => LANDING_PAGES_SETTINGS]);

        $configs = [
            'business_name' => businessConfig('business_name', BUSINESS_INFORMATION)?->value ?? "Xerin Express Delivery",
            'logo' => businessConfig('header_logo', BUSINESS_INFORMATION)?->value ? asset(businessConfig('header_logo', BUSINESS_INFORMATION)?->value) : dynamicAsset('public/assets/admin-module/img/logo.png'),
            'app_url_android' => $cta?->value && $cta?->value['play_store']['user_download_link'] ? $cta?->value['play_store']['user_download_link'] : "",
            'app_url_ios' => $cta?->value && $cta?->value['app_store']['user_download_link'] ? $cta?->value['app_store']['user_download_link'] : "",
        ];
        return response()->json($configs);
    }

    public function updateConfiguration(Request $request)
    {
        $this->externalConfigurationService->updateExternalConfiguration(data: $request->all());
        return response()->json(['message' => 'Configuration updated successfully.']);
    }

    public function getExternalConfiguration(Request $request)
    {
        return response()->json(['status' => false]);
    }

}
