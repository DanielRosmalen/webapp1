<?php
// ─── DB Connectie ────────────────────────────────────────────
try {
    $db = new PDO(
            'mysql:host=mysql_db;dbname=restaraunt;charset=utf8',
            'root',
            'rootpassword',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Verbinding mislukt: " . $e->getMessage());
}

// ─── Haal alle categorieën op ────────────────────────────────
$categorieen = $db->query(
        "SELECT * FROM `categorieen` ORDER BY volgorde ASC, naam ASC"
)->fetchAll(PDO::FETCH_ASSOC);

// ─── Zoekterm uitlezen uit de URL ────────────────────────────
// Als iemand zoekt op "kroket" staat in de URL: ?zoek=kroket
// trim() verwijdert spaties voor en achter de zoekterm
$zoekterm = trim($_GET['zoek'] ?? '');

// ─── Producten ophalen — met of zonder zoekfilter ────────────
if ($zoekterm !== '') {
    // Er is een zoekterm: zoek alleen producten waarvan de naam
    // de zoekterm bevat. % is een wildcard: %kroket% vindt alles
    // dat "kroket" bevat, ook "Bitterbal kroket" of "kroketje".
    // We gebruiken een prepared statement zodat de zoekterm
    // veilig wordt meegegeven (geen SQL injection mogelijk).
    $stmt = $db->prepare(
            "SELECT p.*, c.naam AS cat_naam
         FROM `producten` p
         JOIN `categorieen` c ON c.id = p.categorie_id
         WHERE p.naam LIKE :zoek
         ORDER BY c.volgorde ASC, p.volgorde ASC, p.naam ASC"
    );
    $stmt->execute([':zoek' => '%' . $zoekterm . '%']);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Geen zoekterm: haal alles op zoals normaal
    $rows = $db->query(
            "SELECT p.*, c.naam AS cat_naam
         FROM `producten` p
         JOIN `categorieen` c ON c.id = p.categorie_id
         ORDER BY p.categorie_id ASC, p.volgorde ASC, p.naam ASC"
    )->fetchAll(PDO::FETCH_ASSOC);
}

// ─── Groepeer producten per categorie ────────────────────────
// $producten[categorie_id][naam][] = $row
$producten = [];
foreach ($rows as $row) {
    $producten[$row['categorie_id']][$row['naam']][] = $row;
}

// ─── Hulpfunctie: slug voor select-id's ──────────────────────
function slug(string $str): string {
    return strtolower(preg_replace('/[^a-z0-9]+/i', '-', $str));
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Snackcorner - Gennep</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;700;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="Styling/styles.css" />
</head>
<body>

<!-- HEADER -->
<header class="site-header">
    <div class="header-inner container">
        <a href="#" onclick="window.scrollTo({top:0,behavior:'smooth'});return false;" class="logo">
            <span class="logo-icon"><i class="fa-solid fa-fire-flame-curved"></i></span>
            <span class="logo-text">Snack<strong>corner</strong></span>
        </a>
        <nav class="main-nav" aria-label="Hoofdnavigatie">
            <a href="#menu">Menu</a>
            <a href="#locatie">Locatie</a>
            <a href="#contact">Contact</a>
        </nav>
        <div class="header-actions">
            <button class="cart-btn" id="cartToggle" aria-label="Winkelwagen openen">
                <i class="fa-solid fa-basket-shopping"></i>
                <span class="cart-count" id="cartCount" aria-live="polite">0</span>
            </button>
            <button class="hamburger" id="hamburger" aria-label="Menu openen" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
    <nav class="mobile-nav" id="mobileNav" aria-hidden="true">
        <a href="#menu" class="mobile-nav-link">Menu</a>
        <a href="#locatie" class="mobile-nav-link">Locatie</a>
        <a href="#contact" class="mobile-nav-link">Contact</a>
    </nav>
</header>

<!-- CART SIDEBAR -->
<aside class="cart-sidebar" id="cartSidebar" aria-hidden="true" aria-label="Winkelwagen">
    <div class="cart-overlay" id="cartOverlay"></div>
    <div class="cart-panel">
        <div class="cart-header">
            <h2><i class="fa-solid fa-basket-shopping"></i> Jouw bestelling</h2>
            <button class="cart-close" id="cartClose" aria-label="Sluiten">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <ul class="cart-items" id="cartItems">
            <li class="cart-empty" id="cartEmpty">
                <i class="fa-solid fa-plate-wheat"></i>
                <p>Je winkelwagen is leeg.</p>
                <small>Voeg iets lekkers toe!</small>
            </li>
        </ul>
        <div class="cart-footer">
            <div class="cart-total">
                <span>Totaal:</span>
                <strong id="cartTotal">&euro;0,00</strong>
            </div>
            <button class="btn btn-primary btn-block" id="checkoutBtn" disabled>Bestelling plaatsen</button>
            <button class="btn btn-ghost btn-block" id="clearCartBtn">Winkelwagen leegmaken</button>
        </div>
    </div>
</aside>

<main>

    <!-- HERO -->
    <section class="hero" id="hero">
        <div class="hero-bg-pattern" aria-hidden="true"></div>
        <div class="container hero-content">
            <p class="hero-eyebrow">Genneps favoriete snackbar</p>
            <h1 class="hero-title">Eerlijk &amp; Lekker,<br><span class="highlight">Elke Dag.</span></h1>
            <p class="hero-subtitle">Verse friet, krokets, burgers en meer - voor het hele gezin. Kom langs of bekijk ons volledige menu hieronder.</p>
            <div class="hero-actions">
                <a href="#menu" class="btn btn-primary btn-large">Bekijk het menu <i class="fa-solid fa-arrow-down"></i></a>
                <a href="#contact" class="btn btn-outline btn-large">Openingstijden</a>
            </div>
            <div class="hero-badges">
                <span class="badge"><i class="fa-solid fa-wheat-awn-circle-exclamation"></i> Glutenvrije opties</span>
                <span class="badge"><i class="fa-solid fa-leaf"></i> Vegetarisch aanbod</span>
                <span class="badge"><i class="fa-solid fa-fire-flame-curved"></i> Elke dag vers</span>
            </div>
        </div>
    </section>

    <!-- DAG AANBIEDING -->
    <div style="text-align:center; padding: 16px;">
        <dag-aanbieding snack="Frietje Speciaal"></dag-aanbieding>
    </div>

    <!-- MENU SECTION -->
    <section class="menu-section" id="menu">
        <div class="container">

            <div class="section-header">
                <h2 class="section-title">Ons Menu</h2>
                <p class="section-sub">Van klassieke friet tot sappige burgers - er is altijd iets voor iedereen.</p>
            </div>

            <!-- ─── ZOEKBALK (PHP) ───────────────────────────
                 method="GET" stuurt de zoekterm als ?zoek=...
                 in de URL, zodat PHP het kan uitlezen.
                 action="#menu" scrolt naar het menu na zoeken.
            ─────────────────────────────────────────────── -->
            <form method="GET" action="index.php#menu" class="search-wrapper">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input
                            type="search"
                            name="zoek"
                            class="search-input"
                            placeholder="Zoek een product, bijv. kroket..."
                            autocomplete="off"
                            aria-label="Zoek in het menu"
                            value="<?= htmlspecialchars($zoekterm) ?>"
                    >
                    <?php if ($zoekterm !== ''): ?>
                        <!-- Toon een wis-knop als er een zoekterm actief is -->
                        <a href="index.php#menu" class="search-clear visible" aria-label="Zoekopdracht wissen">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="search-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-magnifying-glass"></i> Zoeken
                    </button>
                    <?php if ($zoekterm !== ''): ?>
                        <a href="index.php#menu" class="btn btn-ghost">
                            Alles tonen
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- ─── ZOEKRESULTAAT MELDING ─────────────────────
                 Als er gezocht is, laat zien hoeveel gevonden.
                 Als niets gevonden: toon foutmelding.
            ─────────────────────────────────────────────── -->
            <?php if ($zoekterm !== ''): ?>
                <?php
                // Tel het totaal aantal gevonden producten
                $aantalGevonden = 0;
                foreach ($producten as $groepen) {
                    $aantalGevonden += count($groepen);
                }
                ?>
                <?php if ($aantalGevonden === 0): ?>
                    <div class="no-results visible">
                        <i class="fa-solid fa-circle-xmark"></i>
                        <p>Geen producten gevonden voor &ldquo;<?= htmlspecialchars($zoekterm) ?>&rdquo;.</p>
                        <small>Probeer een andere zoekterm.</small>
                    </div>
                <?php else: ?>
                    <div class="search-result-info">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <?= $aantalGevonden ?> resultaat<?= $aantalGevonden !== 1 ? 'en' : '' ?>
                        voor &ldquo;<strong><?= htmlspecialchars($zoekterm) ?></strong>&rdquo;
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Tabs: alleen tonen als er NIET gezocht wordt -->
            <?php if ($zoekterm === ''): ?>
                <div class="menu-tabs" id="menuTabs" role="tablist" aria-label="Menucategorieen">
                    <?php foreach ($categorieen as $i => $cat): ?>
                        <button
                                class="tab-btn <?= $i === 0 ? 'active' : '' ?>"
                                data-target="cat-<?= $cat['id'] ?>">
                            <?= htmlspecialchars($cat['naam']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- ─── CATEGORIEËN + PRODUCTEN ───────────────────
                 Bij een zoekopdracht worden alleen categorieën
                 getoond die minstens één resultaat hebben.
                 Bij geen zoekopdracht worden alle categorieën
                 getoond zoals normaal.
            ─────────────────────────────────────────────── -->
            <?php foreach ($categorieen as $cat):
                $catId    = $cat['id'];
                $catNaam  = $cat['naam'];
                $catIcoon = $cat['icoon'];
                $groepen  = $producten[$catId] ?? [];

                // Bij een zoekopdracht: sla lege categorieën over
                if ($zoekterm !== '' && empty($groepen)) continue;
                ?>
                <div class="menu-category" id="cat-<?= $catId ?>">
                    <h3 class="cat-title">
                        <span class="cat-icon"><i class="fa-solid <?= htmlspecialchars($catIcoon) ?>"></i></span>
                        <?= htmlspecialchars($catNaam) ?>
                    </h3>
                    <div class="menu-grid">
                        <?php foreach ($groepen as $naam => $sizes):
                            $first       = $sizes[0];
                            $firstPrice  = number_format($first['prijs'], 2, '.', '');
                            $hasDropdown = count($sizes) > 1;
                            $selectId    = 'size-' . $catId . '-' . slug($naam);
                            $isVeg       = (int)$first['vega'] === 1;
                            ?>
                            <div class="menu-card <?= $isVeg ? 'veg-card' : '' ?>">
                                <div class="menu-card-body">
                                    <div class="menu-card-top">
                                        <h4><?= htmlspecialchars($naam) ?></h4>
                                        <?php if ($isVeg): ?>
                                            <span class="veg-badge"><i class="fa-solid fa-leaf"></i></span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!empty($first['beschrijving'])): ?>
                                        <p class="product-beschrijving"><?= htmlspecialchars($first['beschrijving']) ?></p>
                                    <?php endif; ?>

                                    <?php if ($hasDropdown): ?>
                                        <label class="size-label" for="<?= $selectId ?>">Kies maat:</label>
                                        <select class="size-select" id="<?= $selectId ?>">
                                            <?php foreach ($sizes as $s): ?>
                                                <option value="<?= number_format($s['prijs'], 2, '.', '') ?>"
                                                        data-size="<?= htmlspecialchars($s['maat'] ?? '') ?>">
                                                    <?= htmlspecialchars($s['maat'] ?? '') ?>
                                                    &ndash; &euro;<?= number_format($s['prijs'], 2, ',', '.') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php elseif (!empty($first['maat'])): ?>
                                        <div class="size-prices">
                                            <span>
                                                <?= htmlspecialchars($first['maat']) ?>
                                                <strong>&euro;<?= number_format($first['prijs'], 2, ',', '.') ?></strong>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="menu-card-footer">
                                    <span class="price">&euro;<?= number_format($first['prijs'], 2, ',', '.') ?></span>
                                    <button class="btn btn-primary add-to-cart"
                                            data-name="<?= htmlspecialchars($naam) ?>"
                                            data-price="<?= $firstPrice ?>">
                                        <i class="fa-solid fa-plus"></i> Voeg toe
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (empty($groepen)): ?>
                            <p class="cat-empty-msg">Nog geen producten in deze categorie.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </section>

    <!-- LOCATIE & CONTACT -->
    <section class="location-section" id="locatie">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Locatie &amp; Contact</h2>
                <p class="section-sub">Je vindt ons midden in Gennep - altijd welkom!</p>
            </div>
            <div class="location-grid">
                <div class="contact-info" id="contact">
                    <h3>Snackcorner Gennep</h3>
                    <div class="contact-item">
                        <i class="fa-solid fa-location-dot contact-icon"></i>
                        <div>
                            <strong>Adres</strong>
                            <address>Marktstraat 1<br>6591 CK Gennep<br>Nederland</address>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fa-solid fa-phone contact-icon"></i>
                        <div>
                            <strong>Telefoon</strong>
                            <a href="tel:+31485123456">+31 (0)485 - 12 34 56</a>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fa-solid fa-clock contact-icon"></i>
                        <div>
                            <strong>Openingstijden</strong>
                            <table class="hours-table">
                                <tbody>
                                <tr><td>Ma - Vr</td><td>11:00 - 21:00</td></tr>
                                <tr><td>Zaterdag</td><td>11:00 - 22:00</td></tr>
                                <tr><td>Zondag</td><td>12:00 - 21:00</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="contact-item dietary-icons">
                        <span class="dietary-badge"><i class="fa-solid fa-wheat-awn-circle-exclamation"></i> Glutenvrije opties</span>
                        <span class="dietary-badge veg"><i class="fa-solid fa-leaf"></i> Vegetarisch aanbod</span>
                    </div>
                </div>
                <div class="map-wrapper">
                    <iframe
                            title="Snackcorner locatie op Google Maps"
                            src="https://maps.google.com/maps?q=Snackcorner%2C+Europaplein+7%2C+Gennep%2C+Nederland&output=embed"
                            width="100%" height="400" class="map-frame"
                            allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
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
            <a href="#menu">Menu</a>
            <a href="#locatie">Locatie</a>
            <a href="#contact">Contact</a>
            <a href="#" onclick="window.scrollTo({top:0,behavior:'smooth'});return false;">Terug naar boven</a>
        </div>
        <p class="footer-copy">&copy; <span id="year"></span> Snackcorner Gennep. Alle rechten voorbehouden.</p>
    </div>
</footer>

<div class="toast" id="toast" aria-live="assertive" aria-atomic="true"></div>
<script src="script.js"></script>
</body>
</html>