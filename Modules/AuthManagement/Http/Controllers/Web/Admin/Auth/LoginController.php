<?php

namespace Modules\AuthManagement\Http\Controllers\Web\Admin\Auth;

use App\Http\Controllers\BaseController;
use Brian2694\Toastr\Facades\Toastr;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Modules\BusinessManagement\Service\Interfaces\ExternalConfigurationServiceInterface;
use Modules\UserManagement\Service\Interfaces\EmployeeServiceInterface;

class LoginController extends BaseController
{
    protected $employeeService;
    protected $externalConfigurationService;

    public function __construct(EmployeeServiceInterface $employeeService, ExternalConfigurationServiceInterface $externalConfigurationService)
    {
        parent::__construct($employeeService);
        $this->employeeService = $employeeService;
        $this->externalConfigurationService = $externalConfigurationService;
        $this->middleware(function ($request, $next) {
            if (auth()->check()) {
                return redirect(route('admin.dashboard'));
            }
            return $next($request);
        })->except('logout');
    }

    /**
     * @return Renderable
     */

    public function loginView(): Renderable
    {
        return view('authmanagement::login');
    }

    public function login(Request $request)
    {
        try {
            $user = $this->employeeService->findOneBy(criteria: ['email' => $request['email']]);
        } catch (\Exception $e) {
            Toastr::error(NO_DATA_200['message']);
            return back();
        }

        if (isset($user) && Hash::check($request['password'], $user->password)) {
            if (($user && $user->is_active  && $user?->role?->is_active) || $user->user_type === 'super-admin') {
                $remember = $request->has('remember');
                if (auth()->attempt(['email' => $request['email'], 'password' => $request['password']], $remember)) {
                    if ($remember) {
                        cookie()->queue('remember_email', $request->email, 43200);
                        cookie()->queue('remember_checked', true, 43200);
                    } else {
                        cookie()->queue(cookie()->forget('remember_email'));
                        cookie()->queue(cookie()->forget('remember_checked'));
                    }
                    Toastr::success(AUTH_LOGIN_200['message']);
                    return redirect()->route('admin.dashboard');
                }
            }
            Toastr::error(ACCOUNT_DISABLED['message']);
            return back();
        }
        Toastr::error(AUTH_LOGIN_401['message']);
        return back();
    }

    public function externalLoginFromMart(Request $request)
    {
        return back();
    }

    public function logout()
    {
        if (auth()->user()) {
            auth()->guard('web')->logout();
            Toastr::success(AUTH_LOGOUT_200['message']);
            return redirect(route('admin.auth.login'));
        }
        return redirect()->back();
    }
    public function captcha($tmp): void
    {

        $phrase = new PhraseBuilder;
        $code = $phrase->build(4);
        $builder = new CaptchaBuilder($code, $phrase);
        $builder->setBackgroundColor(220, 210, 230);
        $builder->setMaxAngle(25);
        $builder->setMaxBehindLines(0);
        $builder->setMaxFrontLines(0);
        $builder->build($width = 100, $height = 40, $font = null);
        $phrase = $builder->getPhrase();

        if (Session::has('default_captcha_code')) {
            Session::forget('default_captcha_code');
        }
        Session::put('default_captcha_code', $phrase);
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type:image/jpeg");
        $builder->output();
    }
}
