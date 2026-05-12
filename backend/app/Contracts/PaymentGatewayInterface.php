<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Créer une facture / session de paiement.
     *
     * @param  array{amount: int, description: string, user_id: int, user_email: ?string, user_name: string, user_phone: ?string, plan: string}  $data
     * @return array{success: bool, token?: string, payment_url?: string, error?: string}
     */
    public function createInvoice(array $data): array;

    /**
     * Vérifier le statut d'une transaction via son token/référence.
     *
     * @return array{success: bool, status: string, data?: array, error?: string}
     */
    public function verifyTransaction(string $token): array;

    /**
     * Valider et décoder un webhook entrant.
     * Retourne les données normalisées ou null si invalide.
     *
     * @return array{token: string, status: string, amount: int}|null
     */
    public function parseWebhook(array $payload, string $rawBody, array $headers): ?array;

    /**
     * Prix d'un plan en unité de devise (ex: XOF).
     */
    public function getPlanPrice(string $plan): int;
}
