@extends('adminmodule::layouts.master')

@section('title', translate('SMS_Gateways'))

@push('css_or_js')
@endpush

@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="fs-22 mb-4 text-capitalize">{{translate('3rd_party')}}</h2>
            @include('businessmanagement::admin.configuration.partials._third_party_inline_menu')
            <style>
                .card {
                    display: flex;
                    flex-direction: column;
                    height: 100%;
                }

                .card-body {
                    flex: 1; /* This makes the card body fill the available space */
                }
            </style>
            <div class="main-content">
                <!-- Tab Content -->
                <div class="row">
                    @foreach($dataValues as $gateway)
                        <div class="col-md-6 mb-30 ">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="page-title">{{ucfirst(str_replace('_',' ',$gateway->key_name))}}</h4>
                                </div>
                                <div class="card-body p-30">
                                    <form
                                        action="{{route('admin.business.configuration.third-party.sms-gateway.update')}}"
                                        method="POST"
                                        id="{{$gateway->key_name}}-form" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <div class="discount-type">
                                            <div class="d-flex align-items-center gap-4 gap-xl-5 mb-30">
                                                <div class="custom-radio">
                                                    <input type="radio" id="{{$gateway->key_name}}-active" name="status"
                                                           value="1" {{$dataValues->where('key_name',$gateway->key_name)->first()->live_values['status']?'checked':''}}>
                                                    <label
                                                        for="{{$gateway->key_name}}-active">{{translate('active')}}</label>
                                                </div>
                                                <div class="custom-radio">
                                                    <input type="radio" id="{{$gateway->key_name}}-inactive"
                                                           name="status"
                                                           value="0" {{$dataValues->where('key_name',$gateway->key_name)->first()->live_values['status']?'':'checked'}}>
                                                    <label
                                                        for="{{$gateway->key_name}}-inactive">{{translate('inactive')}}</label>
                                                </div>
                                            </div>

                                            <input name="gateway" value="{{$gateway->key_name}}" class="d-none">
                                            <input name="mode" value="live" class="d-none">

                                            @php($skip=['gateway','mode','status'])
                                            @foreach($dataValues->where('key_name',$gateway->key_name)->first()->live_values as $key=>$value)
                                                @if(!in_array($key,$skip))
                                                    <div class="   mb-30 mt-30">
                                                        <label for="exampleFormControlInput1"
                                                               class="form-label">{{ucfirst(str_replace('_',' ',$key))}}
                                                            *</label>
                                                        <input type="text" class="form-control"
                                                               name="{{$key}}"
                                                               placeholder="{{ucfirst(str_replace('_',' ',$key))}} *"
                                                               value="{{env('APP_MODE') == 'demo' ? '------' : $value ??''}}"
                                                            {{ env('APP_MODE') == 'demo' ? 'disabled' : '' }}
                                                        >
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="{{ env('APP_MODE') != 'demo' ? 'submit' : 'button' }}" class="btn btn-primary call-demo">
                                            {{translate('update')}}
                                        </button>
                                        <button type="button" class="btn btn-outline-info test-sms-btn"
                                                data-gateway="{{$gateway->key_name}}"
                                                data-bs-toggle="modal" data-bs-target="#testSmsModal">
                                            <i class="bi bi-send"></i> Test SMS
                                        </button>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <!-- End Tab Content -->
            </div>

        </div>
    </div>
    <!-- End Main Content -->

    <!-- Test SMS Modal -->
    <div class="modal fade" id="testSmsModal" tabindex="-1" aria-labelledby="testSmsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="testSmsModalLabel">
                        <i class="bi bi-send"></i> Test SMS Gateway
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Gateway</label>
                        <input type="text" class="form-control" id="testSmsGateway" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Phone Number *</label>
                        <input type="text" class="form-control" id="testSmsPhone" placeholder="e.g. +255712345678" value="+255712345678">
                        <small class="text-muted">Enter phone number with country code</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Message (optional)</label>
                        <textarea class="form-control" id="testSmsMessage" rows="3" placeholder="Test SMS from Xerin Express. Your OTP is 1234">Test SMS from Xerin Express. Your OTP is 1234</textarea>
                    </div>
                    <div id="testSmsResult" class="alert d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="sendTestSmsBtn">
                        <i class="bi bi-send"></i> Send Test SMS
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    (function() {
        const testSmsModal = document.getElementById('testSmsModal');
        const testSmsGateway = document.getElementById('testSmsGateway');
        const testSmsPhone = document.getElementById('testSmsPhone');
        const testSmsMessage = document.getElementById('testSmsMessage');
        const testSmsResult = document.getElementById('testSmsResult');
        const sendTestSmsBtn = document.getElementById('sendTestSmsBtn');

        let selectedGateway = '';

        document.querySelectorAll('.test-sms-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                selectedGateway = this.getAttribute('data-gateway');
                testSmsGateway.value = selectedGateway.replace(/_/g, ' ').replace(/\b\w/g, function(c) {
                    return c.toUpperCase();
                });
                testSmsResult.classList.add('d-none');
            });
        });

        sendTestSmsBtn.addEventListener('click', function() {
            const phone = testSmsPhone.value.trim();
            const message = testSmsMessage.value.trim();

            if (!phone) {
                testSmsResult.className = 'alert alert-danger';
                testSmsResult.textContent = 'Please enter a phone number';
                testSmsResult.classList.remove('d-none');
                return;
            }

            sendTestSmsBtn.disabled = true;
            sendTestSmsBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending...';
            testSmsResult.classList.add('d-none');

            fetch('{{route("admin.business.configuration.third-party.sms-gateway.test")}}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{csrf_token()}}',
                },
                body: JSON.stringify({
                    phone: phone,
                    gateway: selectedGateway,
                    message: message,
                }),
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                testSmsResult.className = 'alert ' + (data.success ? 'alert-success' : 'alert-danger');
                testSmsResult.textContent = data.message;
                testSmsResult.classList.remove('d-none');
            })
            .catch(function(error) {
                testSmsResult.className = 'alert alert-danger';
                testSmsResult.textContent = 'Request failed: ' + error.message;
                testSmsResult.classList.remove('d-none');
            })
            .finally(function() {
                sendTestSmsBtn.disabled = false;
                sendTestSmsBtn.innerHTML = '<i class="bi bi-send"></i> Send Test SMS';
            });
        });
    })();
</script>
@endpush
