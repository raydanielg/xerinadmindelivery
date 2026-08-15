<?php

namespace Modules\Gateways\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\AdminModule\Entities\ActivityLog;
use Modules\Gateways\Entities\SmsLog;

class SmsLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('sms_log_view');

        $query = SmsLog::query();

        if ($request?->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request?->filled('gateway')) {
            $query->where('gateway', $request->gateway);
        }

        if ($request?->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request?->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('receiver', 'like', "%{$search}%")
                  ->orWhere('gateway', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(25);

        $stats = [
            'total' => SmsLog::count(),
            'success' => SmsLog::where('status', 'success')->count(),
            'failed' => SmsLog::where('status', 'error')->count(),
            'today' => SmsLog::whereDate('created_at', today())->count(),
        ];

        $gateways = SmsLog::distinct()->pluck('gateway')->filter();
        $types = SmsLog::distinct()->pluck('type')->filter();

        return view('Gateways::admin.sms-logs.index', compact('logs', 'stats', 'gateways', 'types'));
    }

    public function show($id)
    {
        $this->authorize('sms_log_view');

        $log = SmsLog::findOrFail($id);
        return view('Gateways::admin.sms-logs.show', compact('log'));
    }

    public function destroy($id)
    {
        $this->authorize('sms_log_delete');

        $log = SmsLog::findOrFail($id);
        $logData = $log->toArray();

        DB::beginTransaction();
        try {
            $log->delete();

            $activityLog = new ActivityLog();
            $activityLog->edited_by = auth()->user()->id;
            $activityLog->before = $logData;
            $activityLog->user_type = auth()->user()->user_type;
            $activityLog->logable_type = SmsLog::class;
            $activityLog->logable_id = $id;
            $activityLog->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete SMS log');
        }

        return back()->with('success', 'SMS log deleted successfully');
    }

    public function clearAll(Request $request)
    {
        $this->authorize('sms_log_clear');

        $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);

        $count = SmsLog::count();

        DB::beginTransaction();
        try {
            $activityLog = new ActivityLog();
            $activityLog->edited_by = auth()->user()->id;
            $activityLog->before = ['action' => 'clear_all', 'reason' => $request->reason, 'records_deleted' => $count];
            $activityLog->user_type = auth()->user()->user_type;
            $activityLog->logable_type = SmsLog::class;
            $activityLog->logable_id = 0;
            $activityLog->save();

            SmsLog::truncate();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to clear SMS logs');
        }

        return back()->with('success', 'All SMS logs cleared. Audit record created.');
    }
}
