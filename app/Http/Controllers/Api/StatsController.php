<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BenefitRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatsController extends Controller
{
    public function index()
    {
        $totalBenefitRequests = BenefitRequest::count();

        $statusDistribution = BenefitRequest::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $formsPerDay = collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::now()->subDays($daysAgo);
            $count = BenefitRequest::whereDate('created_at', $date->toDateString())->count();
            return [
                'date' => $date->locale('pt_BR')->isoFormat('DD [de] MMM[.]'),
                'count' => $count,
            ];
        })->values()->toArray();

        $topBenefitTypes = BenefitRequest::select(
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(benefit_details, '$.name')) as name"),
                DB::raw('count(*) as count')
            )
            ->whereNotNull('benefit_details')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(benefit_details, '$.name')) != ''")
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(benefit_details, '$.name')) != 'null'")
            ->groupBy('name')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->toArray();

        return response()->json([
            'total_forms'            => 0,
            'total_benefit_requests' => $totalBenefitRequests,
            'total_evaluations'      => 0,
            'status_distribution'    => $statusDistribution,
            'forms_per_day'          => $formsPerDay,
            'top_benefit_types'      => $topBenefitTypes,
        ]);
    }
}
