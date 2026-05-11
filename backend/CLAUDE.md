# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

COTA Backend is a Laravel 12 REST API for an automated football prediction mobile application. The system integrates with API-Football to fetch match data and uses a sophisticated 9-criteria prediction algorithm to generate betting predictions with confidence scores.

**Stack**: Laravel 12.x, MySQL/SQLite, Redis (Predis), Laravel Sanctum, Guzzle HTTP Client

## Common Commands

### Development Server
```bash
# Start Laravel development server
php artisan serve

# Start with queue worker, logs, and Vite (all services)
composer dev

# Start queue worker only
php artisan queue:work
```

### Database
```bash
# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Fresh migration (drops all tables)
php artisan migrate:fresh
```

### Testing
```bash
# Run PHPUnit tests
php artisan test
# or
composer test

# Run specific test
php artisan test --filter=TestName
```

### Cache Management
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Clear API-Football cache specifically
php artisan football:clear-cache
```

### API-Football Integration
```bash
# Test API-Football connection and fetch Premier League matches
php artisan football:test

# Fetch today's matches from configured leagues
php artisan football:fetch-today
```

### Prediction Algorithm
```bash
# Test prediction algorithm on upcoming matches (shows top 3)
php artisan prediction:test

# Test specific match by ID
php artisan prediction:test {match_id}

# Generate predictions for all upcoming matches (next 24h)
php artisan prediction:generate
```

### Code Quality
```bash
# Run Laravel Pint (code formatter)
./vendor/bin/pint

# Run Pint on specific file
./vendor/bin/pint app/Services/PredictionAlgorithmService.php
```

## Architecture & Key Concepts

### Prediction Algorithm (PredictionAlgorithmService)

The core of COTA is a weighted 9-criteria prediction algorithm (v3.0) located in `app/Services/PredictionAlgorithmService.php`:

**Weighted Criteria (total 100 points)**:
1. **Recent Form (25%)** - Analyzes last 10 matches, includes win streak bonuses
2. **Head-to-Head (20%)** - Last 8 direct confrontations with temporal weighting
3. **Home/Away Performance (15%)** - Separate analysis of home/away records
4. **League Position (12%)** - Current standings and ranking gap
5. **Goal Statistics (10%)** - Goals scored/conceded, BTTS, clean sheets
6. **Match Time (8%)** - Optimal playing hours (prime time bonus)
7. **Weather Conditions (5%)** - Temperature, precipitation, wind (OpenWeatherMap)
8. **Shots on Target (3%)** - Recent shot accuracy and efficiency
9. **Physical Form (2%)** - Fatigue analysis and injury tracking

**Confidence Levels**:
- 85-100 points: 4 stars (Very High) - Premium predictions
- 70-84 points: 3 stars (High) - Premium predictions
- 60-69 points: 2 stars (Medium)
- 50-59 points: 1 star (Low)
- <50 points: Not published

**Prediction Output**: Returns bet type (1X2, BTTS, Over/Under, Handicap, Double Chance), estimated odds, detailed score breakdown, and textual analysis.

### API-Football Integration (FootballApiService)

`app/Services/FootballApiService.php` handles all external API calls with:
- **Rate Limiting**: Tracks daily/minute request counts (free plan: 100 req/day, 30 req/min)
- **Smart Caching**: Different TTLs for different data types (fixtures: 5min, standings: 24h)
- **Error Handling**: Graceful fallbacks with comprehensive logging

Key methods:
- `getUpcomingMatches()` - Fetch future matches by date range and league
- `getLiveMatches()` - Real-time scores
- `getHeadToHead()` - Direct confrontation history
- `getTeamStatistics()` - Season stats per team
- `getStandings()` - League tables

### Database Schema

8 core tables (see migrations in `database/migrations/`):
- `users` - Authentication (phone/email OTP, Facebook, referral codes, premium status)
- `matches` - Match data from API-Football (home/away teams, scores, status, venue)
- `predictions` - Generated predictions (not in migrations yet, needs creation)
- `subscriptions` - Premium payments via Paydunya (Wave, Orange Money, MTN, Moov)
- `affiliations_bonus` - Bookmaker tracking links (Betwinner, 1xBet) with 7-day premium bonuses
- `referrals` - User referral system
- `feedbacks` - User bug reports and suggestions
- `statistics` - Daily prediction performance metrics
- `combined_bets` - Daily combined bet suggestions

### Scheduled Jobs (routes/console.php)

Automated tasks configured with Laravel Scheduler:
- `FetchMatchesJob` - Hourly: Sync matches from API-Football
- `GeneratePredictionsJob` - Daily at 8:00 and 20:00: Run prediction algorithm
- `UpdateLiveScoresJob` - Every 5 minutes: Update match scores
- `UpdatePredictionResultsJob` - Hourly: Mark predictions as won/lost

To run scheduler in development: `php artisan schedule:work`

### API Routes Structure (routes/api.php)

**Public Routes**:
- `GET /api/health` - Health check
- `POST /api/auth/send-otp` - Request OTP code
- `POST /api/auth/verify-otp` - Verify OTP and login
- `POST /api/auth/facebook` - Facebook OAuth login
- `GET /api/predictions/today` - Today's predictions (guest mode allowed)
- `GET /api/predictions/{id}` - Single prediction details

**Protected Routes** (require `auth:sanctum`):
- `GET /api/auth/me` - Current user profile
- `POST /api/auth/logout` - Invalidate token
- `GET /api/predictions/history` - User's prediction history
- `GET /api/predictions/statistics` - Performance stats
- `POST /api/predictions/feedback` - Submit feedback

**Future Routes** (commented in api.php):
- Subscriptions, Referrals, Statistics, Feedback, User RGPD endpoints

### Environment Configuration

**Required Environment Variables**:
```env
# API-Football
FOOTBALL_API_KEY=your_key_here
FOOTBALL_API_PLAN=free  # or premium
FOOTBALL_API_TIMEZONE=Africa/Ouagadougou

