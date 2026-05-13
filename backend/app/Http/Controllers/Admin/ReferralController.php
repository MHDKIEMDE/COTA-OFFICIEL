<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function index(Request $request): View
    {
        $query = Referral::with(['referrer', 'referred'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('referrer', fn($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('referred', fn($r) => $r->where('name', 'like', "%{$search}%"));
            });
        }

        $referrals = $query->paginate(25)->withQueryString();

        $stats = [
            'total'          => Referral::count(),
            'completed'      => Referral::where('status', 'completed')->count(),
            'pending'        => Referral::where('status', 'pending')->count(),
            'rewards_given'  => Referral::where('reward_granted', true)->count(),
            'this_month'     => Referral::whereMonth('created_at', now()->month)->count(),
        ];

        // Top parrains
        $topReferrers = User::withCount(['referrals as referral_count' => fn($q) => $q->where('status', 'completed')])
            ->where('referral_code', '!=', null)
            ->having('referral_count', '>', 0)
            ->orderByDesc('referral_count')
            ->take(10)
            ->get();

        return view('admin.referrals.index', compact('referrals', 'stats', 'topReferrers'));
    }
}
