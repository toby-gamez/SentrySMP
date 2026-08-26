<?php

namespace App\Services;

use GuzzleHttp\Client;

class StripeService
{
    private string $secretKey;
    private string $apiBase = 'https://api.stripe.com/v1';

    public function __construct()
    {
        $this->secretKey = config('services.stripe.secret', '');
    }

    private function client(): Client
    {
        return new Client([
            'timeout' => 30,
            'auth'    => [$this->secretKey, ''],
        ]);
    }

    /**
     * @return array{sessionId: string, url: string}
     */
    public function createSession(string $amount, string $description, string $successUrl, string $cancelUrl): array
    {
        $amountCents = (int) round((float) $amount * 100);

        $response = $this->client()->post("{$this->apiBase}/checkout/sessions", [
            'form_params' => [
                'mode'                                              => 'payment',
                'success_url'                                       => $successUrl,
                'cancel_url'                                        => $cancelUrl,
                'line_items[0][price_data][currency]'               => 'eur',
                'line_items[0][price_data][product_data][name]'     => $description ?: 'SentrySMP Purchase',
                'line_items[0][price_data][unit_amount]'            => (string) $amountCents,
                'line_items[0][quantity]'                           => '1',
            ],
        ]);

        $data = json_decode((string) $response->getBody(), true);

        return [
            'sessionId' => $data['id'] ?? '',
            'url'       => $data['url'] ?? '',
        ];
    }

    /**
     * @return array{amount: float, currency: string, rawResponse: string, status: string}
     */
    public function retrieveSession(string $sessionId): array
    {
        $response = $this->client()->get("{$this->apiBase}/checkout/sessions/{$sessionId}");
        $raw      = (string) $response->getBody();
        $data     = json_decode($raw, true);

        $amountCents = (int) ($data['amount_total'] ?? 0);
        $currency    = strtoupper($data['currency'] ?? 'EUR');
        $payStatus   = $data['payment_status'] ?? '';

        return [
            'amount'      => $amountCents / 100,
            'currency'    => $currency,
            'rawResponse' => $raw,
            'status'      => $payStatus,
        ];
    }
}
