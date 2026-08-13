@extends('adminmodule::layouts.master')

@section('title', translate('Edit Partner'))

@push('css_or_js')
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="mb-4">
                <h2 class="fs-22 text-capitalize">{{ translate('Edit Partner') }}</h2>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body p-30">
                            <form action="{{ route('admin.partnership.update', $partner) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ translate('Name') }} *</label>
                                        <input type="text" name="name" class="form-control" required value="{{ old('name', $partner->name) }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ translate('Company Name') }}</label>
                                        <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $partner->company_name) }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ translate('Email') }} *</label>
                                        <input type="email" name="email" class="form-control" required value="{{ old('email', $partner->email) }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ translate('Phone') }}</label>
                                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $partner->phone) }}">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">{{ translate('Webhook URL') }}</label>
                                        <input type="url" name="webhook_url" class="form-control" placeholder="https://example.com/webhook" value="{{ old('webhook_url', $partner->webhook_url) }}">
                                        <small class="text-muted">{{ translate('We will send order updates to this URL') }}</small>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">{{ translate('Status') }} *</label>
                                        <select name="status" class="form-select" required>
                                            <option value="active" {{ $partner->status === 'active' ? 'selected' : '' }}>{{ translate('Active') }}</option>
                                            <option value="inactive" {{ $partner->status === 'inactive' ? 'selected' : '' }}>{{ translate('Inactive') }}</option>
                                            <option value="suspended" {{ $partner->status === 'suspended' ? 'selected' : '' }}>{{ translate('Suspended') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">{{ translate('Permissions') }}</label>
                                        <div class="row">
                                            @php($currentPerms = $partner->permissions ?? [])
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[create_order]" value="1" id="perm_create" {{ in_array('create_order', $currentPerms) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm_create">{{ translate('Create Orders') }}</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[view_order]" value="1" id="perm_view" {{ in_array('view_order', $currentPerms) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm_view">{{ translate('View Orders') }}</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[cancel_order]" value="1" id="perm_cancel" {{ in_array('cancel_order', $currentPerms) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm_cancel">{{ translate('Cancel Orders') }}</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[wallet_access]" value="1" id="perm_wallet" {{ in_array('wallet_access', $currentPerms) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm_wallet">{{ translate('Wallet Access') }}</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[track_order]" value="1" id="perm_track" {{ in_array('track_order', $currentPerms) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm_track">{{ translate('Track Orders') }}</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[transaction_history]" value="1" id="perm_tx" {{ in_array('transaction_history', $currentPerms) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm_tx">{{ translate('Transaction History') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.partnership.index') }}" class="btn btn-secondary">{{ translate('Cancel') }}</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg"></i> {{ translate('Update Partner') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
