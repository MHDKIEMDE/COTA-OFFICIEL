<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Affiche une page « Bientôt disponible » pour TOUT le web (site public + dashboard admin),
 * sans supprimer les routes ni les contrôleurs (rien n'est perdu, 100% réversible).
 *
 * Pour réactiver le web plus tard : retirer ce middleware du groupe 'web' dans bootstrap/app.php.
 * L'API (routes /api/*) n'est pas concernée : elle utilise le groupe 'api', pas 'web'.
 */
class WebComingSoon
{
    public function handle(Request $request, Closure $next): Response
    {
        // Laisse passer les fichiers techniques nécessaires (app links, favicon...).
        if ($request->is('.well-known/*') || $request->is('favicon.*') || $request->is('r/*')) {
            return $next($request);
        }

        return response()->view('coming-soon', [], 200);
    }
}
