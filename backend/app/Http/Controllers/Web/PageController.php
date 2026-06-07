<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Prediction;
use App\Models\Referral;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{
    /**
     * Live matches page
     */
    public function live()
    {
        // Get live matches
        $liveMatches = Prediction::where('status', 'live')
            ->orderBy('match_time', 'asc')
            ->get();

        // Get upcoming matches for today
        $upcomingMatches = Prediction::whereDate('match_date', today())
            ->where('status', 'pending')
            ->where('match_time', '>', now()->format('H:i:s'))
            ->orderBy('match_time', 'asc')
            ->limit(10)
            ->get();

        $liveCount = $liveMatches->count();

        return view('web.live', compact('liveMatches', 'upcomingMatches', 'liveCount'));
    }

    /**
     * Favorites page
     */
    public function favorites()
    {
        return view('web.favorites');
    }

    /**
     * Competitions / Leagues page
     */
    public function competitions()
    {
        $competitions = \App\Models\Competition::active()
            ->orderBy('priority')
            ->orderBy('name')
            ->get()
            ->groupBy('country');

        return view('web.competitions', compact('competitions'));
    }

    /**
     * Dashboard / Home page
     */
    public function home(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));

        // Get predictions for the selected date
        $allPredictions = Prediction::published()
            ->whereDate('match_date', $date)
            ->orderBy('confidence_stars', 'desc')
            ->orderBy('match_time', 'asc')
            ->get();

        // Group predictions by competition
        $predictions = $allPredictions->groupBy('competition');

        $stats = [
            'today_count' => Prediction::whereDate('match_date', today())->count(),
            'total_predictions' => Prediction::count(),
            'win_rate' => $this->calculateWinRate(),
            'premium_count' => Prediction::where('confidence_stars', '>=', 3)->count(),
        ];

        // Compétitions dynamiques basées sur la date sélectionnée
        // Triées par : matchs live en premier, puis par nombre de matchs
        $favoriteCompetitions = Prediction::select('competition', 'competition_id')
            ->selectRaw('COUNT(*) as match_count')
            ->selectRaw('SUM(CASE WHEN status = "live" THEN 1 ELSE 0 END) as live_count')
            ->whereDate('match_date', $date)
            ->whereNotNull('competition')
            ->groupBy('competition', 'competition_id')
            ->orderByRaw('SUM(CASE WHEN status = "live" THEN 1 ELSE 0 END) DESC')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(10)
            ->get()
            ->map(fn($comp) => [
                'name'  => $comp->competition,
                'id'    => $comp->competition_id,
                'count' => (int) $comp->match_count,
                'live'  => (int) $comp->live_count > 0,
            ]);

        return view('web.dashboard', compact('predictions', 'stats', 'favoriteCompetitions'));
    }

    /**
     * Predictions list page
     */
    public function predictions(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $competition = $request->get('competition');
        $confidence = $request->get('confidence');

        $query = Prediction::query()
            ->whereDate('match_date', $date)
            ->orderBy('confidence', 'desc')
            ->orderBy('match_date', 'asc');

        if ($competition) {
            $query->where('competition_id', $competition);
        }

        if ($confidence) {
            $query->where('confidence', '>=', (int) $confidence);
        }

        $predictions = $query->get();

        // Get unique competitions for filter
        $competitions = Prediction::select('competition', 'competition_id')
            ->distinct()
            ->whereNotNull('competition')
            ->get()
            ->map(fn($p) => ['id' => $p->competition_id, 'name' => $p->competition]);

        $filters = [
            'date' => $date,
            'competition' => $competition,
            'confidence' => $confidence,
        ];

        return view('web.predictions', compact('predictions', 'competitions', 'filters'));
    }

    /**
     * Single prediction detail page
     */
    public function showPrediction(Prediction $prediction)
    {
        return view('web.prediction-detail', compact('prediction'));
    }

    /**
     * History page (public)
     */
    public function history()
    {
        $predictions = Prediction::where('is_published', true)
            ->where('status', '!=', 'pending')
            ->orderBy('match_date', 'desc')
            ->limit(50)
            ->get();

        $stats = [
            'total'    => $predictions->count(),
            'won'      => $predictions->where('status', 'won')->count(),
            'lost'     => $predictions->where('status', 'lost')->count(),
            'pending'  => Prediction::where('status', 'pending')->count(),
            'win_rate' => $this->calculateWinRate(),
        ];

        return view('web.history', compact('predictions', 'stats'));
    }

    /**
     * Statistics page
     */
    public function statistics()
    {
        $won   = Prediction::where('status', 'won')->count();
        $lost  = Prediction::where('status', 'lost')->count();
        $total = $won + $lost;

        $stats = [
            'total_predictions' => Prediction::where('is_published', true)->count(),
            'won'               => $won,
            'lost'              => $lost,
            'pending'           => Prediction::where('status', 'pending')->count(),
            'win_rate'          => $total > 0 ? round(($won / $total) * 100, 1) : 0,
            'avg_odds'          => Prediction::avg('odds') ?? 0,
            'roi'               => $this->calculateROI(),
            'streak'            => $this->calculateStreak(),
            'best_competition'  => $this->getBestCompetition(),
            'best_bet_type'     => $this->getBestBetType(),
        ];

        $byCompetition = Prediction::selectRaw('
                competition as name,
                COUNT(*) as total,
                SUM(CASE WHEN status = "won" THEN 1 ELSE 0 END) as won,
                SUM(CASE WHEN status = "lost" THEN 1 ELSE 0 END) as lost
            ')
            ->whereIn('status', ['won', 'lost'])
            ->groupBy('competition')
            ->get()
            ->map(function ($item) {
                $total = $item->won + $item->lost;
                $item->win_rate = $total > 0 ? round(($item->won / $total) * 100, 1) : 0;
                return $item;
            });

        return view('web.statistics', compact('stats', 'byCompetition'));
    }

    /**
     * Profile page
     */
    public function profile()
    {
        return view('web.profile');
    }

    /**
     * Subscription page
     */
    public function subscription()
    {
        $user = Auth::user();
        
        $currentSubscription = null;
        if ($user && $user->is_premium) {
            $currentSubscription = Subscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->latest()
                ->first();
        }

        $dbPlans = \App\Models\AppConfig::get('app.premium_plans');
        $plans   = $dbPlans ? array_values($dbPlans) : [
            ['key'=>'weekly',  'name'=>'Hebdomadaire','price'=>2000, 'duration'=>'7 jours', 'popular'=>false,'savings'=>null,  'features'=>['Pronostics 3–4 étoiles','Alertes temps réel']],
            ['key'=>'monthly', 'name'=>'Mensuel',     'price'=>5000, 'duration'=>'30 jours','popular'=>true, 'savings'=>'30%', 'features'=>['Tous les pronostics','Alertes temps réel','Stats avancées','Support prioritaire']],
            ['key'=>'annual',  'name'=>'Annuel',      'price'=>40000,'duration'=>'365 jours','popular'=>false,'savings'=>'50%','features'=>['Tous les avantages','Badge VIP','Support WhatsApp dédié']],
        ];

        $dbChannels     = \App\Models\AppConfig::get('payment.channels');
        $paymentChannels = $dbChannels ?? [
            ['emoji'=>'🟠','name'=>'Orange Money','color'=>'#FF6600'],
            ['emoji'=>'🟡','name'=>'MTN MoMo',    'color'=>'#FFCB00'],
            ['emoji'=>'🔵','name'=>'Wave',         'color'=>'#1573E5'],
            ['emoji'=>'🟢','name'=>'Moov Money',   'color'=>'#00A859'],
        ];

        return view('web.subscription', compact('plans', 'currentSubscription', 'paymentChannels'));
    }

    /**
     * Referral page
     */
    public function referral()
    {
        $user = Auth::user();

        if (!$user) {
            $referrals = collect();
            $stats = [
                'total_referrals' => 0,
                'premium_days_earned' => 0,
                'pending_rewards' => 0,
            ];
        } else {
            $referrals = Referral::where('referrer_user_id', $user->id)
                ->with('referred:id,name,created_at')
                ->latest()
                ->get()
                ->map(fn($r) => [
                    'id' => $r->id,
                    'name' => $r->referred->name ?? 'Utilisateur',
                    'created_at' => $r->created_at,
                    'status' => $r->status ?? 'completed',
                ]);

            $stats = [
                'total_referrals' => $referrals->count(),
                'premium_days_earned' => $referrals->where('status', 'completed')->count() * 7,
                'pending_rewards' => $referrals->where('status', 'pending')->count(),
            ];
        }

        return view('web.referral', compact('referrals', 'stats'));
    }

    /**
     * Helper: Calculate win rate
     */
    private function calculateWinRate(): float
    {
        $won = Prediction::where('status', 'won')->count();
        $lost = Prediction::where('status', 'lost')->count();
        $total = $won + $lost;

        return $total > 0 ? round(($won / $total) * 100, 1) : 0;
    }

    /**
     * Helper: Calculate ROI
     */
    private function calculateROI(): float
    {
        $winRate = $this->calculateWinRate();
        $avgOdds = Prediction::where('status', 'won')->avg('odds') ?? 1.5;
        
        return round(($winRate / 100 * $avgOdds * 100) - 100, 1);
    }

    /**
     * Helper: Calculate current streak
     */
    private function calculateStreak(): array
    {
        $recentPredictions = Prediction::whereIn('status', ['won', 'lost'])
            ->orderBy('match_date', 'desc')
            ->limit(20)
            ->pluck('status');

        if ($recentPredictions->isEmpty()) {
            return ['type' => 'none', 'count' => 0];
        }

        $firstStatus = $recentPredictions->first();
        $count = 0;

        foreach ($recentPredictions as $status) {
            if ($status === $firstStatus) {
                $count++;
            } else {
                break;
            }
        }

        return [
            'type' => $firstStatus === 'won' ? 'win' : 'loss',
            'count' => $count,
        ];
    }

    /**
     * Helper: Get best performing competition
     */
    private function getBestCompetition(): ?array
    {
        $best = Prediction::selectRaw('
                competition as name,
                COUNT(*) as total,
                SUM(CASE WHEN status = "won" THEN 1 ELSE 0 END) as won
            ')
            ->whereIn('status', ['won', 'lost'])
            ->groupBy('competition')
            ->having('total', '>=', 5)
            ->get()
            ->map(function ($item) {
                $item->win_rate = round(($item->won / $item->total) * 100, 1);
                return $item;
            })
            ->sortByDesc('win_rate')
            ->first();

        return $best ? ['name' => $best->name, 'win_rate' => $best->win_rate] : null;
    }

    /**
     * Helper: Get best performing bet type
     */
    private function getBestBetType(): ?array
    {
        $best = Prediction::selectRaw('
                bet_type as name,
                COUNT(*) as total,
                SUM(CASE WHEN status = "won" THEN 1 ELSE 0 END) as won
            ')
            ->whereIn('status', ['won', 'lost'])
            ->groupBy('bet_type')
            ->having('total', '>=', 5)
            ->get()
            ->map(function ($item) {
                $item->win_rate = round(($item->won / $item->total) * 100, 1);
                return $item;
            })
            ->sortByDesc('win_rate')
            ->first();

        return $best ? ['name' => $best->name, 'win_rate' => $best->win_rate] : null;
    }

    /**
     * Helper: Get empty stats structure
     */
    private function getEmptyStats(): array
    {
        return [
            'total_predictions' => 0,
            'won' => 0,
            'lost' => 0,
            'pending' => 0,
            'win_rate' => 0,
            'avg_odds' => 0,
            'roi' => 0,
            'streak' => ['type' => 'none', 'count' => 0],
            'best_competition' => null,
            'best_bet_type' => null,
        ];
    }
}
