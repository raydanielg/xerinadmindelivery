@extends('adminmodule::layouts.master')

@section('title', translate('Partnership'))

@push('css_or_js')
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <h2 class="fs-22 text-capitalize">{{ translate('Partnership') }}</h2>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.partnership.documentation') }}" class="btn btn-outline-info">
                        <i class="bi bi-file-earmark-text"></i> {{ translate('API Documentation') }}
                    </a>
                    <a href="{{ route('admin.partnership.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> {{ translate('Add Partner') }}
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ translate('Partners List') }}</h5>
                        </div>
                        <div class="card-body p-0">
                            @if($partners->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ translate('Name') }}</th>
                                                <th>{{ translate('Company') }}</th>
                                                <th>{{ translate('Email') }}</th>
                                                <th>{{ translate('API Key') }}</th>
                                                <th>{{ translate('Status') }}</th>
                                                <th>{{ translate('Last Active') }}</th>
                                                <th class="text-end">{{ translate('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($partners as $key => $partner)
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    <td>{{ $partner->name }}</td>
                                                    <td>{{ $partner->company_name ?? '-' }}</td>
                                                    <td>{{ $partner->email }}</td>
                                                    <td>
                                                        <code class="text-muted">{{ substr($partner->api_key, 0, 12) }}...</code>
                                                    </td>
                                                    <td>
                                                        @if($partner->status === 'active')
                                                            <span class="badge bg-success">{{ translate('Active') }}</span>
                                                        @elseif($partner->status === 'inactive')
                                                            <span class="badge bg-secondary">{{ translate('Inactive') }}</span>
                                                        @else
                                                            <span class="badge bg-danger">{{ translate('Suspended') }}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $partner->last_active_at ? $partner->last_active_at->diffForHumans() : translate('Never') }}</td>
                                                    <td class="text-end">
                                                        <a href="{{ route('admin.partnership.show', $partner) }}" class="btn btn-sm btn-outline-info" title="{{ translate('View') }}">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.partnership.edit', $partner) }}" class="btn btn-sm btn-outline-primary" title="{{ translate('Edit') }}">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <form action="{{ route('admin.partnership.destroy', $partner) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ translate('Are you sure?') }}')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ translate('Delete') }}">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="bi bi-people fs-1 text-muted"></i>
                                    <p class="mt-3 text-muted">{{ translate('No partners found. Click "Add Partner" to create one.') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
