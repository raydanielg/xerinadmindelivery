@extends('adminmodule::layouts.master')

@section('title', translate('SMS Log Details'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fs-22">{{translate('SMS Log Details')}} #{{$log->id}}</h2>
                <a href="{{route('admin.gateways.sms-logs.index')}}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> {{translate('Back to Logs')}}
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 180px;">{{translate('ID')}}</th>
                                <td>{{$log->id}}</td>
                            </tr>
                            <tr>
                                <th>{{translate('Gateway')}}</th>
                                <td><span class="badge bg-info">{{$log->gateway}}</span></td>
                            </tr>
                            <tr>
                                <th>{{translate('Receiver')}}</th>
                                <td>{{$log->masked_receiver}}</td>
                            </tr>
                            <tr>
                                <th>{{translate('Type')}}</th>
                                <td><span class="badge bg-secondary">{{$log->type}}</span></td>
                            </tr>
                            <tr>
                                <th>{{translate('Status')}}</th>
                                <td>
                                    @if($log->status == 'success')
                                        <span class="text-success fw-bold"><i class="bi bi-check-circle"></i> Success</span>
                                    @elseif($log->status == 'error')
                                        <span class="text-danger fw-bold"><i class="bi bi-x-circle"></i> Failed</span>
                                    @else
                                        <span class="text-warning fw-bold"><i class="bi bi-clock"></i> Pending</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>{{translate('Message')}}</th>
                                <td><pre class="mb-0" style="white-space: pre-wrap;">{{$log->redacted_message}}</pre></td>
                            </tr>
                            <tr>
                                <th>{{translate('Response')}}</th>
                                <td><pre class="mb-0" style="white-space: pre-wrap;">{{$log->response ?? 'N/A'}}</pre></td>
                            </tr>
                            <tr>
                                <th>{{translate('Error Message')}}</th>
                                <td class="text-danger">{{$log->error_message ?? 'N/A'}}</td>
                            </tr>
                            <tr>
                                <th>{{translate('Date')}}</th>
                                <td>{{$log->created_at->format('d M Y, H:i:s')}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
