<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $business = $request->user()->business;

        $collectedThisMonth = $business->invoices()
            ->where('status', 'paid')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->sum('total');

        $receiptsThisMonth = $business->invoices()
            ->whereMonth('issue_date', now()->month)
            ->whereYear('issue_date', now()->year)
            ->count();

        $unpaidAmount = (float) $business->invoices()
            ->whereIn('status', ['unpaid', 'part_payment'])
            ->sum(\Illuminate\Support\Facades\DB::raw('total - COALESCE(amount_paid, 0)'));

        $customerCount = $business->customers()->count();

        $recentReceipts = $business->invoices()
            ->with('customer:id,name')
            ->latest('issue_date')
            ->limit(5)
            ->get();

        $recentlyCompleted = $business->invoices()
            ->with('customer:id,name')
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->limit(5)
            ->get();

        $invoicesLast7Days = $business->invoices()
            ->where('issue_date', '>=', now()->subDays(6)->startOfDay())
            ->get();
            
        $completedLast7Days = $business->invoices()
            ->where('status', 'paid')
            ->where('completed_at', '>=', now()->subDays(6)->startOfDay())
            ->get();
            
        $customersLast7Days = $business->customers()
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->get();
            
        $days = collect(range(6, 0))->map(fn($daysAgo) => now()->subDays($daysAgo)->format('Y-m-d'));

        $collectedTrendData = $days->map(function($date) use ($completedLast7Days) {
            return $completedLast7Days->filter(fn($inv) => $inv->completed_at && $inv->completed_at->format('Y-m-d') === $date)->sum('total');
        })->values()->toArray();
        
        $receiptsTrendData = $days->map(function($date) use ($invoicesLast7Days) {
            return $invoicesLast7Days->filter(fn($inv) => $inv->issue_date && $inv->issue_date->format('Y-m-d') === $date)->count();
        })->values()->toArray();
        
        $customersTrendData = $days->map(function($date) use ($customersLast7Days) {
            return $customersLast7Days->filter(fn($cust) => $cust->created_at && $cust->created_at->format('Y-m-d') === $date)->count();
        })->values()->toArray();

        return Inertia::render('Dashboard', [
            'stats' => [
                'collectedThisMonth' => $collectedThisMonth,
                'unpaidAmount' => $unpaidAmount,
                'receiptsThisMonth' => $receiptsThisMonth,
                'customerCount' => $customerCount,
                'collectedTrend' => $collectedTrendData,
                'receiptsTrend' => $receiptsTrendData,
                'customersTrend' => $customersTrendData,
                'trendLabels' => $days->map(fn($date) => \Carbon\Carbon::parse($date)->format('M d'))->values()->toArray(),
            ],
            'recentReceipts' => $recentReceipts,
            'recentlyCompleted' => $recentlyCompleted,
            'currency' => $business->default_currency,
        ]);
    }
}
