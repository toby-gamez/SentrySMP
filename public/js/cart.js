const CART_KEY = 'sentry_cart';

const _selectedQty = {};

function getCart() {
    try { return JSON.parse(localStorage.getItem(CART_KEY) || '[]'); } catch { return []; }
}

function saveCart(cart) {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    updateCartCount();
    renderCartSidebar();
    if (typeof updateCheckoutBtn === 'function') updateCheckoutBtn();
}

function addToCart(item) {
    const cart = getCart();
    const existing = cart.find(c => c.id === item.id);
    if (existing) {
        existing.quantity = (existing.quantity || 1) + (item.quantity || 1);
    } else {
        cart.push({ ...item, quantity: item.quantity || 1 });
    }
    saveCart(cart);
    showCartFeedback(item.name + ' added to cart');
    openCartSidebar();
}

function selectQtyPreset(btn, itemId, qty) {
    var presets = btn.closest('.qty-presets');
    if (presets) {
        presets.querySelectorAll('.qty-preset-btn').forEach(function(b) {
            b.classList.remove('selected');
        });
    }
    btn.classList.add('selected');
    _selectedQty['item_' + itemId] = qty;
}

// category replaces the old type+server combo
function addToCartPreset(itemId, name, category, price, sale) {
    var key = 'item_' + itemId;
    var qty = _selectedQty[key] || 1;
    addToCart({ id: itemId, name: name, category: category, price: price, sale: sale, quantity: qty });
    refreshProductButtons();
}

function removeFromCart(id) {
    const cart = getCart().filter(c => c.id !== id);
    saveCart(cart);
    refreshProductButtons();
}

function updateQty(id, qty) {
    const cart = getCart();
    const item = cart.find(c => c.id === id);
    if (item) { item.quantity = Math.max(1, parseInt(qty) || 1); saveCart(cart); }
}

function clearCart() {
    localStorage.removeItem(CART_KEY);
    updateCartCount();
    renderCartSidebar();
    refreshProductButtons();
}

function getEffectivePrice(item) {
    if (item.sale > 0) return Math.round(item.price * (1 - item.sale / 100) * 100) / 100;
    return item.price;
}

function getCartTotal() {
    return getCart().reduce(function(sum, item) { return sum + getEffectivePrice(item) * (item.quantity || 1); }, 0);
}

function updateCartCount() {
    const count = getCart().reduce(function(s, i) { return s + (i.quantity || 1); }, 0);
    document.querySelectorAll('.cart-count').forEach(function(el) {
        el.textContent = count;
        el.style.display = count > 0 ? 'inline-flex' : 'none';
    });
}

function openCartSidebar() {
    document.getElementById('cart-sidebar')?.classList.add('cart-sidebar--open');
    var overlay = document.getElementById('cart-overlay');
    if (overlay) overlay.style.display = 'block';
}

function closeCartSidebar() {
    document.getElementById('cart-sidebar')?.classList.remove('cart-sidebar--open');
    var overlay = document.getElementById('cart-overlay');
    if (overlay) overlay.style.display = 'none';
}

function renderCartSidebar() {
    const container = document.getElementById('cart-items-container');
    const totalEl   = document.getElementById('cart-total');
    if (!container) return;

    const cart = getCart();
    if (cart.length === 0) {
        container.innerHTML = '<p class="cart-empty">Your cart is empty.</p>';
        if (totalEl) totalEl.textContent = '€0.00';
        return;
    }

    let html = '';
    cart.forEach(function(item) {
        const ep   = getEffectivePrice(item);
        const line = ep * item.quantity;
        html += '<div class="cart-sidebar-item">' +
            '<div class="cart-sidebar-item-img">' +
            (item.image ? '<img src="' + escapeHtml(item.image) + '" alt="' + escapeHtml(item.name) + '">' : '<i class="bi bi-box-seam"></i>') +
            '</div>' +
            '<div class="cart-sidebar-item-info">' +
            '<div class="cart-sidebar-item-name">' + escapeHtml(item.name) + '</div>' +
            (item.category ? '<div class="cart-sidebar-item-server">' + escapeHtml(item.category) + '</div>' : '') +
            '<div class="cart-sidebar-item-price">' +
            (item.sale > 0 ? '<span class="text-muted" style="text-decoration:line-through;margin-right:4px;">€' + item.price.toFixed(2) + '</span>' : '') +
            '€' + line.toFixed(2) +
            '</div>' +
            '<div class="quantity-controls" style="margin-top:4px;">' +
            '<button class="quantity-btn" onclick="updateQty(' + item.id + ', ' + Math.max(1, item.quantity - 1) + ')" ' + (item.quantity <= 1 ? 'disabled' : '') + '>-</button>' +
            '<span>' + item.quantity + '</span>' +
            '<button class="quantity-btn" onclick="updateQty(' + item.id + ', ' + (item.quantity + 1) + ')">+</button>' +
            '</div>' +
            '</div>' +
            '<button class="cart-sidebar-item-remove secondary" onclick="removeFromCart(' + item.id + ')" title="Remove"><i class="bi bi-trash"></i></button>' +
            '</div>';
    });

    container.innerHTML = html;
    if (totalEl) totalEl.textContent = '€' + getCartTotal().toFixed(2);
}

function refreshProductButtons() {
    const cart = getCart();
    document.querySelectorAll('.product').forEach(function(card) {
        var btn = card.querySelector('.great-button');
        if (!btn) return;
        var preset = card.querySelector('.qty-preset-btn');
        if (!preset) return;
        var itemId = parseInt(preset.getAttribute('data-item-id'));
        if (!itemId) return;
        var inCart = cart.find(function(c) { return c.id === itemId; });
        if (inCart) {
            btn.innerHTML = '<i class="bi bi-cart-check"></i> Update Cart';
        } else {
            btn.innerHTML = '<i class="bi bi-cart-plus"></i> Add to Cart';
        }
    });
}

function showCartFeedback(msg) {
    const el = document.createElement('div');
    el.className = 'cart-feedback';
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(function() { el.classList.add('show'); }, 10);
    setTimeout(function() { el.classList.remove('show'); setTimeout(function() { el.remove(); }, 300); }, 2500);
}

function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text || '').replace(/[&<>"']/g, function(m) { return map[m]; });
}

document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    renderCartSidebar();
    refreshProductButtons();
    if (typeof updateCheckoutBtn === 'function') updateCheckoutBtn();

    document.querySelectorAll('.qty-presets').forEach(function(presets) {
        var first = presets.querySelector('.qty-preset-btn');
        if (first) {
            first.classList.add('selected');
            var itemId = first.getAttribute('data-item-id');
            if (itemId) _selectedQty['item_' + itemId] = parseInt(first.getAttribute('data-qty') || '1');
        }
    });
});
