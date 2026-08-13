@php($seoTitle = 'Privacy Policy - ' . ($businessName ?? 'Zerin Express'))
@php($seoDescription = 'Read the Zerin Express privacy policy. Learn how we protect your data and privacy when using our ride sharing and delivery services.')
@php($seoKeywords = 'privacy policy, data protection, zerin express privacy, user data')
@extends('landing-page.layouts.master')
@section('title', 'Privacy Policy')

@section('content')
    <div class="container pt-3">
        <section class="page-header bg__img"
                 data-img="{{$data?->value['image'] ? dynamicStorage(path: 'storage/app/public/business/pages/'.$data?->value['image']) : dynamicAsset(path: 'public/landing-page/assets/img/page-header.png')}}"
                 style="background-image: url({{$data?->value['image'] ? dynamicStorage(path: 'storage/app/public/business/pages/'.$data?->value['image']) : dynamicAsset(path: 'public/landing-page/assets/img/page-header.png')}});">

            <h1 class="title">{{ translate('Privacy Policy') }}</h1>
            <p class="mt-2">
                {{ $data?->value['short_description'] ?? "" }}
            </p>
        </section>
    </div>
    <!-- Page Header End -->
    <section class="terms-section py-5">
        <div class="container">
            {!! $data?->value['long_description'] !!}
        </div>
    </section>
@endsection
