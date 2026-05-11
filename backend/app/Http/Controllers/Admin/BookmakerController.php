<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class BookmakerController extends Controller
{
    /**
     * Configuration des bookmakers
     */
    public function index()
    {
        $bookmakers = config('affiliates.bookmakers', []);
        $defaultBonusDays = config('affiliates.default_bonus_days', 7);

        return view('admin.bookmakers.index', compact('bookmakers', 'defaultBonusDays'));
    }

    /**
     * Formulaire de création d'un bookmaker
     */
    public function create()
    {
        return view('admin.bookmakers.create');
    }

    /**
     * Enregistrer un nouveau bookmaker
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string|alpha_dash|max:50',
            'name' => 'required|string|max:100',
            'affiliate_url' => 'required|url',
            'bonus_days' => 'required|integer|min:1|max:365',
            'logo_emoji' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $bookmakers = config('affiliates.bookmakers', []);

        if (isset($bookmakers[$validated['id']])) {
            return back()->withErrors(['id' => 'Ce bookmaker existe déjà.'])->withInput();
        }

        $bookmakers[$validated['id']] = [
            'name' => $validated['name'],
            'affiliate_url' => $validated['affiliate_url'],
            'bonus_days' => $validated['bonus_days'],
            'logo_emoji' => $validated['logo_emoji'] ?? '🎰',
            'color' => $validated['color'] ?? '#6A1B9A',
            'description' => $validated['description'] ?? '',
            'is_active' => $request->boolean('is_active', true),
        ];

        $this->saveBookmakersConfig($bookmakers);

        return redirect()->route('admin.bookmakers.index')
            ->with('success', 'Bookmaker ajouté avec succès.');
    }

    /**
     * Formulaire d'édition
     */
    public function edit(string $id)
    {
        $bookmakers = config('affiliates.bookmakers', []);

        if (!isset($bookmakers[$id])) {
            return redirect()->route('admin.bookmakers.index')
                ->with('error', 'Bookmaker non trouvé.');
        }

        $bookmaker = $bookmakers[$id];
        $bookmaker['id'] = $id;

        return view('admin.bookmakers.edit', compact('bookmaker'));
    }

    /**
     * Mettre à jour un bookmaker
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'affiliate_url' => 'required|url',
            'bonus_days' => 'required|integer|min:1|max:365',
            'logo_emoji' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $bookmakers = config('affiliates.bookmakers', []);

        if (!isset($bookmakers[$id])) {
            return redirect()->route('admin.bookmakers.index')
                ->with('error', 'Bookmaker non trouvé.');
        }

        $bookmakers[$id] = [
            'name' => $validated['name'],
            'affiliate_url' => $validated['affiliate_url'],
            'bonus_days' => $validated['bonus_days'],
            'logo_emoji' => $validated['logo_emoji'] ?? '🎰',
            'color' => $validated['color'] ?? '#6A1B9A',
            'description' => $validated['description'] ?? '',
            'is_active' => $request->boolean('is_active', true),
        ];

        $this->saveBookmakersConfig($bookmakers);

        return redirect()->route('admin.bookmakers.index')
            ->with('success', 'Bookmaker mis à jour avec succès.');
    }

    /**
     * Supprimer un bookmaker
     */
    public function destroy(string $id)
    {
        $bookmakers = config('affiliates.bookmakers', []);

        if (!isset($bookmakers[$id])) {
            return redirect()->route('admin.bookmakers.index')
                ->with('error', 'Bookmaker non trouvé.');
        }

        unset($bookmakers[$id]);

        $this->saveBookmakersConfig($bookmakers);

        return redirect()->route('admin.bookmakers.index')
            ->with('success', 'Bookmaker supprimé avec succès.');
    }

    /**
     * Activer/Désactiver un bookmaker
     */
    public function toggleActive(string $id)
    {
        $bookmakers = config('affiliates.bookmakers', []);

        if (!isset($bookmakers[$id])) {
            return back()->with('error', 'Bookmaker non trouvé.');
        }

        $bookmakers[$id]['is_active'] = !($bookmakers[$id]['is_active'] ?? true);

        $this->saveBookmakersConfig($bookmakers);

        $status = $bookmakers[$id]['is_active'] ? 'activé' : 'désactivé';
        return back()->with('success', "Bookmaker {$status}.");
    }

    /**
     * Sauvegarder la configuration des bookmakers
     */
    private function saveBookmakersConfig(array $bookmakers): void
    {
        $configPath = config_path('affiliates.php');
        
        $content = "<?php\n\nreturn [\n";
        $content .= "    'default_bonus_days' => " . config('affiliates.default_bonus_days', 7) . ",\n\n";
        $content .= "    'bookmakers' => [\n";

        foreach ($bookmakers as $id => $data) {
            $content .= "        '{$id}' => [\n";
            $content .= "            'name' => '" . addslashes($data['name']) . "',\n";
            $content .= "            'affiliate_url' => '" . addslashes($data['affiliate_url']) . "',\n";
            $content .= "            'bonus_days' => " . ($data['bonus_days'] ?? 7) . ",\n";
            $content .= "            'logo_emoji' => '" . addslashes($data['logo_emoji'] ?? '🎰') . "',\n";
            $content .= "            'color' => '" . addslashes($data['color'] ?? '#6A1B9A') . "',\n";
            $content .= "            'description' => '" . addslashes($data['description'] ?? '') . "',\n";
            $content .= "            'is_active' => " . (($data['is_active'] ?? true) ? 'true' : 'false') . ",\n";
            $content .= "        ],\n";
        }

        $content .= "    ],\n";
        $content .= "];\n";

        File::put($configPath, $content);

        // Vider le cache de configuration
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }
}

