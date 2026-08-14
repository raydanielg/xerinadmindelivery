<!-- Newsletters Section Start -->
@if(landingPageConfig(key: 'is_newsletter_enabled',settingsType: NEWSLETTER)?->value == 1 )
    @php($newsLetter = landingPageConfig(key: INTRO_CONTENTS,settingsType: NEWSLETTER)?->value ?? null)
    <section class="newsletter-section p-0 mt-4 mt-sm-60">
        <div class="container">
            <div class="newsletter--wrapper bg__img"
                 data-img="{{ $newsLetter && $newsLetter['background_image'] ? dynamicStorage('storage/app/public/business/landing-pages/newsletter/'.$newsLetter['background_image']) :dynamicAsset(path: 'public/landing-page/assets/img/newsletter-new-bg.png') }}">
                <div class="position-relative p-4 p-sm-5">
                    <div class="row g-4 align-items-center">
                        <div class="col-lg-8">
                            <div class="wow animate__fadeInDown">
                                <h4 class="text-white text-uppercase mb-2 fs-16-mobile">{!! $newsLetter && $newsLetter['title'] ? change_text_color_or_bg($newsLetter['title']) :  translate('GET ALL UPDATES & EXCITING NEWS') !!}</h4>
                                <p class="text-white opacity-75 lh-base fs-12-mobile">{!! $newsLetter && $newsLetter['subtitle'] ? change_text_color_or_bg($newsLetter['subtitle']) :translate('Subscribe to out newsletters to receive all the latest activity we provide for you') !!}</p>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="wow animate__fadeInUp">
                                <div class="newsletter-right">
                                    <form action="{{ route('newsletter-subscription.store') }}" method="POST" class="newsletter-form">
                                        @csrf
                                        <input type="email" class="form-control"
                                               placeholder="{{ translate('Type email...') }}" name="email" autocomplete="off" required>
                                        <button type="submit"
                                                class="btn cmn--btn">{{ translate('Subscribe ') }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
<!-- Newsletters Section End -->

@php($logo = getSession('header_logo'))
@php($footerLogo = getSession('footer_logo'))
@php($email = getSession('business_contact_email'))
@php($contactNumber = getSession('business_contact_phone'))
@php($businessAddress = getSession('business_address'))
@php($businessName = getSession('business_name'))
@php($footerContent = landingPageConfig(key: 'footer_contents', settingsType: FOOTER)?->value ?? null)
@php($links = \Modules\BusinessManagement\Entities\SocialLink::where(['is_active'=>1])->orderBy('name','asc')->get())
@php($driverAppVersionControlForAndroid = businessConfig(key: DRIVER_APP_VERSION_CONTROL_FOR_ANDROID, settingsType: APP_VERSION)?->value ?? null)
@php($driverAppVersionControlForIos = businessConfig(key: DRIVER_APP_VERSION_CONTROL_FOR_IOS, settingsType: APP_VERSION)?->value ?? null)
@php($customerAppVersionControlForAndroid = businessConfig(key: CUSTOMER_APP_VERSION_CONTROL_FOR_ANDROID, settingsType: APP_VERSION)?->value ?? null)
@php($customerAppVersionControlForIos = businessConfig(key: CUSTOMER_APP_VERSION_CONTROL_FOR_IOS, settingsType: APP_VERSION)?->value ?? null)

<style>
    .xe-footer {
        background: var(--footer);
        color: #e0e0e0;
        padding: 60px 0 0;
        margin-top: 60px;
    }
    .xe-footer-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr 1fr 1.2fr;
        gap: 40px;
        padding-bottom: 50px;
    }
    @media (max-width: 991px) {
        .xe-footer-grid {
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
    }
    @media (max-width: 575px) {
        .xe-footer-grid {
            grid-template-columns: 1fr;
            gap: 35px;
        }
    }
    .xe-footer-brand .logo img {
        max-width: 180px;
        margin-bottom: 20px;
    }
    .xe-footer-brand p {
        color: #b0b0b0;
        font-size: 14px;
        line-height: 1.7;
        margin-bottom: 24px;
        max-width: 320px;
    }
    .xe-footer-social {
        display: flex;
        gap: 12px;
        margin-bottom: 28px;
    }
    .xe-footer-social a {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255,255,255,0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    .xe-footer-social a:hover {
        background: var(--text-primary);
        transform: translateY(-3px);
    }
    .xe-footer-social a img {
        width: 18px;
        height: 18px;
    }
    .xe-footer-apps {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }
    .xe-footer-apps .app-group h6 {
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .xe-footer-apps .app-group .app-links {
        display: flex;
        gap: 8px;
        flex-direction: column;
    }
    .xe-footer-apps .app-group .app-links a {
        transition: transform 0.3s ease;
    }
    .xe-footer-apps .app-group .app-links a:hover {
        transform: translateY(-2px);
    }
    .xe-footer-col h5 {
        color: #fff;
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 20px;
        position: relative;
        padding-bottom: 12px;
    }
    .xe-footer-col h5::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 35px;
        height: 3px;
        background: var(--text-primary);
        border-radius: 2px;
    }
    .xe-footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .xe-footer-links li {
        margin-bottom: 12px;
    }
    .xe-footer-links li a {
        color: #b0b0b0;
        font-size: 14px;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .xe-footer-links li a::before {
        content: '\203A';
        color: var(--text-primary);
        font-weight: bold;
        font-size: 18px;
        transition: transform 0.3s ease;
    }
    .xe-footer-links li a:hover {
        color: #fff;
        padding-left: 5px;
    }
    .xe-footer-contact-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 20px;
    }
    .xe-footer-contact-item .icon-wrap {
        width: 42px;
        height: 42px;
        min-width: 42px;
        border-radius: 10px;
        background: rgba(255,255,255,0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.3s ease;
    }
    .xe-footer-contact-item .icon-wrap img {
        width: 20px;
        height: 20px;
    }
    .xe-footer-contact-item:hover .icon-wrap {
        background: var(--text-primary);
    }
    .xe-footer-contact-item .info h6 {
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .xe-footer-contact-item .info a,
    .xe-footer-contact-item .info span {
        color: #b0b0b0;
        font-size: 13px;
        text-decoration: none;
        transition: color 0.3s ease;
        word-break: break-word;
    }
    .xe-footer-contact-item .info a:hover {
        color: var(--text-primary);
    }
    .xe-footer-bottom {
        background: rgba(0,0,0,0.2);
        padding: 18px 0;
        text-align: center;
    }
    .xe-footer-bottom p {
        margin: 0;
        color: #b0b0b0;
        font-size: 13px;
    }
    .xe-footer-bottom p a {
        color: var(--text-primary);
        text-decoration: none;
    }
</style>

<footer class="xe-footer">
    <div class="container">
        <div class="xe-footer-grid">
            <!-- Brand Column -->
            <div class="xe-footer-brand">
                <a href="{{ route('index') }}" class="logo">
                    <img
                        src="{{ $footerLogo ? dynamicStorage(path: "storage/app/public/business/".$footerLogo) : dynamicAsset(path: 'public/landing-page/assets/img/logo.png') }}"
                        alt="logo">
                </a>
                <p>
                    {!! $footerContent && $footerContent['title'] ? change_text_color_or_bg($footerContent['title']) : translate('Connect with our social media and other sites to keep up to date')!!}
                </p>
                <div class="xe-footer-social">
                    @foreach($links as $link)
                        @if($link->name == "facebook")
                            <a href="{{$link->link}}" target="_blank" title="Facebook">
                                <img src="{{ dynamicAsset(path: 'public/landing-page/assets/img/footer/facebook.png') }}" alt="Facebook">
                            </a>
                        @elseif($link->name == "instagram")
                            <a href="{{$link->link}}" target="_blank" title="Instagram">
                                <img src="{{ dynamicAsset(path: 'public/landing-page/assets/img/footer/instagram.png') }}" alt="Instagram">
                            </a>
                        @elseif($link->name == "twitter")
                            <a href="{{$link->link}}" target="_blank" title="Twitter">
                                <img src="{{ dynamicAsset(path: 'public/landing-page/assets/img/footer/twitter.png') }}" alt="Twitter">
                            </a>
                        @elseif($link->name == "linkedin")
                            <a href="{{$link->link}}" target="_blank" title="LinkedIn">
                                <img src="{{ dynamicAsset(path: 'public/landing-page/assets/img/footer/linkedin.png') }}" alt="LinkedIn">
                            </a>
                        @endif
                    @endforeach
                </div>
                <div class="xe-footer-apps">
                    @if($customerAppVersionControlForAndroid || $customerAppVersionControlForIos)
                        <div class="app-group">
                            <h6>User App</h6>
                            <div class="app-links">
                                @if($customerAppVersionControlForAndroid)
                                    <a target="_blank" href="{{ $customerAppVersionControlForAndroid['app_url'] }}">
                                        <img src="{{ dynamicAsset(path: 'public/landing-page/assets/img/play-store.png') }}" class="w-115px" alt="Google Play">
                                    </a>
                                @endif
                                @if($customerAppVersionControlForIos)
                                    <a target="_blank" href="{{ $customerAppVersionControlForIos['app_url'] }}">
                                        <img src="{{ dynamicAsset(path: 'public/landing-page/assets/img/app-store.png') }}" class="w-115px" alt="App Store">
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                    @if($driverAppVersionControlForAndroid || $driverAppVersionControlForIos)
                        <div class="app-group">
                            <h6>Driver App</h6>
                            <div class="app-links">
                                @if($driverAppVersionControlForAndroid)
                                    <a target="_blank" href="{{ $driverAppVersionControlForAndroid['app_url'] }}">
                                        <img src="{{ dynamicAsset(path: 'public/landing-page/assets/img/play-store.png') }}" class="w-115px" alt="Google Play">
                                    </a>
                                @endif
                                @if($driverAppVersionControlForIos)
                                    <a target="_blank" href="{{ $driverAppVersionControlForIos['app_url'] }}">
                                        <img src="{{ dynamicAsset(path: 'public/landing-page/assets/img/app-store.png') }}" class="w-115px" alt="App Store">
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Links Column -->
            <div class="xe-footer-col">
                <h5>{{ translate('Quick Links') }}</h5>
                <ul class="xe-footer-links">
                    <li><a href="{{ route('index') }}">{{ translate('Home') }}</a></li>
                    <li><a href="{{ route('about-us') }}">{{ translate('About Us') }}</a></li>
                    <li><a href="{{ route('contact-us') }}">{{ translate('Contact Us') }}</a></li>
                    <li><a href="{{ route('privacy') }}">{{ translate('Privacy Policy') }}</a></li>
                    <li><a href="{{ route('terms') }}">{{ translate('Terms & Condition') }}</a></li>
                    <li><a href="{{ route('account-deletion') }}">{{ translate('Delete Account') }}</a></li>
                </ul>
            </div>

            <!-- Contact Info Column -->
            <div class="xe-footer-col">
                <h5>{{ translate('Get In Touch') }}</h5>
                <div class="xe-footer-contact-item">
                    <div class="icon-wrap">
                        <img src="{{ dynamicAsset(path: 'public/landing-page/assets/img/footer/mail.png') }}" alt="Email">
                    </div>
                    <div class="info">
                        <h6>{{ translate('Email Us') }}</h6>
                        <a href="Mailto:{{ $email ?? 'contact@example.com' }}">{{ $email ?? 'contact@example.com' }}</a>
                    </div>
                </div>
                <div class="xe-footer-contact-item">
                    <div class="icon-wrap">
                        <img src="{{ dynamicAsset(path: 'public/landing-page/assets/img/footer/tel.png') }}" alt="Phone">
                    </div>
                    <div class="info">
                        <h6>{{ translate('Call Us') }}</h6>
                        <a href="Tel:{{ $contactNumber ?? '+255000000000' }}">{{ $contactNumber ?? '+255000000000' }}</a>
                    </div>
                </div>
                <div class="xe-footer-contact-item">
                    <div class="icon-wrap">
                        <img src="{{ dynamicAsset(path: 'public/landing-page/assets/img/footer/pin.png') }}" alt="Address">
                    </div>
                    <div class="info">
                        <h6>{{ translate('Visit Us') }}</h6>
                        <span>{{ $businessAddress ?? 'Dar es Salaam, Tanzania' }}</span>
                    </div>
                </div>
            </div>

            <!-- App Download Column -->
            <div class="xe-footer-col">
                <h5>{{ translate('Download App') }}</h5>
                <p style="color: #b0b0b0; font-size: 14px; line-height: 1.7; margin-bottom: 20px;">
                    {{ translate('Download our mobile apps for the best experience on your device.') }}
                </p>
                <div class="xe-footer-apps" style="flex-direction: column; gap: 20px;">
                    @if($customerAppVersionControlForAndroid || $customerAppVersionControlForIos)
                        <div class="app-group">
                            <h6>User App</h6>
                            <div class="app-links" style="flex-direction: row; flex-wrap: wrap;">
                                @if($customerAppVersionControlForAndroid)
                                    <a target="_blank" href="{{ $customerAppVersionControlForAndroid['app_url'] }}">
                                        <img src="{{ dynamicAsset(path: 'public/landing-page/assets/img/play-store.png') }}" class="w-115px" alt="Google Play">
                                    </a>
                                @endif
                                @if($customerAppVersionControlForIos)
                                    <a target="_blank" href="{{ $customerAppVersionControlForIos['app_url'] }}">
                                        <img src="{{ dynamicAsset(path: 'public/landing-page/assets/img/app-store.png') }}" class="w-115px" alt="App Store">
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                    @if($driverAppVersionControlForAndroid || $driverAppVersionControlForIos)
                        <div class="app-group">
                            <h6>Driver App</h6>
                            <div class="app-links" style="flex-direction: row; flex-wrap: wrap;">
                                @if($driverAppVersionControlForAndroid)
                                    <a target="_blank" href="{{ $driverAppVersionControlForAndroid['app_url'] }}">
                                        <img src="{{ dynamicAsset(path: 'public/landing-page/assets/img/play-store.png') }}" class="w-115px" alt="Google Play">
                                    </a>
                                @endif
                                @if($driverAppVersionControlForIos)
                                    <a target="_blank" href="{{ $driverAppVersionControlForIos['app_url'] }}">
                                        <img src="{{ dynamicAsset(path: 'public/landing-page/assets/img/app-store.png') }}" class="w-115px" alt="App Store">
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="xe-footer-bottom">
        <p>{!! getSession('copyright_text') !!}</p>
    </div>
</footer>
