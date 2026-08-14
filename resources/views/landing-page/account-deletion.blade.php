@php($seoTitle = 'Delete Your Account - ' . ($businessName ?? 'Zerin Express'))
@php($seoDescription = 'Request deletion of your account and associated data. Your privacy and data protection rights are important to us.')
@php($seoKeywords = 'account deletion, delete account, data deletion, privacy, GDPR, account removal')
@extends('landing-page.layouts.master')
@section('title', 'Delete Your Account')

@push('seo')
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "WebPage",
        "name": "Account Deletion",
        "url": "{{ config('app.url', 'https://zerinexpress.com') }}/account-deletion",
        "description": "Request deletion of your account and associated data."
    }
    </script>
@endpush

@section('content')
    <!-- Page Header Start -->
    <div class="container pt-3">
        <section class="page-header bg__img"
                 data-img="{{ dynamicAsset(path: 'public/landing-page/assets/img/page-header.png') }}"
                 style="background-image: url({{ dynamicAsset(path: 'public/landing-page/assets/img/page-header.png') }});">
            <h1 class="title">{{ translate('Delete Your Account') }}</h1>
            <p class="mt-2">
                {{ translate('Request permanent deletion of your account and all associated data.') }}
            </p>
        </section>
    </div>
    <!-- Page Header End -->

    <section class="terms-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                        <div class="card-body p-4 p-md-5">
                            <div class="text-center mb-4">
                                <div style="display: inline-flex; align-items: center; justify-content: center; width: 72px; height: 72px; border-radius: 50%; background: #fee2e2; margin-bottom: 16px;">
                                    <i class="las la-trash-alt" style="font-size: 36px; color: #dc2626;"></i>
                                </div>
                                <h2 class="fw-bold mb-2" style="font-size: 28px;">{{ translate('Account & Data Deletion') }}</h2>
                                <p class="text-muted mb-0">
                                    {{ translate('Fill out the form below to request permanent deletion of your account and all associated data.') }}
                                </p>
                            </div>

                            <div class="alert alert-warning d-flex align-items-start gap-2" style="border-radius: 12px;">
                                <i class="las la-exclamation-triangle" style="font-size: 22px; color: #f59e0b; flex-shrink: 0; margin-top: 2px;"></i>
                                <div>
                                    <strong>{{ translate('Important:') }}</strong>
                                    {{ translate('This action is irreversible. Once your account is deleted, all your data including ride history, parcel records, payment information, and personal details will be permanently removed within 30 days. You will not be able to recover this data.') }}
                                </div>
                            </div>

                            <form action="{{ route('account-deletion.submit') }}" method="POST" class="mt-4">
                                @csrf
                                <div class="mb-4">
                                    <label for="email_or_phone" class="form-label fw-semibold">
                                        {{ translate('Email or Phone Number') }} <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control form-control-lg"
                                        id="email_or_phone"
                                        name="email_or_phone"
                                        placeholder="{{ translate('Enter your registered email or phone number') }}"
                                        value="{{ old('email_or_phone') }}"
                                        required
                                        style="border-radius: 10px; padding: 12px 16px;"
                                    >
                                    @if ($errors->has('email_or_phone'))
                                        <div class="text-danger mt-1" style="font-size: 14px;">
                                            {{ $errors->first('email_or_phone') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="mb-4">
                                    <label for="reason" class="form-label fw-semibold">
                                        {{ translate('Reason for Deletion (Optional)') }}
                                    </label>
                                    <textarea
                                        class="form-control"
                                        id="reason"
                                        name="reason"
                                        rows="3"
                                        placeholder="{{ translate('Tell us why you are deleting your account (optional)') }}"
                                        style="border-radius: 10px; padding: 12px 16px;"
                                    >{{ old('reason') }}</textarea>
                                </div>

                                <div class="mb-4">
                                    <div class="form-check d-flex align-items-start gap-2">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            id="confirm"
                                            name="confirm"
                                            value="1"
                                            required
                                            style="margin-top: 4px; width: 18px; height: 18px;"
                                        >
                                        <label class="form-check-label" for="confirm" style="font-size: 15px;">
                                            {{ translate('I understand that this action is permanent and cannot be undone. All my data will be permanently deleted.') }}
                                            <span class="text-danger">*</span>
                                        </label>
                                    </div>
                                    @if ($errors->has('confirm'))
                                        <div class="text-danger mt-1" style="font-size: 14px;">
                                            {{ $errors->first('confirm') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="d-grid">
                                    <button
                                        type="submit"
                                        class="btn btn-lg fw-semibold"
                                        style="background: #dc2626; color: #fff; border-radius: 10px; padding: 14px; border: none; transition: all 0.2s;"
                                        onmouseover="this.style.background='#b91c1c'"
                                        onmouseout="this.style.background='#dc2626'"
                                    >
                                        <i class="las la-trash-alt me-1"></i>
                                        {{ translate('Delete My Account') }}
                                    </button>
                                </div>
                            </form>

                            <div class="text-center mt-4 pt-3" style="border-top: 1px solid #e5e7eb;">
                                <p class="text-muted mb-0" style="font-size: 14px;">
                                    <i class="las la-shield-alt me-1"></i>
                                    {{ translate('Your data is processed securely. For questions, contact us at') }}
                                    <a href="mailto:{{ businessConfig('business_contact_email', BUSINESS_INFORMATION)?->value ?? 'support@zerinexpress.com' }}" class="text-decoration-none fw-semibold">
                                        {{ businessConfig('business_contact_email', BUSINESS_INFORMATION)?->value ?? 'support@zerinexpress.com' }}
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
