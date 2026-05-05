<?php
session_start();

// Verbinding maken met de database
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

$foutmelding = '';

// Als het formulier is verstuurd
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naam          = trim($_POST['naam']          ?? '');
    $telefoon      = trim($_POST['telefoon']      ?? '');
    $ophaaltijd    = trim($_POST['ophaaltijd']    ?? '');
    $opmerking     = trim($_POST['opmerking']     ?? '');
    $betaalmethode = in_array($_POST['betaalmethode'] ?? '', ['contant', 'pin']) ? $_POST['betaalmethode'] : 'contant';

    // Producten komen binnen als losse lijsten (ingevuld door JavaScript)
    $namen     = $_POST['product_naam']   ?? [];
    $prijzen   = $_POST['product_prijs']  ?? [];
    $aantallen = $_POST['product_aantal'] ?? [];

    if ($naam !== '' && $telefoon !== '' && $ophaaltijd !== '' && count($namen) > 0) {

        // Totaalprijs berekenen
        $totaal = 0;
        for ($i = 0; $i < count($namen); $i++) {
            $totaal += (float)$prijzen[$i] * (int)$aantallen[$i];
        }

        // Producten opslaan als leesbare tekst voor de admin
        $productenTekst = '';
        for ($i = 0; $i < count($namen); $i++) {
            $productenTekst .= (int)$aantallen[$i] . 'x ' . $namen[$i] . ' (€' . number_format((float)$prijzen[$i], 2, ',', '') . ')' . "\n";
        }

        // Bestelling opslaan in de database
        $stmt = $db->prepare(
            "INSERT INTO `orders` (`klant_naam`, `telefoon`, `ophaaltijd`, `opmerking`, `producten`, `totaal`, `betaalmethode`)
             VALUES (:naam, :telefoon, :ophaaltijd, :opmerking, :producten, :totaal, :betaalmethode)"
        );
        $stmt->execute([
            ':naam'          => $naam,
            ':telefoon'      => $telefoon,
            ':ophaaltijd'    => $ophaaltijd . ':00',
            ':opmerking'     => $opmerking !== '' ? $opmerking : null,
            ':producten'     => trim($productenTekst),
            ':totaal'        => round($totaal, 2),
            ':betaalmethode' => $betaalmethode,
        ]);

        header('Location: afreken.php?succes=1&tijd=' . urlencode($ophaaltijd));
        exit;
    }

    $foutmelding = 'Er ging iets mis. Controleer je gegevens en probeer het opnieuw.';
}

// Ophaaltijden genereren
// Openingstijden per dag (0 = zondag, 6 = zaterdag)
$openingstijden = [
    0 => ['open' => '12:00', 'sluit' => '21:00'],
    1 => ['open' => '11:00', 'sluit' => '21:00'],
    2 => ['open' => '11:00', 'sluit' => '21:00'],
    3 => ['open' => '11:00', 'sluit' => '21:00'],
    4 => ['open' => '11:00', 'sluit' => '21:00'],
    5 => ['open' => '11:00', 'sluit' => '21:00'],
    6 => ['open' => '11:00', 'sluit' => '22:00'],
];

$dagNr = (int)date('w');
$schema = $openingstijden[$dagNr];

// strtotime zet een tijdstip om naar een getal (seconden) waar PHP mee kan rekenen
$openTijd  = strtotime($schema['open']);
$sluitTijd = strtotime($schema['sluit']);

// Vroegste ophaaltijd = nu + 15 minuten, afgerond naar kwartier (900 seconden)
$vroegste  = ceil((time() + 15 * 60) / 900) * 900;
$startTijd = max($vroegste, $openTijd);

$gesloten         = $startTijd >= $sluitTijd;
$ophaaltijdOpties = [];
if (!$gesloten) {
    // Loop van starttijd tot sluitingstijd, steeds 15 minuten (900 seconden) erbij
    for ($t = $startTijd; $t < $sluitTijd; $t += 900) {
        $ophaaltijdOpties[] = date('H:i', $t); // zet het getal terug naar "HH:MM"
    }
}

