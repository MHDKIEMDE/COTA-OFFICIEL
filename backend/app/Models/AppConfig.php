<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppConfig extends Model
{
    use HasFactory;

    protected $table = 'app_configs';

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Récupérer une configuration par sa clé
     */
    public static function get(string $key, $default = null)
    {
        $config = self::where('key', $key)->first();
        
        if (!$config) {
            return $default;
        }

        // Convertir selon le type
        return match ($config->type) {
            'integer' => (int) $config->value,
            'float'   => (float) $config->value,
            'boolean' => filter_var($config->value, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($config->value, true),
            default   => $config->value,
        };
    }

    /**
     * Définir une configuration
     */
    public static function set(string $key, $value, string $type = 'string', ?string $description = null): void
    {
        // Convertir la valeur selon le type
        $valueToStore = match ($type) {
            'json' => json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };

        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $valueToStore,
                'type' => $type,
                'description' => $description,
            ]
        );
    }

    /**
     * Récupérer toutes les configurations en format clé-valeur
     */
    public static function allAsArray(): array
    {
        return self::all()->mapWithKeys(function ($config) {
            return [$config->key => self::get($config->key)];
        })->toArray();
    }
}
