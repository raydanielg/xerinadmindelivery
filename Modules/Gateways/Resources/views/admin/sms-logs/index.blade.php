@extends('adminmodule::layouts.master')

@section('title', translate('SMS Logs'))

@push('css_or_js')
    <style>
        .sms-status-success { color: #28a745; font-weight: 600; }
        .sms-status-error { color: #dc3545; font-weight: 600; }
        .sms-status-pending { color: #ffc107; font-weight: 600; }
        .stat-card { border-radius: 10px; padding: 20px; text-align: center; }
        .stat-card .stat-number { font-size: 28px; font-weight: 700; }
        .stat-card .stat-label { font-size: 13px; color: #6c757d; text-transform: uppercase; }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fs-22">{{translate('SMS Logs & Messages')}}</h2>
                @can('sms_log_clear')
                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                        <i class="bi bi-trash"></i> {{translate('Clear All Logs')}}
                    </button>
                @endcan
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card bg-light">
                        <div class="stat-number">{{ $stats['total'] }}</div>
                        <div class="stat-label">{{translate('Total SMS')}}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-success text-white">
                        <div class="stat-number">{{ $stats['success'] }}</div>
                        <div class="stat-label text-white">{{translate('Successful')}}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-danger text-white">
                        <div class="stat-number">{{ $stats['failed'] }}</div>
                        <div class="stat-label text-white">{{translate('Failed')}}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-primary text-white">
                        <div class="stat-number">{{ $stats['today'] }}</div>
                        <div class="stat-label text-white">{{translate('Today')}}</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{route('admin.gateways.sms-logs.index')}}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">{{translate('Status')}}</label>
                            <select name="status" class="form-control">
                                <option value="">{{translate('All')}}</option>
                                <option value="success" {{request('status') == 'success' ? 'selected' : ''}}>{{translate('Success')}}</option>
                                <option value="error" {{request('status') == 'error' ? 'selected' : ''}}>{{translate('Failed')}}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{translate('Gateway')}}</label>
                            <select name="gateway" class="form-control">
                                <option value="">{{translate('All')}}</option>
                                @foreach($gateways as $gw)
                                    <option value="{{$gw}}" {{request('gateway') == $gw ? 'selected' : ''}}>{{$gw}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{translate('Type')}}</label>
                            <select name="type" class="form-control">
                                <option value="">{{translate('All')}}</option>
                                @foreach($types as $t)
                                    <option value="{{$t}}" {{request('type') == $t ? 'selected' : ''}}>{{$t}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{translate('Search')}}</label>
                            <input type="text" name="search" class="form-control" placeholder="{{translate('Gateway, type...')}}" value="{{request('search')}}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> {{translate('Filter')}}</button>
                            <a href="{{route('admin.gateways.sms-logs.index')}}" class="btn btn-secondary">{{translate('Reset')}}</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- SMS Logs Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{translate('Gateway')}}</th>
                                    <th>{{translate('Receiver')}}</th>
                                    <th>{{translate('Message')}}</th>
                                    <th>{{translate('Type')}}</th>
                                    <th>{{translate('Status')}}</th>
                                    <th>{{translate('Date')}}</th>
                                    <th>{{translate('Actions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td>{{$log->id}}</td>
                                        <td><span class="badge bg-info">{{$log->gateway}}</span></td>
                                        <td>{{$log->masked_receiver}}</td>
                                        <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{\Illuminate\Support\Str::limit($log->redacted_message, 80)}}</td>
                                        <td><span class="badge bg-secondary">{{$log->type}}</span></td>
                                        <td>
                                            @if($log->status == 'success')
                                                <span class="sms-status-success"><i class="bi bi-check-circle"></i> Success</span>
                                            @elseif($log->status == 'error')
                                                <span class="sms-status-error"><i class="bi bi-x-circle"></i> Failed</span>
                                            @else
                                                <span class="sms-status-pending"><i class="bi bi-clock"></i> Pending</span>
                                            @endif
                                        </td>
                                        <td>{{$log->created_at->format('d M Y, H:i')}}</td>
                                        <td>
                                            <a href="{{route('admin.gateways.sms-logs.show', $log->id)}}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">{{translate('No SMS logs found')}}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-4">
                        {{$logs->links()}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('sms_log_clear')
    <div class="modal fade" id="clearLogsModal" tabindex="-1" aria-labelledby="clearLogsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{route('admin.gateways.sms-logs.clear')}}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title text-danger" id="clearLogsModalLabel">
                            <i class="bi bi-exclamation-triangle-fill"></i> {{translate('Clear All SMS Logs')}}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            {{translate('This action will permanently delete all SMS log records. An audit entry will be created with your name and reason. This action cannot be undone.')}}
                        </div>
                        <div class="mb-3">
                            <label for="clearReason" class="form-label fw-bold">{{translate('Reason (required)')}}</label>
                            <textarea name="reason" id="clearReason" class="form-control" rows="3" required minlength="5" maxlength="500" placeholder="{{translate('Please provide a reason for clearing all logs...')}}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{translate('Cancel')}}</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> {{translate('Confirm Clear All')}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
@endsection