# OpenWeatherMap (for weather criterion)
OPENWEATHERMAP_KEY=your_key_here

# Redis (for cache/queue)
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

# Database (SQLite default, MySQL for production)
DB_CONNECTION=sqlite
```

### Key Models

- **User** (`app/Models/User.php`) - Extended with COTA fields: `is_premium`, `subscription_expires_at`, `referral_code`, `otp_code`, `otp_expires_at`
- **Match** (`app/Models/Match.php`) - Match data with API IDs, scores, status, venue
- **Prediction** - To be created, stores generated predictions

### Rate Limit Management

API-Football free plan has strict limits (100 req/day). The system:
1. Checks rate limits before each request
2. Throws exception if limit exceeded
3. Caches aggressively to minimize API calls
4. Logs usage stats accessible via `$footballApi->getUsageStats()`

**Best Practice**: Always test with `php artisan football:test` before running bulk operations.

### Authentication Flow

1. User enters phone/email
2. Backend sends OTP via Twilio/Termii (SMS) or email
3. User submits OTP code
4. Backend verifies and returns Sanctum token
5. Token used in `Authorization: Bearer {token}` header for protected routes

Alternative: Facebook OAuth via `POST /api/auth/facebook`

## Development Workflow

### Adding a New Prediction Criterion

1. Add weight constant in `PredictionAlgorithmService::WEIGHTS`
2. Create `calculateXxxScore()` method returning float (0 to max weight)
3. Update `generatePrediction()` to include new score in total calculation
4. Update `generateAnalysis()` to add textual insights for the criterion
5. Test with `php artisan prediction:test`

### Creating a New Job

```bash
php artisan make:job ProcessPredictionsJob
```

Register in `routes/console.php`:
```php
Schedule::job(new \App\Jobs\ProcessPredictionsJob())->daily();
```

### Adding API Endpoints

1. Create controller: `php artisan make:controller Api/XxxController`
2. Add routes in `routes/api.php` under appropriate group (public/protected)
3. Use FormRequest for validation: `php artisan make:request StoreXxxRequest`
4. Return JSON responses with proper HTTP status codes

### Testing Predictions End-to-End

```bash
# 1. Fetch real match data
php artisan football:fetch-today

# 2. Generate predictions
php artisan prediction:generate

