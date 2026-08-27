<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Services\CommandQueueService;
use App\Services\PayPalService;
use App\Services\StripeService;
use App\Services\VoucherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
            'items_json'   => 'required|string',
            'username'     => 'required|string|max:100',
            'voucher_code' => 'nullable|string',
        ]);

        try {
            $cartItems   = $this->parseCartItems($request->items_json);
            $voucherCode = trim($request->voucher_code ?? '');
            $amount      = $this->computeServerAmount($cartItems, $voucherCode ?: null);

            if (empty($cartItems) || $amount <= 0) {
                return response()->json(['error' => 'Cart is empty or total is zero.'], 422);
            }

            $returnUrl = route('payment.paypal.return');
            $cancelUrl = url('/checkout?status=cancelled');

            $result  = $this->payPal->createOrder((string) $amount, $returnUrl, $cancelUrl);
            $orderId = $result['orderId'] ?? '';

            if (!$orderId) {
                return response()->json(['error' => 'Failed to create PayPal order.'], 500);
            }

            // G4/G10: store cart state server-side, keyed by orderId.
            // The return handler reads from here — never from URL params.
            Cache::put("paypal_order_{$orderId}", [
                'items_json'   => $request->items_json,
                'username'     => $request->username,
                'voucher_code' => $voucherCode,
                'amount'       => $amount,
            ], now()->addHour());

            return response()->json($result);
        } catch (\Throwable $e) {
            Log::error('PayPal createOrder error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function paypalReturn(Request $request): RedirectResponse
    {
        $token = $request->query('token') ?? $request->query('orderId', '');

        if (!$token) {
            return redirect('/checkout?status=error');
        }

        // G4/G10: retrieve and consume the cached order state atomically.
        // A missing key means either the cache expired or the URL was replayed after processing.
        $cacheKey  = "paypal_order_{$token}";
        $orderData = Cache::pull($cacheKey);

        if (!$orderData) {
            // Idempotent: if transaction already exists the payment succeeded on a prior hit
            if (PaymentTransaction::where('provider_transaction_id', $token)->exists()) {
                return redirect('/checkout?status=success&orderId=' . urlencode($token));
            }
            Log::warning("PayPal return: no cached order data for token {$token}");
            return redirect('/checkout?status=error');
        }

        $username    = $orderData['username'];
        $itemsJson   = $orderData['items_json'];
        $voucherCode = $orderData['voucher_code'];

        try {
            // G2: verify the capture actually completed before doing anything
            $capture = $this->payPal->captureOrder($token);

            if (!$capture['success']) {
                Log::warning("PayPal capture not COMPLETED for order {$token}");
                return redirect('/checkout?status=error');
            }

            // G6: wrap the full write sequence in a transaction
            DB::transaction(function () use ($token, $capture, $username, $itemsJson, $voucherCode) {
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
            });

            Log::info("PayPal purchase complete: {$username} €{$capture['amount']} (order {$token})");

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
            'items_json'   => 'required|string',
            'username'     => 'required|string|max:100',
            'voucher_code' => 'nullable|string',
        ]);

        try {
            $cartItems   = $this->parseCartItems($request->items_json);
            $voucherCode = trim($request->voucher_code ?? '');
            $amount      = $this->computeServerAmount($cartItems, $voucherCode ?: null);

            if (empty($cartItems) || $amount <= 0) {
                return response()->json(['error' => 'Cart is empty or total is zero.'], 422);
            }

            $username   = $request->username;
            $successUrl = route('payment.stripe.return') . '?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl  = url('/checkout?status=cancelled');
            $desc       = "SentrySMP Purchase for {$username}";

            $result    = $this->stripe->createSession((string) $amount, $desc, $successUrl, $cancelUrl);
            $sessionId = $result['sessionId'] ?? '';

            if (!$sessionId) {
                return response()->json(['error' => 'Failed to create Stripe session.'], 500);
            }

            // G4/G10: store cart state server-side, keyed by sessionId.
            Cache::put("stripe_session_{$sessionId}", [
                'items_json'   => $request->items_json,
                'username'     => $username,
                'voucher_code' => $voucherCode,
                'amount'       => $amount,
            ], now()->addHour());

            return response()->json($result);
        } catch (\Throwable $e) {
            Log::error('Stripe createSession error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function stripeReturn(Request $request): RedirectResponse
    {
        $sessionId = $request->query('session_id', '');

        if (!$sessionId) {
            return redirect('/checkout?status=error');
        }

        // G4/G10: retrieve and consume the cached session state atomically.
        $cacheKey    = "stripe_session_{$sessionId}";
        $sessionData = Cache::pull($cacheKey);

        if (!$sessionData) {
            if (PaymentTransaction::where('provider_transaction_id', $sessionId)->exists()) {
                return redirect('/checkout?status=success&session_id=' . urlencode($sessionId));
            }
            Log::warning("Stripe return: no cached session data for {$sessionId}");
            return redirect('/checkout?status=error');
        }

        $username    = $sessionData['username'];
        $itemsJson   = $sessionData['items_json'];
        $voucherCode = $sessionData['voucher_code'];

        try {
            $session = $this->stripe->retrieveSession($sessionId);

            // G1: only proceed if Stripe confirms the payment was received
            if (($session['status'] ?? '') !== 'paid') {
                Log::warning("Stripe session {$sessionId} not paid — status: " . ($session['status'] ?? 'unknown'));
                return redirect('/checkout?status=error');
            }

            // G6: wrap the full write sequence in a transaction
            DB::transaction(function () use ($sessionId, $session, $username, $itemsJson, $voucherCode) {
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
            });

            Log::info("Stripe purchase complete: {$username} €{$session['amount']} (session {$sessionId})");

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

    /**
     * G5: compute the authoritative purchase total from the database.
     * Never trust a price or amount from the client.
     */
    private function computeServerAmount(array $cartItems, ?string $voucherCode): float
    {
        if (empty($cartItems)) {
            return 0.0;
        }

        $productIds = collect($cartItems)
            ->pluck('id')
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->all();

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $subtotal     = 0.0;
        $voucherItems = [];

        foreach ($cartItems as $item) {
            $id      = (int) ($item['id'] ?? 0);
            $qty     = max(1, (int) ($item['quantity'] ?? 1));
            $product = $products[$id] ?? null;

            if (!$product) {
                continue;
            }

            $ep        = $product->effective_price;
            $subtotal += $ep * $qty;

            $voucherItems[] = ['id' => $id, 'unit_price' => $ep, 'quantity' => $qty];
        }

        if ($voucherCode) {
            $result = $this->voucher->validate($voucherCode, $voucherItems);
            if ($result['valid'] && $result['discount_amount'] > 0) {
                $subtotal = max(0.0, $subtotal - $result['discount_amount']);
            }
        }

        return round($subtotal, 2);
    }

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
