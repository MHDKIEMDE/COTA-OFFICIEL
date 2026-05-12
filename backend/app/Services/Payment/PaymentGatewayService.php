<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\AppConfig;
use RuntimeException;

/**
 * Résout dynamiquement le provider de paiement actif depuis app_configs.
 * Aucun nom de provider n'est codé en dur — tout vient de la base.
 *
 * Utilisation :
 *   app(PaymentGatewayService::class)->gateway()->createInvoice([...]);
 *   app(PaymentGatewayService::class)->activeProvider(); // => 'paydunya'
 */
class PaymentGatewayService
{
    /** @var array<string, class-string<PaymentGatewayInterface>> */
    private array $drivers = [];

    public function __construct()
    {
        // Enregistrer les drivers disponibles.
        // Pour ajouter un nouveau provider : créer la classe et l'enregistrer ici.
        $this->register('paydunya', \App\Services\Payment\Drivers\PaydunyaDriver::class);
        $this->register('cinetpay', \App\Services\Payment\Drivers\CinetpayDriver::class);
    }

    /** Enregistrer un driver (peut être appelé depuis un ServiceProvider) */
    public function register(string $slug, string $class): void
    {
        $this->drivers[$slug] = $class;
    }

    /** Slug du provider actif en base */
    public function activeProvider(): string
    {
        return (string) AppConfig::get('payment.active_provider', '');
    }

    /** Config complète du provider actif */
    public function activeConfig(): array
    {
        $slug      = $this->activeProvider();
        $providers = AppConfig::get('payment.providers', []);

        foreach ($providers as $p) {
            if (($p['slug'] ?? '') === $slug) {
                return $p;
            }
        }

        return [];
    }

    /** Résoudre et instancier le gateway actif */
    public function gateway(): PaymentGatewayInterface
    {
        $slug = $this->activeProvider();

        if ($slug === '') {
            throw new RuntimeException('Aucun provider de paiement actif. Configurez payment.active_provider dans le dashboard admin.');
        }

        if (!isset($this->drivers[$slug])) {
            throw new RuntimeException("Provider de paiement inconnu : \"{$slug}\". Drivers disponibles : " . implode(', ', array_keys($this->drivers)));
        }

        $config = $this->activeConfig();
        $class  = $this->drivers[$slug];

        return new $class($config);
    }

    /** Liste des drivers enregistrés */
    public function availableDrivers(): array
    {
        return array_keys($this->drivers);
    }

    /** Tous les providers configurés en base */
    public function configuredProviders(): array
    {
        return AppConfig::get('payment.providers', []);
    }
}
