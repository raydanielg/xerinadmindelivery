<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function index()
    {
        $partners = Partner::orderBy('created_at', 'desc')->get();
        return view('admin.partnership.index', compact('partners'));
    }

    public function create()
    {
        return view('admin.partnership.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:partners,email',
            'phone' => 'nullable|string|max:20',
            'webhook_url' => 'nullable|url',
            'status' => 'required|in:active,inactive,suspended',
            'permissions' => 'nullable|array',
        ]);

        $permissions = [];
        if ($request->has('permissions')) {
            foreach ($request->permissions as $key => $value) {
                if ($value === '1') {
                    $permissions[] = $key;
                }
            }
        }

        Partner::create([
            'name' => $request->name,
            'company_name' => $request->company_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'webhook_url' => $request->webhook_url,
            'status' => $request->status,
            'permissions' => $permissions,
            'api_key' => Partner::generateApiKey(),
            'secret_key' => Partner::generateSecretKey(),
        ]);

        Toastr::success('Partner created successfully.');
        return redirect()->route('admin.partnership.index');
    }

    public function show(Partner $partner)
    {
        return view('admin.partnership.show', compact('partner'));
    }

    public function edit(Partner $partner)
    {
        return view('admin.partnership.edit', compact('partner'));
    }

    public function update(Request $request, Partner $partner)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:partners,email,' . $partner->id,
            'phone' => 'nullable|string|max:20',
            'webhook_url' => 'nullable|url',
            'status' => 'required|in:active,inactive,suspended',
            'permissions' => 'nullable|array',
        ]);

        $permissions = [];
        if ($request->has('permissions')) {
            foreach ($request->permissions as $key => $value) {
                if ($value === '1') {
                    $permissions[] = $key;
                }
            }
        }

        $partner->update([
            'name' => $request->name,
            'company_name' => $request->company_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'webhook_url' => $request->webhook_url,
            'status' => $request->status,
            'permissions' => $permissions,
        ]);

        Toastr::success('Partner updated successfully.');
        return redirect()->route('admin.partnership.index');
    }

    public function destroy(Partner $partner)
    {
        $partner->delete();
        Toastr::success('Partner deleted successfully.');
        return redirect()->route('admin.partnership.index');
    }

    public function regenerateKeys(Partner $partner)
    {
        $partner->update([
            'api_key' => Partner::generateApiKey(),
            'secret_key' => Partner::generateSecretKey(),
        ]);

        Toastr::success('API keys regenerated successfully.');
        return redirect()->route('admin.partnership.show', $partner);
    }

    public function documentation()
    {
        $baseUrl = config('app.url');
        return view('admin.partnership.documentation', compact('baseUrl'));
    }
}
