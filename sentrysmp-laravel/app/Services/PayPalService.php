<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    private string $baseUrl;
    private string $clientId;
    private string $secret;

    public function __construct()
    {
        $sandbox       = config('services.paypal.sandbox', false);
        $this->baseUrl = $sandbox
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
        $this->clientId = config('services.paypal.client_id', '');
        $this->secret   = config('services.paypal.secret', '');
    }

    private function getAccessToken(Client $client): string
    {
        $response = $client->post("{$this->baseUrl}/v1/oauth2/token", [
            'auth'        => [$this->clientId, $this->secret],
            'form_params' => ['grant_type' => 'client_credentials'],
        ]);
        $data = json_decode((string) $response->getBody(), true);
        return $data['access_token'] ?? throw new \RuntimeException('Failed to get PayPal access token');
    }

    /**
     * @return array{orderId: string, approveUrl: string}
     */
    public function createOrder(string $amount, string $returnUrl, string $cancelUrl): array
    {
        $client      = new Client(['timeout' => 30]);
        $accessToken = $this->getAccessToken($client);

        $payload = [
            'intent'              => 'CAPTURE',
            'purchase_units'      => [['amount' => ['currency_code' => 'EUR', 'value' => $amount]]],
            'application_context' => ['return_url' => $returnUrl, 'cancel_url' => $cancelUrl],
        ];

        $response = $client->post("{$this->baseUrl}/v2/checkout/orders", [
            'headers' => ['Authorization' => "Bearer {$accessToken}", 'Content-Type' => 'application/json'],
            'json'    => $payload,
        ]);

        $data       = json_decode((string) $response->getBody(), true);
        $orderId    = $data['id'] ?? '';
        $approveUrl = '';

        foreach ($data['links'] ?? [] as $link) {
            if ($link['rel'] === 'approve') {
                $approveUrl = $link['href'];
                break;
            }
        }

        return ['orderId' => $orderId, 'approveUrl' => $approveUrl];
    }

    /**
     * @return array{success: bool, amount: float, currency: string, rawResponse: string}
     */
    public function captureOrder(string $orderId): array
    {
        $client      = new Client(['timeout' => 30]);
        $accessToken = $this->getAccessToken($client);

        $response    = $client->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture", [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type'  => 'application/json',
            ],
            'body' => '{}',
        ]);

        $raw  = (string) $response->getBody();
        $data = json_decode($raw, true);

        $amount   = 0.0;
        $currency = 'EUR';

        foreach ($data['purchase_units'] ?? [] as $pu) {
            foreach ($pu['payments']['captures'] ?? [] as $cap) {
                $amount   = (float) ($cap['amount']['value'] ?? 0);
                $currency = strtoupper($cap['amount']['currency_code'] ?? 'EUR');
                break 2;
            }
        }

        return [
            'success'     => ($data['status'] ?? '') === 'COMPLETED',
            'amount'      => $amount,
            'currency'    => $currency,
            'rawResponse' => $raw,
        ];
    }
}
