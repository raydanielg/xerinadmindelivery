<?php

namespace Modules\UserManagement\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Modules\UserManagement\Service\Interfaces\CustomerAccountServiceInterface;
use Modules\UserManagement\Service\Interfaces\CustomerServiceInterface;

class WalletTransferController extends Controller
{
    protected $customerService;
    protected $customerAccountService;

    public function __construct(CustomerServiceInterface $customerService, CustomerAccountServiceInterface $customerAccountService)
    {
        $this->customerService = $customerService;
        $this->customerAccountService = $customerAccountService;
    }

    public function transferXerin Express DeliveryToMartWallet(Request $request)
    {
        return response()->json(responseFormatter([
            'response_code' => 'feature_disabled_403',
            'message' => 'This feature is not available.',
        ]), 403);
    }

    public function transferXerin Express DeliveryFromMartWallet(Request $request)
    {
        return response()->json([
            'status' => false,
            'data' => ['error_code' => 403, 'message' => 'This feature is not available.']
        ]);
    }
}
