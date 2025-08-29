<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\Tip;
use App\Models\Employee;
use App\Models\ServiceProvider;

class AdminReportController extends Controller
{
    // GET /api/admin/reports/overview
    public function overview(Request $request)
    {
        [$start, $end] = $this->parseDateRange($request);

        $paymentsQ = Payment::query();
        if ($start) $paymentsQ->where('created_at', '>=', $start);
        if ($end) $paymentsQ->where('created_at', '<=', $end);

        $totals = (clone $paymentsQ)
            ->selectRaw('COALESCE(SUM(amount),0) as total_amount, COALESCE(SUM(chapa_fee),0) as total_chapa, COALESCE(SUM(service_fee),0) as total_service')
            ->first();

        $activeProviders = ServiceProvider::query()
            ->where('is_verified', true)
            ->count();

        $activeEmployees = Employee::query()
            ->where('is_active', true)
            ->when($request->boolean('only_verified', false), fn($q) => $q->where('is_verified', true))
            ->count();

        return response()->json([
            'range' => [ 'start' => $start, 'end' => $end ],
            'total_gross_tips' => (float) $totals->total_amount,
            'platform_revenue' => (float) $totals->total_service,
            'chapa_fees' => (float) $totals->total_chapa,
            'net_to_employees' => (float) ($totals->total_amount - $totals->total_service - $totals->total_chapa),
            'active_providers' => $activeProviders,
            'active_employees' => $activeEmployees,
        ]);
    }

    // GET /api/admin/reports/tips
    public function tips(Request $request)
    {
        [$start, $end] = $this->parseDateRange($request);

        $query = Tip::query();

        if ($pid = $request->query('provider_id')) {
            $query->where('service_provider_id', $pid);
        }
        if ($eid = $request->query('employee_id')) {
            $query->where('employee_id', $eid);
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($start) $query->where('created_at', '>=', $start);
        if ($end) $query->where('created_at', '<=', $end);

        $tips = $query->orderByDesc('created_at')->paginate((int) $request->query('per_page', 15));
        return response()->json($tips);
    }

    // GET /api/admin/reports/payments
    public function payments(Request $request)
    {
        [$start, $end] = $this->parseDateRange($request);

        $query = Payment::query()
            ->select('payments.*')
            ->leftJoin('tips', 'tips.id', '=', 'payments.tip_id');

        if ($pid = $request->query('provider_id')) {
            $query->where('tips.service_provider_id', $pid);
        }
        if ($eid = $request->query('employee_id')) {
            $query->where('payments.employee_id', $eid);
        }
        if ($start) $query->where('payments.created_at', '>=', $start);
        if ($end) $query->where('payments.created_at', '<=', $end);

        $payments = $query->orderByDesc('payments.created_at')->paginate((int) $request->query('per_page', 15));
        return response()->json($payments);
    }

    // GET /api/admin/reports/top-employees
    public function topEmployees(Request $request)
    {
        [$start, $end] = $this->parseDateRange($request);
        $limit = (int) $request->query('limit', 10);

        $query = Payment::query()
            ->selectRaw('payments.employee_id, COALESCE(SUM(payments.amount),0) as total_amount, COUNT(*) as payments_count')
            ->when($start, fn($q) => $q->where('payments.created_at', '>=', $start))
            ->when($end, fn($q) => $q->where('payments.created_at', '<=', $end))
            ->groupBy('payments.employee_id')
            ->orderByDesc('total_amount')
            ->limit($limit);

        $rows = $query->get();

        return response()->json([
            'range' => [ 'start' => $start, 'end' => $end ],
            'items' => $rows,
        ]);
    }

    // GET /api/admin/reports/top-providers
    public function topProviders(Request $request)
    {
        [$start, $end] = $this->parseDateRange($request);
        $limit = (int) $request->query('limit', 10);

        $query = Payment::query()
            ->leftJoin('tips', 'tips.id', '=', 'payments.tip_id')
            ->selectRaw('tips.service_provider_id as provider_id, COALESCE(SUM(payments.amount),0) as total_amount, COUNT(*) as payments_count')
            ->when($start, fn($q) => $q->where('payments.created_at', '>=', $start))
            ->when($end, fn($q) => $q->where('payments.created_at', '<=', $end))
            ->groupBy('tips.service_provider_id')
            ->orderByDesc('total_amount')
            ->limit($limit);

        $rows = $query->get();

        return response()->json([
            'range' => [ 'start' => $start, 'end' => $end ],
            'items' => $rows,
        ]);
    }

    private function parseDateRange(Request $request): array
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');
        return [ $start ? date('Y-m-d 00:00:00', strtotime($start)) : null,
                 $end ? date('Y-m-d 23:59:59', strtotime($end)) : null ];
    }
}
