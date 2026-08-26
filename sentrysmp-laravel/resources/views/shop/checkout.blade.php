@extends('layouts.app')
@section('title', 'Checkout - SentrySMP')

@section('content')
<div class="main-wrapper">
    <p class="main">Checkout</p>

    @if($status === 'success')
        <div class="checkout-success">
            <i class="bi bi-check-circle-fill" style="font-size:48px;color:#28a745;display:block;margin-bottom:16px;"></i>
            <h2 style="color:white;margin:0 0 8px;">Payment Successful!</h2>
            <p style="color:#aaa;">Your purchase has been recorded. Commands have been queued and will be delivered to the game server shortly.</p>
            <button onclick="clearCart();location.href='/'" class="btn-checkout-pay" style="margin-top:16px;">Back to Home</button>
        </div>
        <script>clearCart && clearCart();</script>
    @elseif($status === 'cancelled')
        <div class="checkout-error">
            <i class="bi bi-x-circle-fill" style="font-size:48px;color:#dc3545;display:block;margin-bottom:16px;"></i>
            <h2 style="color:white;margin:0 0 8px;">Payment Cancelled</h2>
            <p style="color:#aaa;">Your payment was cancelled. Your cart has been preserved.</p>
        </div>
    @elseif($status === 'error')
        <div class="checkout-error">
            <i class="bi bi-exclamation-circle-fill" style="font-size:48px;color:#dc3545;display:block;margin-bottom:16px;"></i>
            <h2 style="color:white;margin:0 0 8px;">Payment Error</h2>
            <p style="color:#aaa;">Something went wrong. Please try again or contact support.</p>
        </div>
    @endif

    @if(!$status || in_array($status, ['cancelled', 'error']))
    @if(!$settings->enable_payments)
        <div class="info" style="text-align:center;padding:30px;">
            <p style="color:#dc3545;font-size:18px;font-weight:700;">Payments are currently disabled.</p>
            <p style="color:#aaa;">Please check back later.</p>
        </div>
    @else
        <div class="checkout-layout">
            <!-- Left: cart summary -->
            <div class="checkout-cart-section">
                <div class="info">
                    <h3 style="margin:0 0 16px;color:white;">Order Summary</h3>
                    <div id="checkout-cart-list"><p style="color:#666;">Loading cart...</p></div>
                    <hr style="border-color:#333;margin:16px 0;">

                    <!-- Voucher -->
                    <div style="margin-bottom:16px;">
                        <label style="font-size:12px;font-weight:600;color:#aaa;text-transform:uppercase;letter-spacing:0.05em;">Voucher Code</label>
                        <div style="display:flex;gap:8px;margin-top:6px;">
                            <input type="text" id="voucher-code" placeholder="Enter code..." style="flex:1;margin:0;">
                            <button onclick="applyVoucher()" style="background:#333;padding:0 14px;border-radius:10px;font-weight:600;white-space:nowrap;">Apply</button>
                        </div>
                        <p id="voucher-msg" style="font-size:12px;margin:6px 0 0;"></p>
                    </div>

                    <div class="total-row">
                        <span>Subtotal</span>
                        <span id="subtotal-display">€0.00</span>
                    </div>
                    <div class="total-row" id="discount-row" style="display:none;color:#28a745;">
                        <span>Discount</span>
                        <span id="discount-display">-€0.00</span>
                    </div>
                    <div class="total-row" style="font-size:20px;font-weight:700;color:white;border-top:1px solid #333;padding-top:12px;margin-top:8px;">
                        <span>Total</span>
                        <span id="grand-total-display">€0.00</span>
                    </div>
                </div>
            </div>

            <!-- Right: payment -->
            <div class="checkout-payment-section">
                <div class="info">
                    <h3 style="margin:0 0 16px;color:white;">Your Details</h3>
                    <label style="font-size:12px;font-weight:600;color:#aaa;text-transform:uppercase;letter-spacing:0.05em;">Minecraft Username</label>
                    <input type="text" id="minecraft-username" placeholder="Enter your Minecraft username" style="margin:8px 0 16px;">

                    <h3 style="margin:0 0 16px;color:white;">Payment Method</h3>

                    @if(!$settings->disable_paypal)
                        <div id="paypal-button-container" style="margin-bottom:16px;"></div>
                    @endif

                    @if(!$settings->disable_stripe)
                        <button onclick="stripeCheckout()" class="btn-checkout-pay stripe-btn" id="stripe-btn">
                            <i class="bi bi-stripe"></i> Pay with Stripe
                        </button>
                    @endif

                    @if($settings->disable_paypal && $settings->disable_stripe)
                        <p style="color:#dc3545;text-align:center;">All payment methods are currently disabled.</p>
                    @endif

                    <p style="font-size:11px;color:#555;margin-top:16px;text-align:center;">
                        All sales are final. By completing your purchase, you agree to our
                        <a href="{{ route('terms') }}">Terms of Use</a>.
                    </p>
                </div>
            </div>
        </div>
    @endif
    @endif
</div>

