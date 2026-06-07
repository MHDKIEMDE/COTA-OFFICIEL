<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Subscription::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan')) {
            $query->where('plan', $request->plan);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%"));
        }

        $subscriptions = $query->paginate(25)->withQueryString();

        $stats = [
            'total'           => Subscription::count(),
            'active'          => Subscription::where('status', 'active')->where('expires_at', '>', now())->count(),
            'pending'         => Subscription::where('payment_status', 'pending')->count(),
            'revenue_month'   => Subscription::where('payment_status', 'completed')
                                    ->whereMonth('created_at', now()->month)
                                    ->sum('amount'),
            'revenue_total'   => Subscription::where('payment_status', 'completed')->sum('amount'),
            'weekly_count'    => Subscription::where('plan', 'weekly')->count(),
            'monthly_count'   => Subscription::where('plan', 'monthly')->count(),
            'quarterly_count' => Subscription::where('plan', 'quarterly')->count(),
        ];

        $revenueChart = $this->getRevenueChartData();

        $users = User::orderBy('name')->select('id', 'name', 'email', 'phone')->get();

        return view('admin.subscriptions.index', compact('subscriptions', 'stats', 'revenueChart', 'users'));
    }

    public function show(Subscription $subscription): View
    {
        $subscription->load('user');
        return view('admin.subscriptions.show', compact('subscription'));
    }

    public function grantManual(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'plan'    => 'required|in:weekly,monthly,quarterly',
            'reason'  => 'required|string|max:255',
        ]);

        $plans = ['weekly' => 7, 'monthly' => 30, 'quarterly' => 90];
        $days  = $plans[$request->plan];

        Subscription::create([
            'user_id'        => $request->user_id,
            'plan'           => $request->plan,
            'amount'         => 0,
            'currency'       => 'XOF',
            'status'         => 'active',
            'payment_method' => 'admin',
            'payment_status' => 'completed',
            'starts_at'      => now(),
            'expires_at'     => now()->addDays($days),
            'payment_details' => json_encode(['reason' => $request->reason]),
        ]);

        User::find($request->user_id)->update([
            'is_premium'             => true,
            'subscription_expires_at' => now()->addDays($days),
        ]);

        return back()->with('success', 'Abonnement accordé manuellement.');
    }

    public function cancel(Subscription $subscription): RedirectResponse
    {
        $subscription->update([
            'status'              => 'cancelled',
            'cancelled_at'        => now(),
            'cancellation_reason' => 'Annulé par admin',
        ]);

        return back()->with('success', 'Abonnement annulé.');
    }

    private function getRevenueChartData(): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month  = now()->subMonths($i);
            $amount = Subscription::where('payment_status', 'completed')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');
            $data[] = [
                'month'  => $month->format('M Y'),
                'amount' => (int) $amount,
            ];
        }
        return $data;
    }
}