# 3. Check predictions via API
curl http://127.0.0.1:8000/api/predictions/today
```

## Important Notes

- **Never commit `.env`** - Contains API keys and secrets
- **API-Football quota is precious** - Always use cache, avoid testing in loops
- **Prediction algorithm uses Match model** - Ensure matches table is populated before testing predictions
- **Sanctum tokens are stateless** - No session management needed
- **All dates use Carbon** - Timezone configured in `config/football-api.php`
- **Jobs require Redis** - Install Redis or use `QUEUE_CONNECTION=database` for development
- **Migrations are sequential** - Check timestamps when creating new ones to ensure proper order

## Project Context

This is the backend for a Flutter mobile app. The frontend consumes this API for:
- User authentication (OTP/Facebook)
- Daily football predictions with confidence ratings
- Premium subscription management
- Referral and bookmaker affiliation tracking
- Performance statistics and user feedback

**Current Status**: MVP phase with API-Football and Sportradar integration complete, prediction algorithm v3.0 operational, authentication and basic endpoints implemented. Pending features include payment integration (Paydunya), full referral system, competition filtering backend, and admin dashboard.

## Match Selection and League Prioritization

The prediction generation system (`GenerateSportradarPredictions.php`) implements intelligent league prioritization to ensure quality predictions:

**League Priority Tiers**:
1. **Tier 1**: Major European leagues (Premier League, La Liga, Serie A, Bundesliga, Ligue 1)
2. **Tier 2**: Secondary major leagues (UEFA competitions, Liga Portugal, Eredivisie, Saudi Pro League)
3. **Tier 3**: International competitions and Championship (Africa Cup, Copa America, Championship)
4. **Tier 4**: Lower UK leagues (League One, League Two)
5. **Tier 5**: Unlisted leagues

When generating predictions with `php artisan predictions:generate-sportradar`, the system:
- Groups all available matches by tier
- Selects matches prioritizing higher tiers first
- Displays league distribution in console output
- Ensures users get predictions from the most popular and reliable leagues

To modify league priorities, edit the `$leaguePriorities` array in `app/Console/Commands/GenerateSportradarPredictions.php:prioritizePopularLeagues()`.

## Available Artisan Commands

### Sportradar Integration
```bash
# Test Sportradar API connection
php artisan test:sportradar

