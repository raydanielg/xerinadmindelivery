@extends('adminmodule::layouts.master')

@section('title', translate('Integration Settings'))

@push('css_or_js')
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fs-22 text-capitalize">{{ translate('Integration Settings') }}</h2>
                    <p class="text-muted mb-0">{{ $partner->name }} — {{ $partner->company_name ?? '' }}</p>
                </div>
                <a href="{{ route('admin.partnership.show', $partner) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> {{ translate('Back') }}
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <form action="{{ route('admin.partnership.integration.update', $partner) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-shield-lock"></i> {{ translate('Secure Configuration') }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    {{ translate('Enter secret-manager references only. Never paste a raw API key or webhook secret here.') }}
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label">{{ translate('Partner API base URL') }}</label>
                                    <input type="url" name="partner_api_base_url" class="form-control"
                                           placeholder="https://partner.example/api"
                                           value="{{ old('partner_api_base_url', $partner->partner_api_base_url) }}">
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label">{{ translate('Outbound webhook URL') }}</label>
                                    <input type="url" name="outbound_webhook_url" class="form-control"
                                           placeholder="https://partner.example/webhooks/xerin"
                                           value="{{ old('outbound_webhook_url', $partner->outbound_webhook_url) }}">
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-key"></i> {{ translate('Authentication') }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="col-12 mb-3">
                                    <label class="form-label">{{ translate('Authentication') }}</label>
                                    <select name="auth_method" class="form-select" id="auth_method">
                                        <option value="none" {{ old('auth_method', $partner->auth_method) === 'none' ? 'selected' : '' }}>{{ translate('None') }}</option>
                                        <option value="api_key" {{ old('auth_method', $partner->auth_method) === 'api_key' ? 'selected' : '' }}>{{ translate('API Key') }}</option>
                                    </select>
                                </div>

                                <div id="api_key_fields" style="display: none;">
                                    <div class="col-12 mb-3">
                                        <label class="form-label">{{ translate('API key header') }}</label>
                                        <input type="text" name="api_key_header" class="form-control"
                                               placeholder="X-API-Key"
                                               value="{{ old('api_key_header', $partner->api_key_header) }}">
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label class="form-label">{{ translate('Credential reference') }}</label>
                                        <input type="text" name="credential_reference" class="form-control"
                                               placeholder="vault://xerin/partner/api-key"
                                               value="{{ old('credential_reference', $partner->credential_reference) }}">
                                        <small class="text-muted">{{ translate('Secret-manager reference for the API key') }}</small>
                                    </div>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label">{{ translate('Webhook secret reference') }}</label>
                                    <input type="text" name="webhook_secret_reference" class="form-control"
                                           placeholder="vault://xerin/partner/webhook-secret"
                                           value="{{ old('webhook_secret_reference', $partner->webhook_secret_reference) }}">
                                    <small class="text-muted">{{ translate('Secret-manager reference for the webhook signing secret') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-broadcast"></i> {{ translate('Events') }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="col-12 mb-3">
                                    <label class="form-label">{{ translate('Enabled event names') }}</label>
                                    <input type="text" name="enabled_events" class="form-control"
                                           placeholder="shipment.updated, delivery.completed"
                                           value="{{ old('enabled_events', $partner->enabled_events) }}">
                                    <small class="text-muted">{{ translate('Separate event names with commas.') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="integration_active" value="1"
                                           id="integration_active" {{ old('integration_active', $partner->integration_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="integration_active">
                                        {{ translate('Activate integration') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.partnership.show', $partner) }}" class="btn btn-secondary">{{ translate('Cancel') }}</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> {{ translate('Save integration') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('script')
    <script>
        (function () {
            var authSelect = document.getElementById('auth_method');
            var apiKeyFields = document.getElementById('api_key_fields');

            function toggleApiKeyFields() {
                apiKeyFields.style.display = authSelect.value === 'api_key' ? 'block' : 'none';
            }

            authSelect.addEventListener('change', toggleApiKeyFields);
            toggleApiKeyFields();
        })();
    </script>
    @endpush
@endsection