// Bevestigingspagina na succesvolle bestelling
$succes     = isset($_GET['succes']) && $_GET['succes'] === '1';
$ophaaltijd = htmlspecialchars($_GET['tijd'] ?? '');
?>
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
    <link rel="stylesheet" href="Styling/afreken.css" />
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

            <?php if ($succes): ?>
                <!-- Bevestigingsscherm -->
                <div class="bevestiging">
                    <div class="bevestiging-icon">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <h2>Bestelling geplaatst!</h2>
                    <p>
                        Bedankt voor je bestelling bij Snackcorner Gennep.
                        <?php if ($ophaaltijd): ?>
                            Je bestelling is klaar om <strong><?= $ophaaltijd ?></strong> uur!
                        <?php else: ?>
                            We gaan direct aan de slag!
                        <?php endif; ?>
                    </p>
                    <a href="index.php" class="btn btn-primary btn-large">
                        <i class="fa-solid fa-arrow-left"></i> Terug naar het menu
                    </a>
                </div>

            <?php else: ?>

                <?php if ($foutmelding): ?>
                    <div class="checkout-error">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <?= htmlspecialchars($foutmelding) ?>
                    </div>
                <?php endif; ?>

                <div class="checkout-grid" id="checkoutGrid">

                    <!-- Linkerkolom: bestellingoverzicht (gevuld door JavaScript via localStorage) -->
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

                        <form id="checkoutForm" method="POST" action="afreken.php">
                            <!-- JavaScript voegt hier losse productvelden aan toe bij verzenden -->

                            <div class="form-group">
                                <label for="naam">Naam <span class="required">*</span></label>
                                <input type="text" id="naam" name="naam" class="form-control"
                                       placeholder="Bijv. Jan de Vries" required autocomplete="name"
                                       value="<?= htmlspecialchars($_POST['naam'] ?? '') ?>" />
                            </div>

                            <div class="form-group">
                                <label for="telefoon">Telefoonnummer <span class="required">*</span></label>
                                <input type="tel" id="telefoon" name="telefoon" class="form-control"
                                       placeholder="Bijv. 06-12345678" required autocomplete="tel"
                                       value="<?= htmlspecialchars($_POST['telefoon'] ?? '') ?>" />
                            </div>

                            <div class="form-group">
                                <label for="ophaaltijd">Ophaaltijd <span class="required">*</span></label>
                                <?php if ($gesloten): ?>
                                    <div class="gesloten-melding">
                                        <i class="fa-solid fa-clock"></i>
                                        <span>We zijn vandaag gesloten. Kom morgen langs!</span>
                                    </div>
                                <?php else: ?>
                                    <select id="ophaaltijd" name="ophaaltijd" class="form-control" required>
                                        <?php foreach ($ophaaltijdOpties as $tijd): ?>
                                            <option value="<?= $tijd ?>"><?= $tijd ?> uur</option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="opmerking">Opmerkingen <small style="font-weight:400;color:var(--clr-text-muted)">(optioneel)</small></label>
                                <textarea id="opmerking" name="opmerking" class="form-control"
                                          placeholder="Bijv. extra saus, geen ui..."><?= htmlspecialchars($_POST['opmerking'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="betaalmethode">Betaalmethode <span class="required">*</span></label>
                                <select id="betaalmethode" name="betaalmethode" class="form-control" required>
                                    <option value="contant">Contant bij ophalen</option>
                                    <option value="pin">Pinnen bij ophalen</option>
                                </select>
                            </div>

                            <?php if (!$gesloten): ?>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fa-solid fa-check"></i> Bestelling bevestigen
                                </button>
                            <?php endif; ?>
                            <a href="index.php" class="btn btn-ghost btn-block" style="margin-top:var(--space-sm);text-align:center;display:block;">
                                <i class="fa-solid fa-arrow-left"></i> Terug naar het menu
                            </a>
                        </form>
                    </div>

                </div>
            <?php endif; ?>

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
        <p class="footer-copy">&copy; <?= date('Y') ?> Snackcorner Gennep. Alle rechten voorbehouden.</p>
    </div>
</footer>

<script src="afreken.js"></script>
</body>
</html>
