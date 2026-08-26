<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Services\CommandQueueService;
use App\Services\PayPalService;
use App\Services\StripeService;
use App\Services\VoucherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PayPalService       $payPal,
        private readonly StripeService       $stripe,
        private readonly CommandQueueService $commandQueue,
        private readonly VoucherService      $voucher,
    ) {}

    // ─── PayPal ─────────────────────────────────────────────────────────────

    public function paypalCreateOrder(Request $request): JsonResponse
    {
        $request->validate([
            'amount'   => 'required|string',
            'username' => 'required|string|max:100',
        ]);

        try {
            $returnUrl = route('payment.paypal.return') . '?username=' . urlencode($request->username)
                . '&items_json=' . urlencode($request->items_json ?? '')
                . '&voucher_code=' . urlencode($request->voucher_code ?? '');
            $cancelUrl = url('/checkout?status=cancelled');

            $amount = str_replace(',', '.', $request->amount);
            $result = $this->payPal->createOrder($amount, $returnUrl, $cancelUrl);

            return response()->json($result);
        } catch (\Throwable $e) {
            Log::error('PayPal createOrder error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function paypalReturn(Request $request): RedirectResponse
    {
        $token       = $request->query('token') ?? $request->query('orderId', '');
        $username    = $request->query('username', '');
        $itemsJson   = $request->query('items_json', '');
        $voucherCode = $request->query('voucher_code', '');

        if (!$token) {
            return redirect('/checkout?status=error');
        }

        try {
            $capture = $this->payPal->captureOrder($token);

            $tx = PaymentTransaction::create([
                'provider'                => 'PayPal',
                'provider_transaction_id' => $token,
                'amount'                  => $capture['amount'],
                'currency'                => $capture['currency'],
                'minecraft_username'      => $username,
                'items_json'              => $itemsJson,
                'status'                  => 'captured',
                'raw_response'            => $capture['rawResponse'],
                'created_at'              => now(),
            ]);

            $cartItems = $this->parseCartItems($itemsJson);
            if (!empty($cartItems)) {
                $this->commandQueue->dispatchForTransaction($tx, $cartItems);
            }

            if ($voucherCode) {
                $this->voucher->recordUsage($voucherCode, $username);
            }

            return redirect('/checkout?status=success&orderId=' . urlencode($token));
        } catch (\Throwable $e) {
            Log::error('PayPal return error: ' . $e->getMessage());
            return redirect('/checkout?status=error');
        }
    }

    // ─── Stripe ─────────────────────────────────────────────────────────────

    public function stripeCreateSession(Request $request): JsonResponse
    {
        $request->validate([
            'amount'   => 'required|string',
            'username' => 'required|string|max:100',
        ]);

        try {
            $amount    = str_replace(',', '.', $request->amount);
            $username  = $request->username;
            $itemsJson = $request->items_json ?? '';
            $voucher   = $request->voucher_code ?? '';

            $successUrl = route('payment.stripe.return')
                . '?session_id={CHECKOUT_SESSION_ID}'
                . '&username=' . urlencode($username)
                . '&items_json=' . urlencode($itemsJson)
                . '&voucher_code=' . urlencode($voucher);
            $cancelUrl  = url('/checkout?status=cancelled');
            $desc       = "SentrySMP Purchase for {$username}";

            $result = $this->stripe->createSession($amount, $desc, $successUrl, $cancelUrl);

            return response()->json($result);
        } catch (\Throwable $e) {
            Log::error('Stripe createSession error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function stripeReturn(Request $request): RedirectResponse
    {
        $sessionId   = $request->query('session_id', '');
        $username    = $request->query('username', '');
        $itemsJson   = $request->query('items_json', '');
        $voucherCode = $request->query('voucher_code', '');

        if (!$sessionId) {
            return redirect('/checkout?status=error');
        }

        try {
            $session = $this->stripe->retrieveSession($sessionId);

            $tx = PaymentTransaction::create([
                'provider'                => 'Stripe',
                'provider_transaction_id' => $sessionId,
                'amount'                  => $session['amount'],
                'currency'                => $session['currency'],
                'minecraft_username'      => $username,
                'items_json'              => $itemsJson,
                'status'                  => 'succeeded',
                'raw_response'            => $session['rawResponse'],
                'created_at'              => now(),
            ]);

            $cartItems = $this->parseCartItems($itemsJson);
            if (!empty($cartItems)) {
                $this->commandQueue->dispatchForTransaction($tx, $cartItems);
            }

            if ($voucherCode) {
                $this->voucher->recordUsage($voucherCode, $username);
            }

            return redirect('/checkout?status=success&session_id=' . urlencode($sessionId));
        } catch (\Throwable $e) {
            Log::error('Stripe return error: ' . $e->getMessage());
            return redirect('/checkout?status=error');
        }
    }

    // ─── Voucher ─────────────────────────────────────────────────────────────

    public function validateVoucher(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string', 'items' => 'array']);
        $result = $this->voucher->validate($request->code, $request->items ?? []);
        return response()->json($result);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function parseCartItems(string $json): array
    {
        if (!$json) {
            return [];
        }
        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable) {
            return [];
        }
    }
}
