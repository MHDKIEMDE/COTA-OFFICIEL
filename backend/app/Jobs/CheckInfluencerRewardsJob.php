<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Influencer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckInfluencerRewardsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $influencers = Influencer::where('is_active', true)
            ->where('total_registrations', '>', 0)
            ->get();

        $rewarded = 0;

        foreach ($influencers as $influencer) {
            if ($influencer->checkAndReward()) {
                $rewarded++;
                Log::info("Influencer récompensé automatiquement", [
                    'slug'          => $influencer->slug,
                    'registrations' => $influencer->total_registrations,
                    'reward_days'   => $influencer->reward_value,
                ]);
            }
        }

        Log::info("CheckInfluencerRewardsJob terminé", [
            'checked'  => $influencers->count(),
            'rewarded' => $rewarded,
        ]);
    }
}
