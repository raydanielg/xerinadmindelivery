<?php

namespace App\Http\Controllers;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Service\Interfaces\UserServiceInterface;

class AccountDeletionController extends Controller
{
    protected $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $businessName = businessConfig(key: 'business_name', settingsType: BUSINESS_INFORMATION)?->value ?? null;
        return view('landing-page.account-deletion', compact('businessName'));
    }

    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_or_phone' => 'required|string',
            'reason' => 'nullable|string|max:500',
            'confirm' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $input = $request->input('email_or_phone');
        $user = User::where('email', $input)
            ->orWhere('phone', $input)
            ->first();

        if (!$user) {
            Toastr::error(translate('No account found with this email or phone number.'));
            return redirect()->back()->withInput();
        }

        if ($user->deleted_at) {
            Toastr::info(translate('This account has already been deleted.'));
            return redirect()->back();
        }

        try {
            $user->deleted_at = now();
            $user->fcm_token = null;
            $user->save();

            $adminEmail = businessConfig(key: 'business_contact_email', settingsType: BUSINESS_INFORMATION)?->value ?? config('mail.from.address');
            try {
                Mail::raw(
                    "Account deletion request:\n\nUser: {$user->name}\nEmail: {$user->email}\nPhone: {$user->phone}\nReason: " . ($request->input('reason') ?? 'N/A'),
                    function ($message) use ($adminEmail) {
                        $message->to($adminEmail)->subject('Account Deletion Request');
                    }
                );
            } catch (\Exception $e) {
                Log::warning('Account deletion email failed: ' . $e->getMessage());
            }

            Toastr::success(translate('Your account deletion request has been processed successfully. Your data will be permanently removed within 30 days.'));
            return redirect()->back();
        } catch (\Exception $e) {
            Log::error('Account deletion failed: ' . $e->getMessage());
            Toastr::error(translate('Something went wrong. Please try again or contact support.'));
            return redirect()->back()->withInput();
        }
    }
}
