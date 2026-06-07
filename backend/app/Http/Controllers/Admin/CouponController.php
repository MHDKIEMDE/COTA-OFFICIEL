<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(Request $request): View
    {
        $date   = $request->get('date', now()->format('Y-m-d'));
        $status = $request->get('status');

        $query = DB::table('combined_bets')->orderByDesc('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        $coupons = $query->paginate(15);

        // Coupon du jour (daily, publié ou non)
        $todayCoupon = DB::table('combined_bets')
            ->where('type', 'daily')
            ->whereDate('date', $date)
            ->first();

        $counts = [
            'total'   => DB::table('combined_bets')->count(),
            'pending' => DB::table('combined_bets')->where('status', 'pending')->count(),
            'won'     => DB::table('combined_bets')->where('status', 'won')->count(),
            'lost'    => DB::table('combined_bets')->where('status', 'lost')->count(),
        ];

        return view('admin.coupon.index', compact('coupons', 'todayCoupon', 'counts', 'date', 'status'));
    }

    public function publish(int $id): RedirectResponse
    {
        DB::table('combined_bets')->where('id', $id)->update([
            'is_published' => true,
            'published_at' => now(),
            'updated_at'   => now(),
        ]);

        return back()->with('success', '✅ Coupon publié.');
    }

    public function unpublish(int $id): RedirectResponse
    {
        DB::table('combined_bets')->where('id', $id)->update([
            'is_published' => false,
            'published_at' => null,
            'updated_at'   => now(),
        ]);

        return back()->with('success', 'Coupon dépublié.');
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'required|in:pending,won,lost,partial',
        ]);

        DB::table('combined_bets')->where('id', $id)->update([
            'status'     => $data['status'],
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Statut mis à jour.');
    }

    public function destroy(int $id): RedirectResponse
    {
        DB::table('combined_bets')->where('id', $id)->delete();

        return back()->with('success', 'Coupon supprimé.');
    }
}
