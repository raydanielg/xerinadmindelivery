@php($seoTitle = 'Terms & Conditions - ' . ($businessName ?? 'Zerin Express'))
@php($seoDescription = 'Read the terms and conditions for using Zerin Express ride sharing and delivery services. Understand your rights and responsibilities.')
@php($seoKeywords = 'terms and conditions, zerin express terms, service agreement, user agreement')
@extends('landing-page.layouts.master')
@section('title', 'Terms & Conditions')

@section('content')
    <!-- Page Header Start -->
    <div class="container pt-3">
        <section class="page-header bg__img"
                 data-img="{{$data?->value['image'] ? dynamicStorage(path: 'storage/app/public/business/pages/'.$data?->value['image']) : dynamicAsset(path: 'public/landing-page/assets/img/page-header.png')}}"
                 style="background-image: url({{$data?->value['image'] ? dynamicStorage(path: 'storage/app/public/business/pages/'.$data?->value['image']) : dynamicAsset(path: 'public/landing-page/assets/img/page-header.png')}});">
            <h1 class="title">{{ translate('Terms & Condition') }}</h1>
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