<style>
.checkout-success, .checkout-error { background:#242424; border-radius:14px; padding:40px; text-align:center; margin-bottom:24px; }
.checkout-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
@media (max-width: 768px) { .checkout-layout { grid-template-columns: 1fr; } }
.checkout-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #2a2a2a; font-size: 14px; }
.checkout-item:last-child { border-bottom: none; }
.total-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 15px; color: #ccc; }
.btn-checkout-pay { width: 100%; background: #dc3545; color: white; border: none; border-radius: 10px; padding: 14px; font-size: 15px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 12px; transition: background 0.2s; }
.btn-checkout-pay:hover { background: #c82333; transform: none; }
.stripe-btn { background: #635bff; }
.stripe-btn:hover { background: #4f44e0; }
</style>

@push('head')
    @if(!$settings->disable_paypal)
        <script src="https://www.paypal.com/sdk/js?client-id={{ env('PAYPAL_CLIENT_ID') }}&currency=EUR" data-namespace="paypalSDK"></script>
    @endif
@endpush

@push('scripts')
<script>
let appliedVoucher = null;

function getUsername() {
    return document.getElementById('minecraft-username')?.value.trim() || '';
}

function renderCheckoutCart() {
    const cart = getCart();
    const container = document.getElementById('checkout-cart-list');
    if (!container) return;

    if (cart.length === 0) {
        container.innerHTML = '<p style="color:#666;">Your cart is empty. <a href="/">Browse products</a></p>';
        updateTotals();
        return;
    }

    let html = '';
    cart.forEach(item => {
        const ep = item.sale > 0 ? Math.round(item.price * (1 - item.sale / 100) * 100) / 100 : item.price;
        html += `<div class="checkout-item">
            <div>
                <span style="color:white;font-weight:600;">${escapeHtml(item.name)}</span><br>
                <span style="font-size:12px;color:#888;">${escapeHtml(item.type)}${item.server ? ' · ' + escapeHtml(item.server) : ''} × ${item.quantity}</span>
            </div>
            <span style="color:white;font-weight:600;">€${(ep * item.quantity).toFixed(2)}</span>
        </div>`;
    });

    container.innerHTML = html;
    updateTotals();
}

function updateTotals() {
    const subtotal = getCartTotal();
    let discount = 0;

    if (appliedVoucher && appliedVoucher.discount_amount > 0) {
        discount = appliedVoucher.discount_amount;
    }

    const grand = Math.max(0, subtotal - discount);

    document.getElementById('subtotal-display').textContent = '€' + subtotal.toFixed(2);
    document.getElementById('grand-total-display').textContent = '€' + grand.toFixed(2);

    const discountRow = document.getElementById('discount-row');
    if (discount > 0) {
        discountRow.style.display = 'flex';
        document.getElementById('discount-display').textContent = '-€' + discount.toFixed(2);
    } else {
        discountRow.style.display = 'none';
    }
}

async function applyVoucher() {
    const code = document.getElementById('voucher-code').value.trim();
    const msg  = document.getElementById('voucher-msg');
    if (!code) { msg.textContent = 'Please enter a code.'; msg.style.color = '#dc3545'; return; }

    const cart  = getCart();
    const items = cart.map(i => ({
        type: i.type, id: i.id,
        unit_price: i.sale > 0 ? Math.round(i.price * (1 - i.sale / 100) * 100) / 100 : i.price,
        quantity: i.quantity
    }));

    try {
        const resp = await fetch('{{ route("payment.voucher.validate") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify({ code, items })
        });
        const data = await resp.json();
        if (data.valid) {
            appliedVoucher = data;
            msg.textContent = '✓ ' + data.message;
            msg.style.color = '#28a745';
        } else {
            appliedVoucher = null;
            msg.textContent = '✗ ' + data.message;
            msg.style.color = '#dc3545';
        }
    } catch(e) {
        msg.textContent = 'Error validating voucher.';
        msg.style.color = '#dc3545';
    }
    updateTotals();
}

function getPaymentAmount() {
    const subtotal = getCartTotal();
    const discount = appliedVoucher ? (appliedVoucher.discount_amount || 0) : 0;
    return Math.max(0, subtotal - discount).toFixed(2);
}

function getItemsJson() {
    return JSON.stringify(getCart());
}

// PayPal
@if(!$settings->disable_paypal)
document.addEventListener('DOMContentLoaded', () => {
    if (typeof paypalSDK === 'undefined') return;
    paypalSDK.Buttons({
        createOrder: async function() {
            const username = getUsername();
            if (!username) { alert('Please enter your Minecraft username.'); return null; }
            if (getCart().length === 0) { alert('Your cart is empty.'); return null; }

            const resp = await fetch('{{ route("payment.paypal.create") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({
                    amount: getPaymentAmount(),
                    username: username,
                    items_json: getItemsJson(),
                    voucher_code: document.getElementById('voucher-code').value.trim()
                })
            });
            const data = await resp.json();
            if (data.error) { alert('Error: ' + data.error); return null; }
            return data.orderId;
        },
        onApprove: async function(data) {
            const username = getUsername();
            window.location.href = '/payment/paypal/return?token=' + encodeURIComponent(data.orderID)
                + '&username=' + encodeURIComponent(username)
                + '&items_json=' + encodeURIComponent(getItemsJson())
                + '&voucher_code=' + encodeURIComponent(document.getElementById('voucher-code').value.trim());
        },
        onError: function(err) { alert('PayPal error: ' + err); }
    }).render('#paypal-button-container');
});
@endif

async function stripeCheckout() {
    const username = getUsername();
    if (!username) { alert('Please enter your Minecraft username.'); return; }
    if (getCart().length === 0) { alert('Your cart is empty.'); return; }

    document.getElementById('stripe-btn').disabled = true;
    document.getElementById('stripe-btn').textContent = 'Redirecting...';

    try {
        const resp = await fetch('{{ route("payment.stripe.create") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify({
                amount: getPaymentAmount(),
                username: username,
                items_json: getItemsJson(),
                voucher_code: document.getElementById('voucher-code').value.trim()
            })
        });
        const data = await resp.json();
        if (data.error) { alert('Error: ' + data.error); return; }
        window.location.href = data.url;
    } catch(e) {
        alert('Error initiating Stripe payment.');
        document.getElementById('stripe-btn').disabled = false;
        document.getElementById('stripe-btn').textContent = 'Pay with Stripe';
    }
}

document.addEventListener('DOMContentLoaded', renderCheckoutCart);
</script>
@endpush
@endsection
