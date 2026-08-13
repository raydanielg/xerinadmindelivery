<!DOCTYPE html>
<html lang="en">
@php($preloader = getSession('preloader'))
@php($favicon = getSession('favicon'))
@php($seoTitle = $seoTitle ?? (@yield('title') . ' - ' . ($businessName ?? 'Zerin Express')))
@php($seoDescription = $seoDescription ?? 'Zerin Express - Smart ride sharing and delivery solution. Book rides, send parcels, and track deliveries in real-time.')
@php($seoKeywords = $seoKeywords ?? 'ride sharing, delivery, parcel delivery, taxi, transport, ride hailing, express delivery, Zerin Express, logistics')
@php($seoImage = $seoImage ?? asset('public/landing-page/assets/img/og-image.jpg'))
@php($canonicalUrl = $canonicalUrl ?? url()->current())
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <!-- Primary Meta Tags -->
    <title>{{ $seoTitle }}</title>
    <meta name="title" content="{{ $seoTitle }}"/>
    <meta name="description" content="{{ $seoDescription }}"/>
    <meta name="keywords" content="{{ $seoKeywords }}"/>
    <meta name="author" content="{{ $businessName ?? 'Zerin Express' }}"/>
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1"/>
    <meta name="googlebot" content="index, follow"/>
    <meta name="language" content="English"/>
    <meta name="revisit-after" content="7 days"/>
    <meta name="theme-color" content="#0d6efd"/>

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ $canonicalUrl }}"/>

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="{{ $canonicalUrl }}"/>
    <meta property="og:title" content="{{ $seoTitle }}"/>
    <meta property="og:description" content="{{ $seoDescription }}"/>
    <meta property="og:image" content="{{ $seoImage }}"/>
    <meta property="og:image:width" content="1200"/>
    <meta property="og:image:height" content="630"/>
    <meta property="og:site_name" content="{{ $businessName ?? 'Zerin Express' }}"/>
    <meta property="og:locale" content="en_US"/>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image"/>
    <meta name="twitter:url" content="{{ $canonicalUrl }}"/>
    <meta name="twitter:title" content="{{ $seoTitle }}"/>
    <meta name="twitter:description" content="{{ $seoDescription }}"/>
    <meta name="twitter:image" content="{{ $seoImage }}"/>

    <!-- Mobile App Meta -->
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
    <meta name="application-name" content="{{ $businessName ?? 'Zerin Express' }}"/>

    <!-- Geo Tags -->
    <meta name="geo.region" content="TZ"/>
    <meta name="geo.placename" content="Dar es Salaam"/>

    <!-- Structured Data: Organization -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "{{ $businessName ?? 'Zerin Express' }}",
        "url": "{{ config('app.url', 'https://zerinexpress.com') }}",
        "logo": "{{ asset('public/landing-page/assets/img/logo.png') }}",
        "description": "{{ $seoDescription }}",
        "sameAs": [
            "{{ config('app.url', 'https://zerinexpress.com') }}"
        ],
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "customer service",
            "availableLanguage": ["English", "Swahili"]
        }
    }
    </script>

    <!-- Structured Data: WebSite with SearchAction -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "{{ $businessName ?? 'Zerin Express' }}",
        "url": "{{ config('app.url', 'https://zerinexpress.com') }}",
        "potentialAction": {
            "@type": "SearchAction",
            "target": {
                "@type": "EntryPoint",
                "urlTemplate": "{{ config('app.url', 'https://zerinexpress.com') }}/blog/search?q={search_term_string}"
            },
            "query-input": "required name=search_term_string"
        }
    }
    </script>

    @stack('seo')

    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/landing-page/assets/css/bootstrap-icons.min.css') }}"/>
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/landing-page/assets/css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/landing-page/assets/css/animate.css') }}"/>
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/landing-page/assets/css/line-awesome.min.css') }}"/>
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/landing-page/assets/css/odometer.css') }}"/>
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/landing-page/assets/css/owl.min.css') }}"/>
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/landing-page/assets/css/main.css') }}"/>
    <link rel="stylesheet" href="{{ dynamicAsset('public/assets/admin-module/css/toastr.css') }}"/>
    @include('landing-page.layouts.css')
    <link rel="shortcut icon"
          href="{{ $favicon ? dynamicStorage(path: "storage/app/public/business/".$favicon) : dynamicAsset(path: 'public/landing-page/assets/img/favicon.png') }}"
          type="image/x-icon"/>
    <link rel="apple-touch-icon" href="{{ $favicon ? dynamicStorage(path: "storage/app/public/business/".$favicon) : dynamicAsset(path: 'public/landing-page/assets/img/favicon.png') }}"/>

    <!-- Sitemap Reference -->
    <link rel="sitemap" type="application/xml" title="Sitemap" href="{{ route('sitemap') }}"/>
</head>

<body>

<div class="preloader" id="preloader">
    @if ($preloader)
        <img class="preloader-img" width="160" loading="eager"
             src="{{ $preloader ? dynamicStorage(path: 'storage/app/public/business/' . $preloader) : '' }}" alt="">
    @else
        <div class="spinner-grow" role="status">
            <span class="visually-hidden">{{ translate('Loading...') }}</span>
        </div>
    @endif
</div>

@include('landing-page.partials._header')

@yield('content')

<!-- Footer Section Start -->
@include('landing-page.partials._footer')
<!-- Footer Section End -->
<script src="{{ dynamicAsset(path: 'public/landing-page/assets/js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/landing-page/assets/js/bootstrap.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/landing-page/assets/js/viewport.jquery.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/landing-page/assets/js/wow.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/landing-page/assets/js/owl.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/landing-page/assets/js/main.js') }}"></script>
<script src="{{ dynamicAsset('public/assets/admin-module/js/toastr.js') }}"></script>

{!! Toastr::message() !!}
@if ($errors->any())
    <script>
        "use strict";
        @foreach ($errors->all() as $error)
        toastr.error('{{ $error }}', {
            CloseButton: true,
            ProgressBar: true,
        });
        @endforeach
    </script>
@endif
@stack('script')
</body>

</html>
