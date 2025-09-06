<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;

class AdminEmployeeController extends Controller
{
    // GET /api/admin/employees
    public function index(Request $request)
    {
        $query = Employee::query()->with('data');

        if ($providerId = $request->query('provider_id')) {
            $query->where('service_provider_id', $providerId);
        }

        if (!is_null($request->query('is_active'))) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        if (!is_null($request->query('is_verified'))) {
            $query->where('is_verified', filter_var($request->query('is_verified'), FILTER_VALIDATE_BOOLEAN));
        }

        if (!is_null($request->query('is_suspended'))) {
            $query->where('is_suspended', filter_var($request->query('is_suspended'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($q = $request->query('q')) {
            $query->where(function ($qb) use ($q) {
                $qb->where('tip_code', 'like', "%{$q}%")
                   ->orWhereHas('data', function ($dqb) use ($q) {
                       $dqb->where('first_name', 'like', "%{$q}%")
                           ->orWhere('last_name', 'like', "%{$q}%")
                           ->orWhere('email', 'like', "%{$q}%");
                   });
            });
        }

        $employees = $query->orderByDesc('created_at')->paginate((int) $request->query('per_page', 15));

        return response()->json($employees);
    }

    // GET /api/admin/employees/{id}
    public function show(string $id)
    {
        $employee = Employee::with('data')->findOrFail($id);
        return response()->json($employee);
    }

    // POST /api/admin/employees/{id}/activate
    public function activate(string $id)
    {
        $employee = Employee::findOrFail($id);
        $employee->is_active = true;
        $employee->save();

        return response()->json([
            'message' => 'Employee activated',
            'employee' => $employee,
        ]);
    }

    // POST /api/admin/employees/{id}/deactivate
    public function deactivate(string $id)
    {
        $employee = Employee::findOrFail($id);
        $employee->is_active = false;
        $employee->save();

        return response()->json([
            'message' => 'Employee deactivated',
            'employee' => $employee,
        ]);
    }

    // POST /api/admin/employees/{id}/suspend
    public function suspend(Request $request, string $id)
    {
        $data = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $employee = Employee::findOrFail($id);
        $employee->is_suspended = true;
        $employee->suspended_at = now();
        $employee->suspension_reason = $data['reason'];
        $employee->save();

        return response()->json([
            'message' => 'Employee suspended',
            'employee' => $employee,
        ]);
    }

    // POST /api/admin/employees/{id}/unsuspend
    public function unsuspend(string $id)
    {
        $employee = Employee::findOrFail($id);
        $employee->is_suspended = false;
        $employee->suspended_at = null;
        $employee->suspension_reason = null;
        $employee->save();

        return response()->json([
            'message' => 'Employee unsuspended',
            'employee' => $employee,
        ]);
    }
}
