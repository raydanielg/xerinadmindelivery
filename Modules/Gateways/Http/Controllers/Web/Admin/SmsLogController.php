<?php

namespace Modules\Gateways\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Gateways\Entities\SmsLog;

class SmsLogController extends Controller
{
    public function index(Request $request)
    {
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
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhere('gateway', 'like', "%{$search}%");
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
        $log = SmsLog::findOrFail($id);
        return view('Gateways::admin.sms-logs.show', compact('log'));
    }

    public function destroy($id)
    {
        SmsLog::findOrFail($id)->delete();
        return back()->with('success', 'SMS log deleted successfully');
    }

    public function clearAll()
    {
        SmsLog::truncate();
        return back()->with('success', 'All SMS logs cleared');
    }
}
