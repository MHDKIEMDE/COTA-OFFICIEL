<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Détermine la langue de la réponse API selon, par ordre de priorité :
 *   1. la locale enregistrée de l'utilisateur authentifié (users.locale)
 *   2. l'en-tête HTTP Accept-Language
 *   3. la locale par défaut de l'application
 *
 * Seules les langues réellement supportées sont appliquées (FR / EN).
 */
class SetLocale
{
    private const SUPPORTED = ['fr', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolve($request);

        if ($locale !== null) {
            App::setLocale($locale);
        }

        return $next($request);
    }

    private function resolve(Request $request): ?string
    {
        // 1. Préférence de l'utilisateur connecté
        $user = $request->user();
        if ($user && !empty($user->locale) && $this->isSupported($user->locale)) {
            return $this->normalize($user->locale);
        }

        // 2. En-tête Accept-Language (ex. "fr-FR,fr;q=0.9,en;q=0.8")
        $header = $request->getPreferredLanguage(self::SUPPORTED);
        if ($header && $this->isSupported($header)) {
            return $this->normalize($header);
        }

        return null;
    }

    private function isSupported(string $locale): bool
    {
        return in_array($this->normalize($locale), self::SUPPORTED, true);
    }

    private function normalize(string $locale): string
    {
        return strtolower(substr($locale, 0, 2));
    }
}
