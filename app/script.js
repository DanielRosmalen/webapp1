'use strict';

document.addEventListener('DOMContentLoaded', function () {

    /* ==========================================================
       PART A - ELEMENT REFERENCES
    ========================================================== */
    const cartToggleBtn  = document.getElementById('cartToggle');
    const cartSidebar    = document.getElementById('cartSidebar');
    const cartOverlay    = document.getElementById('cartOverlay');
    const cartCloseBtn   = document.getElementById('cartClose');
    const cartPanel      = document.querySelector('.cart-panel');
    const cartCountBadge = document.getElementById('cartCount');
    const cartItemsList  = document.getElementById('cartItems');
    const cartEmptyEl    = document.getElementById('cartEmpty');
    const cartTotalEl    = document.getElementById('cartTotal');
    const checkoutBtn    = document.getElementById('checkoutBtn');
    const clearCartBtn   = document.getElementById('clearCartBtn');
    const toastEl        = document.getElementById('toast');
    const hamburgerBtn   = document.getElementById('hamburger');
    const mobileNav      = document.getElementById('mobileNav');
    const yearSpan       = document.getElementById('year');
    const tabButtons     = document.querySelectorAll('.tab-btn');

    /* ==========================================================
       PART B - APP STATE
    ========================================================== */
    let cartItems  = JSON.parse(localStorage.getItem('snackcorner_cart') || '[]');
    let toastTimer = null;

    /* ==========================================================
       PART C - CART OPEN / CLOSE
    ========================================================== */
    function openCart() {
        cartSidebar.classList.add('open');
        cartSidebar.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        cartCloseBtn.focus();
    }

    function closeCart() {
        cartSidebar.classList.remove('open');
        cartSidebar.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        cartToggleBtn.focus();
    }

    cartToggleBtn.addEventListener('click', openCart);
    cartCloseBtn.addEventListener('click', closeCart);

    cartOverlay.addEventListener('click', function (e) {
        if (e.target === cartOverlay) closeCart();
    });

    if (cartPanel) {
        cartPanel.addEventListener('click', function (e) { e.stopPropagation(); });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && cartSidebar.classList.contains('open')) closeCart();
    });

    /* ==========================================================
       PART D - ADD TO CART
    ========================================================== */
    document.addEventListener('click', function (e) {
        const button = e.target.closest('.add-to-cart');
        if (!button) return;

        const baseName = button.dataset.name;
        const card     = button.closest('.menu-card');
        const select   = card ? card.querySelector('.size-select') : null;

        let itemKey, itemName, itemPrice;

        if (select) {
            const opt       = select.options[select.selectedIndex];
            itemPrice       = parseFloat(opt.value);
            const sizeLabel = opt.dataset.size || '';
            itemName        = baseName + ' \u2013 ' + sizeLabel;
            itemKey         = itemName;
        } else {
            itemPrice = parseFloat(button.dataset.price);
            itemName  = baseName;
            itemKey   = baseName;
        }

        if (isNaN(itemPrice)) return;

        const existing = cartItems.find(function (i) { return i.key === itemKey; });
        if (existing) {
            existing.quantity += 1;
        } else {
            cartItems.push({ key: itemKey, name: itemName, price: itemPrice, quantity: 1 });
        }

        renderCart();
        showButtonFeedback(button);
        showToast('\u2713 ' + itemName + ' toegevoegd!');
    });

    /* ==========================================================
       PART E - SIZE DROPDOWN: live price update
    ========================================================== */
    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('size-select')) return;
        const select = e.target;
        const card   = select.closest('.menu-card');
        const label  = card ? card.querySelector('.price') : null;
        if (label) {
            label.textContent = '\u20ac' + parseFloat(select.value).toFixed(2).replace('.', ',');
        }
        const btn = card ? card.querySelector('.add-to-cart') : null;
        if (btn) btn.dataset.price = select.value;
    });

    /* ==========================================================
       PART F - RENDER CART
    ========================================================== */
    function renderCart() {
        cartItemsList.querySelectorAll('.cart-item').forEach(function (r) { r.remove(); });

        var totalQty   = 0;
        var totalPrice = 0;
        cartItems.forEach(function (item) {
            totalQty   += item.quantity;
            totalPrice += item.price * item.quantity;
        });

        cartEmptyEl.style.display = cartItems.length === 0 ? 'flex' : 'none';

        cartItems.forEach(function (item, index) {
            var li        = document.createElement('li');
            li.className  = 'cart-item';
            var linePrice = (item.price * item.quantity).toFixed(2).replace('.', ',');
            li.innerHTML  =
                '<span class="cart-item-name">'  + escapeHTML(item.name) + '</span>' +
                '<div class="cart-item-qty">'    +
                '<button class="qty-btn minus" data-index="' + index + '" aria-label="Minder">&#8722;</button>' +
                '<span class="qty-num">'         + item.quantity + '</span>' +
                '<button class="qty-btn plus"  data-index="' + index + '" aria-label="Meer">&#43;</button>' +
                '</div>'                         +
                '<span class="cart-item-price">&#8364;' + linePrice + '</span>';
            cartItemsList.appendChild(li);
        });

        cartTotalEl.textContent    = '\u20ac' + totalPrice.toFixed(2).replace('.', ',');
        cartCountBadge.textContent = totalQty;
        cartCountBadge.classList.toggle('has-items', totalQty > 0);
        checkoutBtn.disabled = cartItems.length === 0;
        localStorage.setItem('snackcorner_cart', JSON.stringify(cartItems));
    }

    /* ==========================================================
       PART G - CART +/- DELEGATION
    ========================================================== */
    cartItemsList.addEventListener('click', function (e) {
        var btn = e.target.closest('.qty-btn');
        if (!btn) return;
        var index = parseInt(btn.dataset.index, 10);
        if (isNaN(index) || index < 0 || index >= cartItems.length) return;

        if (btn.classList.contains('plus')) {
            cartItems[index].quantity += 1;
        } else {
            cartItems[index].quantity -= 1;
            if (cartItems[index].quantity <= 0) {
                var gone = cartItems[index].name;
                cartItems.splice(index, 1);
                showToast(gone + ' verwijderd.');
            }
        }
        renderCart();
    });

    /* ==========================================================
       PART H - CLEAR CART & CHECKOUT
    ========================================================== */
    clearCartBtn.addEventListener('click', function () {
        if (cartItems.length === 0) return;
        cartItems = [];
        renderCart();
        showToast('Winkelwagen geleegd.');
    });

    checkoutBtn.addEventListener('click', function () {
        if (cartItems.length === 0) return;
        window.location.href = 'afreken.php';
    });

    /* ==========================================================
       PART I - MOBILE HAMBURGER
    ========================================================== */
    hamburgerBtn.addEventListener('click', function () {
        var isOpen = hamburgerBtn.classList.toggle('open');
        mobileNav.classList.toggle('open', isOpen);
        hamburgerBtn.setAttribute('aria-expanded', String(isOpen));
        mobileNav.setAttribute('aria-hidden', String(!isOpen));
    });

    mobileNav.querySelectorAll('.mobile-nav-link').forEach(function (link) {
        link.addEventListener('click', function () {
            hamburgerBtn.classList.remove('open');
            mobileNav.classList.remove('open');
            hamburgerBtn.setAttribute('aria-expanded', 'false');
            mobileNav.setAttribute('aria-hidden', 'true');
        });
    });

    /* ==========================================================
       PART J - CATEGORY TABS
    ========================================================== */
    tabButtons.forEach(function (tab) {
        tab.addEventListener('click', function () {
            tabButtons.forEach(function (t) { t.classList.remove('active'); });
            tab.classList.add('active');
            var target = document.getElementById(tab.dataset.target);
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    /* ==========================================================
       PART K - UTILITIES
    ========================================================== */
    function showButtonFeedback(button) {
        var original = button.innerHTML;
        button.classList.add('added');
        button.innerHTML = '<i class="fa-solid fa-check"></i> Toegevoegd!';
        button.disabled  = true;
        setTimeout(function () {
            button.classList.remove('added');
            button.innerHTML = original;
            button.disabled  = false;
        }, 1200);
    }

    function showToast(message) {
        if (toastTimer) clearTimeout(toastTimer);
        toastEl.textContent = message;
        toastEl.classList.add('show');
        toastTimer = setTimeout(function () { toastEl.classList.remove('show'); }, 2500);
    }

    function escapeHTML(str) {
        var d = document.createElement('div');
        d.textContent = String(str);
        return d.innerHTML;
    }

    /* ==========================================================
       PART L - INIT
    ========================================================== */
    if (yearSpan) yearSpan.textContent = new Date().getFullYear();
    renderCart();

}); // end DOMContentLoaded