<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Afrekenen - Snackcorner Gennep</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;700;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="Styling/styles.css" />
    <style>
        /* ── Checkout-specifieke stijlen ── */
        .checkout-section {
            padding: var(--space-3xl) 0;
            min-height: calc(100vh - var(--header-height) - 200px);
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--space-xl);
        }

        @media (min-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr 1fr;
                align-items: start;
            }
        }

        /* Bestellingoverzicht */
        .order-summary {
            background: var(--clr-surface);
            border: var(--border);
            border-radius: var(--radius-md);
            padding: var(--space-lg);
        }

        .order-summary h2 {
            font-family: var(--font-heading);
            font-size: 1.25rem;
            margin-bottom: var(--space-md);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            color: var(--clr-dark);
        }

        .order-summary h2 i {
            color: var(--clr-red);
        }

        .summary-list {
            list-style: none;
            margin-bottom: var(--space-md);
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--space-sm) 0;
            border-bottom: 1px solid var(--clr-light);
            font-size: 0.95rem;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-item-name {
            flex: 1;
            color: var(--clr-dark);
        }

        .summary-item-qty {
            background: var(--clr-light);
            border-radius: var(--radius-sm);
            padding: 2px 8px;
            font-size: 0.8rem;
            font-weight: 600;
            margin: 0 var(--space-sm);
            color: var(--clr-text-muted);
        }

        .summary-item-price {
            font-weight: 600;
            color: var(--clr-dark);
            white-space: nowrap;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: var(--space-md);
            border-top: 2px solid var(--clr-brown);
            font-size: 1.1rem;
            font-weight: 700;
        }

        .summary-total strong {
            color: var(--clr-red);
            font-size: 1.3rem;
        }

        .cart-empty-checkout {
            text-align: center;
            padding: var(--space-xl) 0;
            color: var(--clr-text-muted);
        }

        .cart-empty-checkout i {
            font-size: 2.5rem;
            margin-bottom: var(--space-md);
            color: var(--clr-light);
            display: block;
        }

        /* Formulier */
        .checkout-form-card {
            background: var(--clr-white);
            border: var(--border);
            border-radius: var(--radius-md);
            padding: var(--space-lg);
            box-shadow: var(--shadow-sm);
        }

        .checkout-form-card h2 {
            font-family: var(--font-heading);
            font-size: 1.25rem;
            margin-bottom: var(--space-lg);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            color: var(--clr-dark);
        }

        .checkout-form-card h2 i {
            color: var(--clr-red);
        }

        .form-group {
            margin-bottom: var(--space-md);
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: var(--space-xs);
            color: var(--clr-dark);
        }

        .form-group label .required {
            color: var(--clr-red);
            margin-left: 2px;
        }

        .form-control {
            width: 100%;
            padding: 0.65rem var(--space-md);
            border: 2px solid var(--clr-light);
            border-radius: var(--radius-sm);
            font-family: var(--font-body);
            font-size: 1rem;
            color: var(--clr-dark);
            background: var(--clr-white);
            transition: border-color var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--clr-red);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 90px;
        }

        .gesloten-melding {
            background: var(--clr-light);
            border: 2px solid var(--clr-brown);
            border-radius: var(--radius-sm);
            padding: var(--space-sm) var(--space-md);
            font-size: 0.9rem;
            color: var(--clr-text-muted);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }

        /* Bevestiging scherm */
        .bevestiging {
            display: none;
            text-align: center;
            padding: var(--space-2xl) var(--space-lg);
        }

        .bevestiging-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--clr-red), var(--clr-red-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--space-lg);
            font-size: 2rem;
            color: var(--clr-white);
        }

        .bevestiging h2 {
            font-family: var(--font-heading);
            font-size: 1.75rem;
            margin-bottom: var(--space-sm);
            color: var(--clr-dark);
        }

        .bevestiging p {
            color: var(--clr-text-muted);
            max-width: 380px;
            margin: 0 auto var(--space-lg);
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header class="site-header" id="top">
    <div class="header-inner container">
        <a href="index.php" class="logo">
            <span class="logo-icon"><i class="fa-solid fa-fire-flame-curved"></i></span>
            <span class="logo-text">Snack<strong>corner</strong></span>
        </a>
        <nav class="main-nav" aria-label="Hoofdnavigatie">
            <a href="index.php#menu">Menu</a>
            <a href="index.php#locatie">Locatie</a>
            <a href="index.php#contact">Contact</a>
        </nav>
        <div class="header-actions">
            <button class="hamburger" id="hamburger" aria-label="Menu openen" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
    <nav class="mobile-nav" id="mobileNav" aria-hidden="true">
        <a href="index.php#menu" class="mobile-nav-link">Menu</a>
        <a href="index.php#locatie" class="mobile-nav-link">Locatie</a>
        <a href="index.php#contact" class="mobile-nav-link">Contact</a>
    </nav>
</header>

<main>
    <section class="checkout-section">
        <div class="container">

            <div class="section-header">
                <h2 class="section-title">Afrekenen</h2>
                <p class="section-sub">Controleer je bestelling en vul je gegevens in.</p>
            </div>

            <!-- Bevestiging (verborgen tot na verzenden) -->
            <div class="bevestiging" id="bevestiging">
                <div class="bevestiging-icon">
                    <i class="fa-solid fa-check"></i>
                </div>
                <h2>Bestelling geplaatst!</h2>
                <p>Bedankt voor je bestelling bij Snackcorner Gennep. We gaan direct aan de slag!</p>
                <a href="index.php" class="btn btn-primary btn-large">
                    <i class="fa-solid fa-arrow-left"></i> Terug naar het menu
                </a>
            </div>

            <!-- Checkout grid (verborgen na verzenden) -->
            <div class="checkout-grid" id="checkoutGrid">

                <!-- Linkerkolom: bestellingoverzicht -->
                <div class="order-summary">
                    <h2><i class="fa-solid fa-basket-shopping"></i> Jouw bestelling</h2>

                    <div id="summaryEmpty" class="cart-empty-checkout">
                        <i class="fa-solid fa-plate-wheat"></i>
                        <p>Je winkelwagen is leeg.</p>
                        <a href="index.php#menu" class="btn btn-primary" style="margin-top:var(--space-md)">Terug naar het menu</a>
                    </div>

                    <ul class="summary-list" id="summaryList"></ul>

                    <div class="summary-total" id="summaryTotal" style="display:none;">
                        <span>Totaal</span>
                        <strong id="summaryTotalBedrag">&euro;0,00</strong>
                    </div>
                </div>

                <!-- Rechterkolom: formulier -->
                <div class="checkout-form-card" id="formCard">
                    <h2><i class="fa-solid fa-user"></i> Jouw gegevens</h2>

                    <form id="checkoutForm" novalidate>

                        <div class="form-group">
                            <label for="naam">Naam <span class="required">*</span></label>
                            <input type="text" id="naam" name="naam" class="form-control"
                                   placeholder="Bijv. Jan de Vries" required autocomplete="name" />
                        </div>

                        <div class="form-group">
                            <label for="telefoon">Telefoonnummer <span class="required">*</span></label>
                            <input type="tel" id="telefoon" name="telefoon" class="form-control"
                                   placeholder="Bijv. 06-12345678" required autocomplete="tel" />
                        </div>

                        <div class="form-group">
                            <label for="ophaaltijd">Ophaaltijd <span class="required">*</span></label>
                            <select id="ophaaltijd" name="ophaaltijd" class="form-control" required></select>
                            <div class="gesloten-melding" id="geslotenMelding" style="display:none;">
                                <i class="fa-solid fa-clock"></i>
                                <span>We zijn vandaag gesloten. Kom morgen langs!</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="opmerking">Opmerkingen <small style="font-weight:400;color:var(--clr-text-muted)">(optioneel)</small></label>
                            <textarea id="opmerking" name="opmerking" class="form-control"
                                      placeholder="Bijv. extra saus, geen ui..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block" id="plaatsBtn">
                            <i class="fa-solid fa-check"></i> Bestelling bevestigen
                        </button>
                        <a href="index.php" class="btn btn-ghost btn-block" style="margin-top:var(--space-sm);text-align:center;display:block;">
                            <i class="fa-solid fa-arrow-left"></i> Terug naar het menu
                        </a>

                    </form>
                </div>

            </div>
        </div>
    </section>
</main>

<!-- FOOTER -->
<footer class="site-footer">
    <div class="container footer-inner">
        <div class="footer-brand">
            <span class="logo-icon"><i class="fa-solid fa-fire-flame-curved"></i></span>
            <span class="logo-text">Snack<strong>corner</strong></span>
            <p>Gennep's lekkerste snackbar.</p>
        </div>
        <div class="footer-links">
            <a href="index.php#menu">Menu</a>
            <a href="index.php#locatie">Locatie</a>
            <a href="index.php#contact">Contact</a>
        </div>
        <p class="footer-copy">&copy; <span id="year"></span> Snackcorner Gennep. Alle rechten voorbehouden.</p>
    </div>
</footer>

<div class="toast" id="toast" aria-live="assertive" aria-atomic="true"></div>

<script>
'use strict';

document.addEventListener('DOMContentLoaded', function () {

    // ── Elementen ──────────────────────────────────────────
    var summaryList      = document.getElementById('summaryList');
    var summaryEmpty     = document.getElementById('summaryEmpty');
    var summaryTotal     = document.getElementById('summaryTotal');
    var summaryBedrag    = document.getElementById('summaryTotalBedrag');
    var checkoutGrid     = document.getElementById('checkoutGrid');
    var bevestiging      = document.getElementById('bevestiging');
    var checkoutForm     = document.getElementById('checkoutForm');
    var ophaaltijdSelect = document.getElementById('ophaaltijd');
    var geslotenMelding  = document.getElementById('geslotenMelding');
    var toastEl          = document.getElementById('toast');
    var yearSpan         = document.getElementById('year');
    var hamburgerBtn     = document.getElementById('hamburger');
    var mobileNav        = document.getElementById('mobileNav');

    if (yearSpan) yearSpan.textContent = new Date().getFullYear();

    // ── Hamburger ──────────────────────────────────────────
    hamburgerBtn.addEventListener('click', function () {
        var isOpen = hamburgerBtn.classList.toggle('open');
        mobileNav.classList.toggle('open', isOpen);
        hamburgerBtn.setAttribute('aria-expanded', String(isOpen));
        mobileNav.setAttribute('aria-hidden', String(!isOpen));
    });

    // ── Ophaaltijden genereren ─────────────────────────────
    // Openingstijden: 0=zo,1=ma,...,6=za
    var openingstijden = {
        0: { open: '12:00', sluit: '21:00' }, // zondag
        1: { open: '11:00', sluit: '21:00' }, // maandag
        2: { open: '11:00', sluit: '21:00' }, // dinsdag
        3: { open: '11:00', sluit: '21:00' }, // woensdag
        4: { open: '11:00', sluit: '21:00' }, // donderdag
        5: { open: '11:00', sluit: '21:00' }, // vrijdag
        6: { open: '11:00', sluit: '22:00' }  // zaterdag
    };

    function tijdNaarMinuten(tijdStr) {
        var delen = tijdStr.split(':');
        return parseInt(delen[0], 10) * 60 + parseInt(delen[1], 10);
    }

    function minutenNaarTijd(min) {
        var u = Math.floor(min / 60);
        var m = min % 60;
        return (u < 10 ? '0' : '') + u + ':' + (m < 10 ? '0' : '') + m;
    }

    var nu      = new Date();
    var dagNr   = nu.getDay();
    var schema  = openingstijden[dagNr];
    var huidigeMinuten = nu.getHours() * 60 + nu.getMinutes();

    var openMin  = tijdNaarMinuten(schema.open);
    var sluitMin = tijdNaarMinuten(schema.sluit);

    // Eerste beschikbare slot: 15 min vooruit (minimale bereidingstijd),
    // afgerond naar het eerstvolgende kwartier
    var vroegsteSlot = huidigeMinuten + 15;
    var rest = vroegsteSlot % 15;
    if (rest !== 0) vroegsteSlot += (15 - rest);

    var startMin = Math.max(vroegsteSlot, openMin);

    if (startMin >= sluitMin) {
        // Vandaag geen tijden meer beschikbaar
        ophaaltijdSelect.style.display = 'none';
        geslotenMelding.style.display  = 'flex';
        ophaaltijdSelect.required      = false;
    } else {
        for (var t = startMin; t < sluitMin; t += 15) {
            var opt = document.createElement('option');
            opt.value       = minutenNaarTijd(t);
            opt.textContent = minutenNaarTijd(t) + ' uur';
            ophaaltijdSelect.appendChild(opt);
        }
    }

    // ── Cart uit localStorage lezen ────────────────────────
    var cartItems = [];
    try {
        cartItems = JSON.parse(localStorage.getItem('snackcorner_cart') || '[]');
    } catch (e) {
        cartItems = [];
    }

    // ── Overzicht renderen ─────────────────────────────────
    if (cartItems.length === 0) {
        summaryEmpty.style.display = 'block';
        summaryTotal.style.display = 'none';
        document.getElementById('formCard').style.display = 'none';
    } else {
        summaryEmpty.style.display = 'none';
        summaryTotal.style.display = 'flex';

        var totaal = 0;
        cartItems.forEach(function (item) {
            var li = document.createElement('li');
            li.className = 'summary-item';
            var regelPrijs = (item.price * item.quantity).toFixed(2).replace('.', ',');
            totaal += item.price * item.quantity;
            li.innerHTML =
                '<span class="summary-item-name">' + escapeHTML(item.name) + '</span>' +
                '<span class="summary-item-qty">x' + item.quantity + '</span>' +
                '<span class="summary-item-price">&#8364;' + regelPrijs + '</span>';
            summaryList.appendChild(li);
        });

        summaryBedrag.textContent = '\u20ac' + totaal.toFixed(2).replace('.', ',');
    }

    // ── Formulier verzenden ────────────────────────────────
    checkoutForm.addEventListener('submit', function (e) {
        e.preventDefault();

        var naam      = document.getElementById('naam').value.trim();
        var telefoon  = document.getElementById('telefoon').value.trim();
        var ophaaltijd = ophaaltijdSelect.value;

        if (!naam) {
            showToast('Vul je naam in.');
            document.getElementById('naam').focus();
            return;
        }
        if (!telefoon) {
            showToast('Vul je telefoonnummer in.');
            document.getElementById('telefoon').focus();
            return;
        }
        if (ophaaltijdSelect.required && !ophaaltijd) {
            showToast('Kies een ophaaltijd.');
            ophaaltijdSelect.focus();
            return;
        }
        if (cartItems.length === 0) {
            showToast('Je winkelwagen is leeg.');
            return;
        }

        // Bestelling "geplaatst" → leeg cart en toon bevestiging
        localStorage.removeItem('snackcorner_cart');

        // Toon gekozen ophaaltijd in bevestiging
        var bevestigingP = document.querySelector('.bevestiging p');
        if (bevestigingP && ophaaltijd) {
            bevestigingP.textContent = 'Bedankt voor je bestelling bij Snackcorner Gennep. Je bestelling is klaar om ' + ophaaltijd + ' uur!';
        }

        checkoutGrid.style.display = 'none';
        bevestiging.style.display  = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // ── Hulpfuncties ───────────────────────────────────────
    function showToast(message) {
        toastEl.textContent = message;
        toastEl.classList.add('show');
        setTimeout(function () { toastEl.classList.remove('show'); }, 2500);
    }

    function escapeHTML(str) {
        var d = document.createElement('div');
        d.textContent = String(str);
        return d.innerHTML;
    }

});
</script>
</body>
</html>