# Generate predictions from Sportradar matches
php artisan predictions:generate-sportradar --date=2025-12-29 --limit=10
```

## Frontend Integration

The Flutter app connects to this backend via:
- Base URL configured in `lib/core/api/api_client.dart`
- Dio HTTP client with interceptors for authentication
- Riverpod state management for API data
- Competition filtering system (frontend ready, backend pending implementation)


 Searched for 1 pattern, read 2 files, listed 1 directory (ctrl+o to expand)                    
                                                                                                 
⏺ Bonne idée. Je vais implémenter les deux fonctionnalités :                                     
  1. Matchs populaires — filtrage par ligues top (PL, Liga, Serie A, Champions League, etc.)     
  2. Coupon IA — l'algo sélectionne les 4-5 meilleures prédictions et génère un combiné avec cote
   totale                                                                                        
                                                                                                 
  Laisse-moi d'abord lire la config et la fin de l'algorithme :                                  
                                                                                                 
  Read 2 files (ctrl+o to expand)                                                                
                                                                                                 
⏺ J'ai tout ce qu'il faut. Voici le plan d'implémentation : 
                                                                                          
  1. Config — ajouter les ligues populaires avec tiers                                           
  2. FootballApiService — méthode getPopularMatches() qui filtre par ligue populaire             
  3. PredictionAlgorithmService — méthode generateDailyCoupon() qui sélectionne les 4-5          
  meilleures picks                                                                               
  4. Commande prediction:coupon — affichage terminal                                             
  5. Endpoint API /api/predictions/coupon                                                        
                                                                                                 
  Read 1 file (ctrl+o to expand)                                                                 
                                                                                                 
⏺ Maintenant j'implémente tout en parallèle :               
                                                                                          
⏺ Update(config/football-api.php)                                                         
  ⎿  Added 28 lines, removed 1 line                                                              
      60          2 => 'UEFA Champions League',                                                  
      61          3 => 'UEFA Europa League',                                                     
      62          848 => 'UEFA Conference League',                                               
      63 -        // Ajoutez d'autres ligues selon vos besoins                                   
      63      ],                                                                               
      64                                                                                         
      65 +    // Ligues populaires avec niveau de priorité (tier 1 = plus populaire)      
      66 +    'popular_leagues' => [                                                           
      67 +        // Tier 1 — Top 5 européennes                                           
      68 +        2   => ['name' => 'Champions League',      'country' => 'Europe',       
         +'tier' => 1],                                                                   
      69 +        39  => ['name' => 'Premier League',         'country' => 'England',     
         + 'tier' => 1],                                                                  
      70 +        140 => ['name' => 'La Liga',                'country' => 'Spain',       
         + 'tier' => 1],                                                                  
      71 +        135 => ['name' => 'Serie A',                'country' => 'Italy',       
         + 'tier' => 1],                                                                  
      72 +        78  => ['name' => 'Bundesliga',             'country' => 'Germany',     
         + 'tier' => 1],                                                                  
      73 +        61  => ['name' => 'Ligue 1',                'country' => 'France',      
         + 'tier' => 1],                                                                  
      74 +        // Tier 2 — Grandes compétitions européennes & ligues secondaires       
      75 +        3   => ['name' => 'Europa League',          'country' => 'Europe',      
         + 'tier' => 2],                                                                  
      76 +        848 => ['name' => 'Conference League',      'country' => 'Europe',      
         + 'tier' => 2],                                                                  
      77 +        94  => ['name' => 'Liga Portugal',          'country' => 'Portugal',    
         + 'tier' => 2],                                                                  
      78 +        88  => ['name' => 'Eredivisie',             'country' => 'Netherlands', 
         + 'tier' => 2],                                                                  
      79 +        144 => ['name' => 'Pro League',             'country' => 'Belgium',     
         + 'tier' => 2],                                                                  
      80 +        179 => ['name' => 'Scottish Premiership',   'country' => 'Scotland',    
         + 'tier' => 2],                                                                  
      81 +        307 => ['name' => 'Saudi Pro League',       'country' => 'Saudi Arabia',
         + 'tier' => 2],                                                                  
      82 +        // Tier 3 — Ligues majeures hors Europe                                 
      83 +        253 => ['name' => 'MLS',                   'country' => 'USA',          
         +'tier' => 3],                                                                   
      84 +        71  => ['name' => 'Brasileirao',            'country' => 'Brazil',      
         + 'tier' => 3],                                                                  
      85 +        262 => ['name' => 'Liga MX',               'country' => 'Mexico',       
         +'tier' => 3],                                                                   
      86 +        203 => ['name' => 'Süper Lig',             'country' => 'Turkey',       
         +'tier' => 3],                                                                   
      87 +        // Tier 4 — Afrique & reste du monde                                    
      88 +        12  => ['name' => 'AFCON',                 'country' => 'Africa',       
         +'tier' => 4],                                                                   
      89 +        17  => ['name' => 'AFCON Qualification',   'country' => 'Africa',       
         +'tier' => 4],                                                                   
      90 +        29  => ['name' => 'CAF Champions League',  'country' => 'Africa',       
         +'tier' => 4],                                                                   
      91 +    ],                                                                          
      92 +                                                                                
      93      // Endpoints disponibles
      94      'endpoints' => [
      95          'fixtures' => '/fixtures',

⏺ Update(app/Services/FootballApiService.php)
  ⎿  Added 36 lines                                                                       
      137      }                         
      138                                                                                        
      139      /**
      140 +     * Récupérer uniquement les matchs populaires du jour                             
      141 +     * Filtre les fixtures par les ligues configurées dans popular_leagues     
      142 +     */                                                                               
      143 +    public function getPopularMatches(?string $date = null): array             
      144 +    {                                                                          
      145 +        $response = $this->getUpcomingMatches(1);                              
      146 +        $fixtures = $response['response'] ?? [];                               
      147 +                                                                               
      148 +        if (empty($fixtures)) {                                                
      149 +            return ['response' => [], 'popular_leagues_only' => true];         
      150 +        }                                                                      
      151 +                                                                               
      152 +        $leagueConfig = config('football-api.popular_leagues', []);            
      153 +        $popularIds   = array_keys($leagueConfig);                             
      154 +                                                                               
      155 +        $popular = [];                                                         
      156 +        foreach ($fixtures as $fixture) {                                      
      157 +            $leagueId = $fixture['league']['id'] ?? null;                      
      158 +            if ($leagueId && in_array($leagueId, $popularIds)) {               
      159 +                $fixture['_tier'] = $leagueConfig[$leagueId]['tier'];          
      160 +                $popular[] = $fixture;                                         
      161 +            }                                                                  
      162 +        }                                                                      
      163 +                                                                               
      164 +        // Trier par tier croissant (1 = plus populaire en premier)            
      165 +        usort($popular, fn($a, $b) => $a['_tier'] <=> $b['_tier']);            
      166 +                                                                               
      167 +        Log::info("API-Football: Popular matches filtered", [                  
      168 +            'total'   => count($fixtures),                                     
      169 +            'popular' => count($popular),                                      
      170 +        ]);                                                                    
      171 +                                                                               
      172 +        return ['response' => $popular, 'popular_leagues_only' => true];       
      173 +    }                                                                          
      174 +                                                                               
      175 +    /**                                                                        
      176       * Récupérer les matchs en direct
      177       */
      178      public function getLiveMatches(): ?array

⏺ Maintenant la méthode generateDailyCoupon dans l'algorithme :
                                                                                          
⏺ Update(app/Services/PredictionAlgorithmService.php)
  ⎿  Added 106 lines                                                                             
      616          });                  
      617      }                                                                                 
      618                                                   
      619 +    // ==================== COUPON IA ====================                            
      620 +                                                                               
      621 +    /**                                                                               
      622 +     * Generer un coupon combiné à partir d'une liste de fixtures.             
      623 +     *                                                                         
      624 +     * Logique :                                                               
      625 +     * - Génère une prédiction pour chaque fixture                             
      626 +     * - Filtre celles avec confidence >= $minConfidence                       
      627 +     * - Sélectionne les meilleures en privilégiant 1 match par ligue          
      628 +     * - Calcule la cote totale et le gain potentiel pour une mise de 1 000 FCF
          +A                                                                              
      629 +     *                                                                         
      630 +     * @param array $fixtures      Liste de fixtures au format API-Football    
      631 +     * @param int   $minPicks      Nombre minimum de sélections requises       
      632 +     * @param int   $maxPicks      Nombre maximum de sélections dans le coupon 
      633 +     * @param float $minConfidence Score minimum de confiance pour être inclus 
      634 +     */                                                                        
      635 +    public function generateDailyCoupon(                                       
      636 +        array $fixtures,                                                       
      637 +        int   $minPicks      = 4,                                              
      638 +        int   $maxPicks      = 5,                                              
      639 +        float $minConfidence = 60.0                                            
      640 +    ): array {                                                                 
      641 +        $candidates = [];                                                      
      642 +                                                                               
      643 +        foreach ($fixtures as $fixture) {                                      
      644 +            $prediction = $this->generatePrediction($fixture);                 
      645 +                                                                               
      646 +            if ($prediction['confidence'] >= $minConfidence && $prediction['sho
          +uld_publish']) {                                                               
      647 +                $candidates[] = [                                              
      648 +                    'fixture'    => $fixture,                                  
      649 +                    'prediction' => $prediction,                               
      650 +                ];                                                             
      651 +            }                                                                  
      652 +        }                                                                      
      653 +                                                                               
      654 +        if (count($candidates) < $minPicks) {                                  
      655 +            return [                                                           
      656 +                'success'   => false,                                          
      657 +                'message'   => 'Pas assez de matchs avec une confiance suffisan
          +te (' . count($candidates) . '/' . $minPicks . ' requis)',                     
      658 +                'analyzed'  => count($fixtures),                               
      659 +                'qualified' => count($candidates),                             
      660 +            ];                                                                 
      661 +        }                                                                      
      662 +                                                                               
      663 +        // Trier par confiance décroissante                                    
      664 +        usort($candidates, fn($a, $b) => $b['prediction']['confidence'] <=> $a[
          +'prediction']['confidence']);                                                  
      665 +                                                                               
      666 +        // Sélectionner en prioritisant 1 match par ligue                      
      667 +        $selected    = [];                                                     
      668 +        $usedLeagues = [];                                                     
      669 +                                                                               
      670 +        foreach ($candidates as $c) {                                          
      671 +            if (count($selected) >= $maxPicks) break;                          
      672 +            $leagueId = $c['fixture']['league']['id'] ?? 'unknown';            
      673 +            if (!in_array($leagueId, $usedLeagues)) {                          
      674 +                $selected[]    = $c;                                           
      675 +                $usedLeagues[] = $leagueId;                                    
      676 +            }                                                                  
      677 +        }                                                                      
      678 +                                                                               
      679 +        // Compléter si on n'a pas encore atteint $minPicks                    
      680 +        if (count($selected) < $minPicks) {                                    
      681 +            foreach ($candidates as $c) {                                      
      682 +                if (count($selected) >= $minPicks) break;                      
      683 +                $alreadyIn = array_filter($selected, fn($s) => $s === $c);     
      684 +                if (empty($alreadyIn)) {                                       
      685 +                    $selected[] = $c;                                          
      686 +                }                                                              
      687 +            }                                                                  
      688 +        }                                                                      
      689 +                                                                               
      690 +        // Calculs coupon                                                      
      691 +        $totalOdds     = array_reduce($selected, fn($carry, $p) => $carry * (fl
          +oat) $p['prediction']['odds'], 1.0);                                           
      692 +        $avgConfidence = array_sum(array_map(fn($p) => $p['prediction']['confid
          +ence'], $selected)) / count($selected);                                        
      693 +        $couponStars   = $this->calculateStars($avgConfidence);                
      694 +                                                                               
      695 +        $picks = array_map(function (array $item): array {                     
      696 +            $f = $item['fixture'];                                             
      697 +            $p = $item['prediction'];                                          
      698 +            return [                                                           
      699 +                'match'       => ($f['teams']['home']['name'] ?? '?') . ' vs ' 
          +. ($f['teams']['away']['name'] ?? '?'),                                        
      700 +                'league'      => $f['league']['name'] ?? 'Unknown',            
      701 +                'date'        => $f['fixture']['date'] ?? null,                
      702 +                'prediction'  => $p['outcome'],                                
      703 +                'type'        => $p['type'],                                   
      704 +                'odds'        => $p['odds'],                                   
      705 +                'confidence'  => round($p['confidence'], 1),                   
      706 +                'stars'       => $p['stars'],                                  
      707 +                'is_premium'  => $p['is_premium'],                             
      708 +            ];                                                                 
      709 +        }, $selected);                                                         
      710 +                                                                               
      711 +        return [                                                               
      712 +            'success'            => true,                                      
      713 +            'picks'              => $picks,                                    
      714 +            'matches_count'      => count($selected),                          
      715 +            'total_odds'         => round($totalOdds, 2),                      
      716 +            'avg_confidence'     => round($avgConfidence, 1),                  
      717 +            'stars'              => $couponStars,                              
      718 +            'potential_gain_1000'=> (int) round($totalOdds * 1000),            
      719 +            'analyzed'           => count($fixtures),                          
      720 +            'qualified'          => count($candidates),                        
      721 +            'generated_at'       => Carbon::now()->toIso8601String(),          
      722 +        ];                                                                     
      723 +    }                                                                          
      724 +                                                                               
      725      private function getDefaultPrediction(): array
      726      {
      727          return [

⏺ Maintenant la commande artisan :      
                                                                                          
⏺ Write(app/Console/Commands/GenerateDailyCoupon.php)
  ⎿  Wrote 130 lines to app/Console/Commands/GenerateDailyCoupon.php                             
       1 <?php                           
       2                                                                                         
       3 namespace App\Console\Commands;                    
       4                                                                                         
       5 use App\Services\FootballApiService;               
       6 use App\Services\PredictionAlgorithmService;                                            
       7 use Illuminate\Console\Command;                    
       8 use Carbon\Carbon;                                                                    
       9
      10 class GenerateDailyCoupon extends Command
     … +120 lines (ctrl+o to expand)
  ⎿  You've hit your limit · resets 6pm (Africa/Ouagadougou)
                                         
✻ Cogitated for 2m 29s                                                                    

❯ /rate-limit-options             