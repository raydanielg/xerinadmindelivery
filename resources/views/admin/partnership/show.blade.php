@extends('adminmodule::layouts.master')

@section('title', translate('Partner Details'))

@push('css_or_js')
    <style>
        .key-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px 16px;
            font-family: monospace;
            word-break: break-all;
            position: relative;
        }
        .copy-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            cursor: pointer;
            opacity: 0.6;
            transition: opacity 0.2s;
        }
        .copy-btn:hover { opacity: 1; }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <h2 class="fs-22 text-capitalize">{{ translate('Partner Details') }}</h2>
                <a href="{{ route('admin.partnership.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> {{ translate('Back') }}
                </a>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ translate('Partner Information') }}</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 200px;">{{ translate('Name') }}</td>
                                    <td>{{ $partner->name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">{{ translate('Company') }}</td>
                                    <td>{{ $partner->company_name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">{{ translate('Email') }}</td>
                                    <td>{{ $partner->email }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">{{ translate('Phone') }}</td>
                                    <td>{{ $partner->phone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">{{ translate('Webhook URL') }}</td>
                                    <td>{{ $partner->webhook_url ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">{{ translate('Status') }}</td>
                                    <td>
                                        @if($partner->status === 'active')
                                            <span class="badge bg-success">{{ translate('Active') }}</span>
                                        @elseif($partner->status === 'inactive')
                                            <span class="badge bg-secondary">{{ translate('Inactive') }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ translate('Suspended') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">{{ translate('Last Active') }}</td>
                                    <td>{{ $partner->last_active_at ? $partner->last_active_at->diffForHumans() : translate('Never') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">{{ translate('Created') }}</td>
                                    <td>{{ $partner->created_at->format('d M Y, H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">{{ translate('API Credentials') }}</h5>
                            <form action="{{ route('admin.partnership.regenerate-keys', $partner) }}" method="POST" onsubmit="return confirm('{{ translate('Regenerate keys? The old keys will stop working immediately.') }}')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-arrow-repeat"></i> {{ translate('Regenerate Keys') }}
                                </button>
                            </form>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ translate('API Key') }}</label>
                                <div class="key-box">
                                    <span id="apiKey">{{ $partner->api_key }}</span>
                                    <i class="bi bi-clipboard copy-btn" onclick="copyText('apiKey', this)" title="Copy"></i>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ translate('Secret Key') }}</label>
                                <div class="key-box">
                                    <span id="secretKey">{{ $partner->secret_key }}</span>
                                    <i class="bi bi-clipboard copy-btn" onclick="copyText('secretKey', this)" title="Copy"></i>
                                </div>
                            </div>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                {{ translate('Keep these keys secure. The secret key will not be shown again after regeneration.') }}
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ translate('Permissions') }}</h5>
                        </div>
                        <div class="card-body">
                            @php($perms = $partner->permissions ?? [])
                            @if(count($perms) > 0)
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($perms as $perm)
                                        <span class="badge bg-info text-dark text-capitalize">{{ str_replace('_', ' ', $perm) }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">{{ translate('No permissions granted.') }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ translate('Quick Actions') }}</h5>
                        </div>
                        <div class="card-body d-flex flex-column gap-2">
                            <a href="{{ route('admin.partnership.edit', $partner) }}" class="btn btn-outline-primary">
                                <i class="bi bi-pencil"></i> {{ translate('Edit Partner') }}
                            </a>
                            <a href="{{ route('admin.partnership.documentation') }}" class="btn btn-outline-info">
                                <i class="bi bi-file-earmark-text"></i> {{ translate('API Documentation') }}
                            </a>
                            <form action="{{ route('admin.partnership.destroy', $partner) }}" method="POST" onsubmit="return confirm('{{ translate('Are you sure you want to delete this partner?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="bi bi-trash"></i> {{ translate('Delete Partner') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    function copyText(elementId, btn) {
        const text = document.getElementById(elementId).innerText;
        navigator.clipboard.writeText(text).then(function() {
            const originalClass = btn.className;
            btn.className = 'bi bi-check-circle-fill copy-btn text-success';
            setTimeout(function() {
                btn.className = originalClass;
            }, 2000);
        });
    }
</script>
@endpush
