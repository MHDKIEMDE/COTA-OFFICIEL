<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CouponTab;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class CouponTabController extends Controller
{
    public function index(): View
    {
        $tabs = CouponTab::orderBy('sort_order')->get();

        return view('admin.coupon_tabs.index', compact('tabs'));
    }

    public function update(Request $request, CouponTab $couponTab): RedirectResponse
    {
        $data = $request->validate([
            'label'      => 'required|string|max:40',
            'subtitle'   => 'nullable|string|max:40',
            'sort_order' => 'required|integer|min:0|max:99',
        ]);

        $data['is_active'] = $request->boolean('is_active', false);
        $couponTab->update($data);
        $this->flush();

        return redirect()->route('admin.coupon-tabs.index')
            ->with('success', "✅ Onglet \"{$couponTab->label}\" mis à jour.");
    }

    public function toggle(CouponTab $couponTab): RedirectResponse
    {
        $couponTab->update(['is_active' => ! $couponTab->is_active]);
        $this->flush();

        $state = $couponTab->is_active ? 'activé' : 'désactivé';

        return redirect()->route('admin.coupon-tabs.index')
            ->with('success', "Onglet \"{$couponTab->label}\" {$state}.");
    }

    /** Invalide le cache de l'endpoint mobile pour une prise en compte immédiate. */
    private function flush(): void
    {
        Cache::forget('coupon_tabs_v1');
    }
}
