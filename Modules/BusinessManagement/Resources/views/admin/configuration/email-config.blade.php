@extends('adminmodule::layouts.master')

@section('title', translate('Email_Config'))

@section('content')

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="fs-22 mb-4 text-capitalize">{{translate('3rd_party')}}</h2>
            @include('businessmanagement::admin.configuration.partials._third_party_inline_menu')

            <div class="card">
                <div class="card-body">
                    <h5 class="text-primary text-uppercase mb-4">{{translate('mail_configuration_information')}}</h5>

                    <form action="{{route('admin.business.configuration.third-party.email-config.update')}}"
                          method="post" id="email_config_form">
                        @csrf
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="mailer_name" class="mb-2">{{translate('mailer_name')}}</label>
                                    <input required type="text" name="mailer_name"
                                           value="{{$setting['mailer_name'] ?? ''}}"
                                           class="form-control" id="mailer_name"
                                           placeholder="{{translate('Ex: John Doe')}}" tabindex="1">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="host" class="mb-2">{{translate('host')}}</label>
                                    <input required type="text" name="host"
                                           value="{{$setting['host'] ?? ''}}"
                                           class="form-control" id="host"
                                           placeholder="{{translate('email.example.com')}}" tabindex="2">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="driver" class="mb-2">{{translate('driver')}}</label>
                                    <input required type="text" name="driver"
                                           value="{{$setting['driver'] ?? ''}}"
                                           class="form-control" id="driver"
                                           placeholder="{{translate('Ex: SMTP')}}" tabindex="3">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="port" class="mb-2">{{translate('port')}}</label>
                                    <input required type="text" name="port"
                                           value="{{$setting['port'] ?? ''}}"
                                           class="form-control" id="port" placeholder="{{translate('Ex: Port')}}" tabindex="4">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="username" class="mb-2">{{translate('username')}}</label>
                                    <input required type="text" name="username"
                                           value="{{$setting['username'] ?? ''}}"
                                           class="form-control" id="username"
                                           placeholder="{{translate('demo@example.com')}}" tabindex="5">
                                </div> 
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="email_id" class="mb-2">{{translate('email_ID')}}</label>
                                    <input required type="text" name="email_id"
                                           value="{{$setting['email_id'] ?? ''}}"
                                           class="form-control" id="email_id"
                                           placeholder="{{translate('demo@example.com')}}" tabindex="6">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="encryption" class="mb-2">{{translate('encryption')}}</label>
                                    <input required type="text" name="encryption"
                                           value="{{$setting['encryption']  ?? ''}}"
                                           class="form-control" id="encryption" placeholder="{{translate('tls')}}" tabindex="7">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="mb-4">
                                    <label for="password" class="mb-2">{{translate('password')}}</label>
                                    <input required type="text" name="password"
                                           value="{{$setting['password'] ?? ''}}"
                                           class="form-control" id="password" placeholder="Ex: 12345678" tabindex="8">
                                </div>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary cmn_focus" tabindex="9">{{translate('submit')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Test Email Section --}}
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="text-primary text-uppercase mb-3">{{translate('test_email')}}</h5>
                    <p class="text-muted mb-3" style="font-size: 13px;">{{translate('send_a_test_email_to_verify_your_mail_configuration')}}</p>
                    <div class="row align-items-end">
                        <div class="col-sm-8">
                            <div class="mb-2">
                                <label for="test_email" class="mb-2">{{translate('test_email_address')}}</label>
                                <input type="email" name="test_email"
                                       class="form-control" id="test_email"
                                       placeholder="{{translate('demo@example.com')}}" tabindex="10">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <button type="button" class="btn btn-outline-primary w-100" id="send_test_email_btn" tabindex="11">
                                <i class="bi bi-send me-1"></i>{{translate('send_test_email')}}
                            </button>
                        </div>
                    </div>
                    <div id="test_email_result" class="mt-3" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Main Content -->
@endsection

@push('script')

    <script>
        "use strict";

        let permission = false;
        @can('business_edit')
            permission = true;
        @endcan


        $('#email_config_form').on('submit', function (e) {
            if (!permission) {
                toastr.error('{{ translate('you_do_not_have_enough_permission_to_update_this_settings') }}');
                e.preventDefault();
            }
        });

        $('#send_test_email_btn').on('click', function () {
            if (!permission) {
                toastr.error('{{ translate('you_do_not_have_enough_permission_to_update_this_settings') }}');
                return;
            }

            const email = $('#test_email').val().trim();
            if (!email) {
                toastr.error('{{ translate('please_enter_a_valid_email_address') }}');
                return;
            }

            const $btn = $(this);
            const $result = $('#test_email_result');
            const originalHtml = $btn.html();

            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>{{translate('sending...')}}');
            $result.hide();

            $.ajax({
                url: '{{ route("admin.business.configuration.third-party.email-config.test") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    test_email: email,
                },
                success: function (response) {
                    $result.removeClass('alert-danger').addClass('alert alert-success').html(
                        '<i class="bi bi-check-circle me-1"></i>' + response.message
                    ).show();
                    toastr.success(response.message);
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON?.message || 'Failed to send test email.';
                    $result.removeClass('alert-success').addClass('alert alert-danger').html(
                        '<i class="bi bi-exclamation-triangle me-1"></i>' + msg
                    ).show();
                    toastr.error(msg);
                },
                complete: function () {
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    </script>

@endpush

