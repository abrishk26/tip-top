<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceProvider;
use Illuminate\Support\Carbon;

class ServiceProviderAdminController extends Controller
{
    // GET /api/admin/service-providers
    public function index(Request $request)
    {
        $query = ServiceProvider::query();

        if ($status = $request->query('status')) {
            $query->where('registration_status', $status);
        }

        if (!is_null($request->query('is_suspended'))) {
            $query->where('is_suspended', filter_var($request->query('is_suspended'), FILTER_VALIDATE_BOOLEAN));
        }

        if (!is_null($request->query('is_verified'))) {
            $query->where('is_verified', filter_var($request->query('is_verified'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($q = $request->query('q')) {
            $query->where(function ($qbuilder) use ($q) {
                $qbuilder->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $providers = $query->orderByDesc('created_at')->paginate((int) $request->query('per_page', 15));

        return response()->json($providers);
    }

    // GET /api/admin/service-providers/{id}
    public function show(string $id)
    {
        $provider = ServiceProvider::withCount('employees')->findOrFail($id);

        return response()->json($provider);
    }

    // POST /api/admin/service-providers/{id}/accept
    public function accept(string $id)
    {
        $provider = ServiceProvider::findOrFail($id);
        $provider->registration_status = 'accepted';
        $provider->save();

        return response()->json([
            'message' => 'Service provider accepted',
            'provider' => $provider,
        ]);
    }

    // POST /api/admin/service-providers/{id}/reject
    public function reject(string $id)
    {
        $provider = ServiceProvider::findOrFail($id);
        $provider->registration_status = 'rejected';
        $provider->save();

        return response()->json([
            'message' => 'Service provider rejected',
            'provider' => $provider,
        ]);
    }

    // POST /api/admin/service-providers/{id}/suspend
    public function suspend(Request $request, string $id)
    {
        $data = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $provider = ServiceProvider::findOrFail($id);
        $provider->is_suspended = true;
        $provider->suspended_at = now();
        $provider->suspension_reason = $data['reason'];
        $provider->save();

        return response()->json([
            'message' => 'Service provider suspended',
            'provider' => $provider,
        ]);
    }

    // POST /api/admin/service-providers/{id}/unsuspend
    public function unsuspend(string $id)
    {
        $provider = ServiceProvider::findOrFail($id);
        $provider->is_suspended = false;
        $provider->suspended_at = null;
        $provider->suspension_reason = null;
        $provider->save();

        return response()->json([
            'message' => 'Service provider unsuspended',
            'provider' => $provider,
        ]);
    }

    // GET /api/admin/service-providers/{id}/employees
    public function employees(string $id)
    {
        $provider = ServiceProvider::findOrFail($id);

        $employees = $provider->employees()
            ->with('data')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'unique_id' => $employee->unique_id ?? $employee->uin ?? null,
                    'is_active' => (bool) $employee->is_active,
                    'first_name' => $employee->data->first_name ?? null,
                    'last_name' => $employee->data->last_name ?? null,
                    'email' => $employee->data->email ?? null,
                    'image_url' => $employee->data->image_url ?? null,
                ];
            });

        return response()->json([
            'provider_id' => $provider->id,
            'employees' => $employees,
        ]);
    }
}
