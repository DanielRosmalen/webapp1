<?php
ob_start();
session_start();

// ─── Login beveiliging ────────────────────────────────────────
define('ADMIN_USER',       'admin');
define('ADMIN_PASS',       'snackcorner123');
define('MAX_POGINGEN',     5);
define('LOCKOUT_SECONDEN', 15 * 60);

if (!isset($_SESSION['login_pogingen']))  $_SESSION['login_pogingen'] = 0;
if (!isset($_SESSION['lockout_tot']))     $_SESSION['lockout_tot']    = null;

$nu                = time();
$geblokkeerd       = false;
$lockoutResterende = 0;
$loginError        = '';

if ($_SESSION['lockout_tot'] !== null && $nu < $_SESSION['lockout_tot']) {
    $geblokkeerd       = true;
    $lockoutResterende = $_SESSION['lockout_tot'] - $nu;
} elseif ($_SESSION['lockout_tot'] !== null && $nu >= $_SESSION['lockout_tot']) {
    $_SESSION['login_pogingen'] = 0;
    $_SESSION['lockout_tot']    = null;
}

if (isset($_POST['login']) && !$geblokkeerd) {
    if ($_POST['username'] === ADMIN_USER && $_POST['password'] === ADMIN_PASS) {
        $_SESSION['admin']          = true;
        $_SESSION['login_pogingen'] = 0;
        $_SESSION['lockout_tot']    = null;
    } else {
        $_SESSION['login_pogingen']++;
        if ($_SESSION['login_pogingen'] >= MAX_POGINGEN) {
            $_SESSION['lockout_tot'] = $nu + LOCKOUT_SECONDEN;
            $geblokkeerd             = true;
            $lockoutResterende       = LOCKOUT_SECONDEN;
        } else {
            $resterend  = MAX_POGINGEN - $_SESSION['login_pogingen'];
            $loginError = "Verkeerde gebruikersnaam of wachtwoord. Nog $resterend poging" . ($resterend === 1 ? '' : 'en') . " over.";
        }
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// ─── DB Connectie ─────────────────────────────────────────────
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

$saveMsg = '';

if ($_SESSION['admin'] ?? false) {

    // ─── CATEGORIE TOEVOEGEN ──────────────────────────────────
    if (isset($_POST['add_cat'])) {
        $naam     = trim($_POST['cat_naam'] ?? '');
        $icoon    = trim($_POST['cat_icoon'] ?? 'fa-star');
        $volgorde = (int)($_POST['cat_volgorde'] ?? 99);
        if ($naam !== '') {
            $db->prepare("INSERT INTO `categorieen` (naam, icoon, volgorde) VALUES (:n,:i,:v)")
                    ->execute([':n' => $naam, ':i' => $icoon, ':v' => $volgorde]);
            $saveMsg = 'success:Categorie toegevoegd!';
        }
    }

    // ─── CATEGORIE VERWIJDEREN ────────────────────────────────
    if (isset($_POST['delete_cat'])) {
        $id = (int)$_POST['delete_cat_id'];
        $db->prepare("DELETE FROM `categorieen` WHERE id = :id")->execute([':id' => $id]);
        $saveMsg = 'success:Categorie en alle bijbehorende producten verwijderd.';
        // Redirect so we don't stay on a deleted category tab
        header('Location: admin.php?tab=categorieen');
        exit;
    }

    // ─── BESTELLING AFGEROND / VERWIJDEREN ───────────────────
    if (isset($_POST['delete_order'])) {
        $db->prepare("DELETE FROM `orders` WHERE ordernummer = :id")
                ->execute([':id' => (int)$_POST['delete_order_id']]);
        $saveMsg = 'success:Bestelling gemarkeerd als afgerond.';
        header('Location: admin.php?tab=bestellingen');
        exit;
    }

    // ─── PRODUCT VERWIJDEREN ──────────────────────────────────
    if (isset($_POST['delete_product'])) {
        $db->prepare("DELETE FROM `producten` WHERE id = :id")
                ->execute([':id' => (int)$_POST['delete_product_id']]);
        $saveMsg = 'success:Product verwijderd.';
    }

    // ─── PRODUCT TOEVOEGEN ────────────────────────────────────
    if (isset($_POST['add_product'])) {
        $catId       = (int)$_POST['prod_cat_id'];
        $naam        = trim($_POST['prod_naam'] ?? '');
        $beschrijving = trim($_POST['prod_beschrijving'] ?? '');
        $prijs       = (float)str_replace(',', '.', $_POST['prod_prijs'] ?? '0');
        $maat        = trim($_POST['prod_maat'] ?? '');
        $vega        = isset($_POST['prod_vega']) ? 1 : 0;
        $volgorde    = (int)($_POST['prod_volgorde'] ?? 99);

        if ($naam !== '' && $catId > 0) {
            $db->prepare(
                    "INSERT INTO `producten` (categorie_id, naam, beschrijving, maat, prijs, vega, volgorde)
                 VALUES (:c, :n, :b, :m, :p, :v, :vo)"
            )->execute([
                    ':c'  => $catId,
                    ':n'  => $naam,
                    ':b'  => $beschrijving ?: null,
                    ':m'  => $maat ?: null,
                    ':p'  => $prijs,
                    ':v'  => $vega,
                    ':vo' => $volgorde,
            ]);
            $saveMsg = 'success:Product toegevoegd!';
        }
    }

}

// ─── Data ophalen ─────────────────────────────────────────────
$categorieen = [];
$producten   = [];
$orders      = [];
$orderCount  = 0;

if ($_SESSION['admin'] ?? false) {
    $categorieen = $db->query(
            "SELECT * FROM `categorieen` ORDER BY volgorde ASC, naam ASC"
    )->fetchAll(PDO::FETCH_ASSOC);

    $orderCount = (int)$db->query("SELECT COUNT(*) FROM `orders`")->fetchColumn();

    // Actieve tab bepalen
    $actief = $_GET['tab'] ?? 'categorieen';
    $geldig = ['categorieen', 'bestellingen'];
    foreach ($categorieen as $c) { $geldig[] = 'cat-' . $c['id']; }
    if (!in_array($actief, $geldig)) $actief = 'categorieen';

    // Producten van actieve categorie ophalen
    if (str_starts_with($actief, 'cat-')) {
        $activeCatId = (int)substr($actief, 4);
        $producten   = $db->prepare(
                "SELECT * FROM `producten` WHERE categorie_id = :c ORDER BY volgorde ASC, naam ASC"
        );
        $producten->execute([':c' => $activeCatId]);
        $producten = $producten->fetchAll(PDO::FETCH_ASSOC);
    }

    // Bestellingen ophalen
    if ($actief === 'bestellingen') {
        $orders = $db->query(
            "SELECT * FROM `orders` ORDER BY `ordernummer` DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    $actief = 'categorieen';
}

if (!$saveMsg && ($_GET['saved'] ?? '') === 'product') {
    $saveMsg = 'success:Product opgeslagen!';
}
if (!$saveMsg && ($_GET['saved'] ?? '') === 'categorie') {
    $saveMsg = 'success:Categorie opgeslagen!';
}

$msgType = $msgText = '';
if ($saveMsg) [$msgType, $msgText] = explode(':', $saveMsg, 2);

function formateerWachttijd(int $seconden): string {
    $min = (int)ceil($seconden / 60);
    return $min === 1 ? '1 minuut' : "$min minuten";
}

// Actieve categorie-info ophalen
$activeCat = null;
if (str_starts_with($actief, 'cat-')) {
    $activeCatId = (int)substr($actief, 4);
    foreach ($categorieen as $c) {
        if ((int)$c['id'] === $activeCatId) { $activeCat = $c; break; }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – Snackcorner</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="Styling/styles.css" />
</head>
<body>

<?php if (!($_SESSION['admin'] ?? false)): ?>

    <div class="login-wrap">
        <div class="login-box">
            <h2>Admin</h2>
            <p>Log in om het menu te beheren.</p>
            <?php if ($geblokkeerd): ?>
                <div class="error-msg">
                    Te veel mislukte pogingen. Probeer het opnieuw over
                    <strong><?= formateerWachttijd($lockoutResterende) ?></strong>.
                </div>
                <button class="btn-login" disabled>Inloggen</button>
            <?php else: ?>
                <?php if ($loginError): ?>
                    <div class="error-msg"><?= htmlspecialchars($loginError) ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Gebruikersnaam</label>
                        <input type="text" name="username" autocomplete="username" required>
                    </div>
                    <div class="form-group">
                        <label>Wachtwoord</label>
                        <input type="password" name="password" autocomplete="current-password" required>
                    </div>
                    <button type="submit" name="login" class="btn-login">Inloggen</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>

    <!-- Delete modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal">
            <h3>Item verwijderen</h3>
            <p>Weet je zeker dat je <strong id="deleteItemName"></strong> wilt verwijderen?
                Dit kan niet ongedaan worden gemaakt.</p>
            <form method="POST" action="admin.php?tab=<?= $actief ?>">
                <input type="hidden" name="delete_product"    value="1">
                <input type="hidden" name="delete_product_id" id="deleteId">
                <div class="modal-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeModal()">Annuleren</button>
                    <button type="submit" class="btn btn-confirm-delete">Ja, verwijderen</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bestelling afgerond modal -->
    <div class="modal-overlay" id="orderDoneModal">
        <div class="modal">
            <h3><i class="fa-solid fa-check-circle" style="color:#2d8a3e"></i> Bestelling afronden</h3>
            <p>Markeer bestelling <strong id="orderDoneNr"></strong> van <strong id="orderDoneNaam"></strong> als afgerond?
                <br>De bestelling wordt uit de lijst verwijderd.</p>
            <form method="POST" action="admin.php?tab=bestellingen">
                <input type="hidden" name="delete_order"    value="1">
                <input type="hidden" name="delete_order_id" id="orderDoneId">
                <div class="modal-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeOrderModal()">Annuleren</button>
                    <button type="submit" class="btn btn-save">
                        <i class="fa-solid fa-check"></i> Ja, afgerond
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete category modal -->
    <div class="modal-overlay" id="deleteCatModal">
        <div class="modal">
            <h3>Categorie verwijderen</h3>
            <p>Weet je zeker dat je de categorie <strong id="deleteCatName"></strong> wilt verwijderen?
                <br><strong>Alle producten</strong> in deze categorie worden ook verwijderd!</p>
            <form method="POST" action="admin.php?tab=categorieen">
                <input type="hidden" name="delete_cat"    value="1">
                <input type="hidden" name="delete_cat_id" id="deleteCatId">
                <div class="modal-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeCatModal()">Annuleren</button>
                    <button type="submit" class="btn btn-confirm-delete">Ja, verwijderen</button>
                </div>
            </form>
        </div>
    </div>

    <div class="topbar">
        <h1><i class="fa-solid fa-fire-flame-curved"></i> Snackcorner Admin</h1>
        <div class="topbar-actions">
            <a href="index.php" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> Bekijk website</a>
            <a href="admin.php?logout=1"><i class="fa-solid fa-right-from-bracket"></i> Uitloggen</a>
        </div>
    </div>

    <div class="wrapper">

        <!-- SIDEBAR -->
        <nav class="sidebar">
            <a href="admin.php?tab=bestellingen" class="<?= $actief === 'bestellingen' ? 'active' : '' ?>">
                <i class="fa-solid fa-receipt"></i> Bestellingen
                <span class="count-badge"><?= $orderCount ?></span>
            </a>
            <a href="admin.php?tab=categorieen" class="<?= $actief === 'categorieen' ? 'active' : '' ?>">
                <i class="fa-solid fa-folder-open"></i> Categorieën
                <span class="count-badge"><?= count($categorieen) ?></span>
            </a>
            <div class="sidebar-divider">Producten per categorie</div>
            <?php foreach ($categorieen as $cat): ?>
                <a href="admin.php?tab=cat-<?= $cat['id'] ?>"
                   class="<?= $actief === 'cat-' . $cat['id'] ? 'active' : '' ?>">
                    <i class="fa-solid <?= htmlspecialchars($cat['icoon']) ?>"></i>
                    <?= htmlspecialchars($cat['naam']) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- MAIN CONTENT -->
        <div class="main">

            <?php if ($msgText): ?>
                <div class="alert <?= $msgType ?>"><?= htmlspecialchars($msgText) ?></div>
            <?php endif; ?>

            <?php if ($actief === 'bestellingen'): ?>
                <!-- ══════════════════════════════════════════
                     TAB: BESTELLINGEN
                ══════════════════════════════════════════ -->
                <div class="page-title">
                    <i class="fa-solid fa-receipt"></i> Bestellingen
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2><?= $orderCount ?> bestelling<?= $orderCount !== 1 ? 'en' : '' ?></h2>
                    </div>
                    <?php if (count($orders) === 0): ?>
                        <p style="padding: 1.5rem; color: var(--clr-text-muted);">Nog geen bestellingen geplaatst.</p>
                    <?php else: ?>
                    <table>
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Naam</th>
                            <th>Telefoon</th>
                            <th>Ophaaltijd</th>
                            <th>Producten</th>
                            <th>Totaal</th>
                            <th>Opmerking</th>
                            <th>Datum</th>
                            <th>Actie</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($orders as $order): ?>
                            <?php
                                // Producten staan als tekst opgeslagen, één product per regel
                                $productenRegels = array_filter(explode("\n", $order['producten'] ?? ''));
                                $ophaaltijd = substr($order['ophaaltijd'], 0, 5); // HH:MM
                                $datum = isset($order['besteldatum'])
                                    ? date('d-m-Y H:i', strtotime($order['besteldatum']))
                                    : '—';
                            ?>
                            <tr>
                                <td><strong>#<?= (int)$order['ordernummer'] ?></strong></td>
                                <td><?= htmlspecialchars($order['klant_naam'] ?? '') ?></td>
                                <td><?= htmlspecialchars($order['telefoon'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($ophaaltijd) ?> uur</td>
                                <td>
                                    <?php if (count($productenRegels) > 0): ?>
                                        <ul style="list-style:none;padding:0;margin:0;font-size:0.85rem;line-height:1.7;">
                                        <?php foreach ($productenRegels as $regel): ?>
                                            <li><?= htmlspecialchars($regel) ?></li>
                                        <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <span class="maat-empty">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong>&euro;<?= number_format((float)($order['totaal'] ?? 0), 2, ',', '') ?></strong></td>
                                <td><?= htmlspecialchars($order['opmerking'] ?? '') ?: '<span class="maat-empty">—</span>' ?></td>
                                <td style="font-size:0.85rem;"><?= $datum ?></td>
                                <td>
                                    <button type="button" class="btn btn-save"
                                            onclick="openOrderModal(<?= (int)$order['ordernummer'] ?>, '<?= htmlspecialchars(addslashes($order['klant_naam']), ENT_QUOTES) ?>')">
                                        <i class="fa-solid fa-check"></i> Afgerond
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>

            <?php elseif ($actief === 'categorieen'): ?>
                <!-- ══════════════════════════════════════════
                     TAB: CATEGORIEËN BEHEREN
                ══════════════════════════════════════════ -->
                <div class="page-title">
                    <i class="fa-solid fa-folder-open"></i> Categorieën beheren
                </div>

                <div class="card">
                        <div class="card-header">
                            <h2><?= count($categorieen) ?> categorie<?= count($categorieen) !== 1 ? 'ën' : '' ?></h2>
                        </div>
                        <table>
                            <thead>
                            <tr>
                                <th>Volgorde</th>
                                <th>Naam</th>
                                <th>Icoon</th>
                                <th>Preview</th>
                                <th>Acties</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($categorieen as $cat): ?>
                                <tr>
                                    <td><?= (int)$cat['volgorde'] ?></td>
                                    <td><?= htmlspecialchars($cat['naam']) ?></td>
                                    <td><?= htmlspecialchars($cat['icoon']) ?></td>
                                    <td>
                                        <i class="fa-solid <?= htmlspecialchars($cat['icoon']) ?> icon-preview"></i>
                                    </td>
                                    <td>
                                        <a href="admin_categorie_bewerken.php?id=<?= $cat['id'] ?>"
                                           class="btn btn-save" style="text-decoration:none;">
                                            <i class="fa-solid fa-pen"></i> Bewerken
                                        </a>
                                        <button type="button" class="btn-delete"
                                                onclick="openCatModal(<?= $cat['id'] ?>, '<?= htmlspecialchars(addslashes($cat['naam'])) ?>')">
                                            <i class="fa-solid fa-trash"></i> Verwijder
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <!-- Nieuwe categorie toevoegen -->
                <div class="add-section">
                    <h3><i class="fa-solid fa-plus"></i> Nieuwe categorie toevoegen</h3>
                    <form method="POST" action="admin.php?tab=categorieen">
                        <input type="hidden" name="add_cat" value="1">
                        <div class="add-form">
                            <div class="field">
                                <label>Naam</label>
                                <input type="text" name="cat_naam" placeholder="bijv. Pizza" required>
                            </div>
                            <div class="field">
                                <label>Font Awesome icoon</label>
                                <input type="text" name="cat_icoon" placeholder="fa-star" value="fa-star">
                            </div>
                            <div class="field">
                                <label>Volgorde</label>
                                <input type="number" name="cat_volgorde" placeholder="99" min="0" class="input-narrow">
                            </div>
                            <div class="field" class="field-end">
                                <button type="submit" class="btn btn-add">
                                    <i class="fa-solid fa-plus"></i> Toevoegen
                                </button>
                            </div>
                        </div>
                    </form>
                    <p class="icon-hint">
                        <i class="fa-solid fa-circle-info"></i>
                        Zoek een icoon op <a href="https://fontawesome.com/icons" target="_blank">fontawesome.com/icons</a>
                        en gebruik de naam zonder het <code>fa-solid</code> prefix, bijv. <code>fa-pizza-slice</code>.
                    </p>
                </div>

            <?php elseif ($activeCat): ?>
                <!-- ══════════════════════════════════════════
                     TAB: PRODUCTEN VAN EEN CATEGORIE
                ══════════════════════════════════════════ -->
                <div class="page-title">
                    <i class="fa-solid <?= htmlspecialchars($activeCat['icoon']) ?>"></i>
                    <?= htmlspecialchars($activeCat['naam']) ?>
                </div>

                <div class="card">
                        <div class="card-header">
                            <h2><?= count($producten) ?> product<?= count($producten) !== 1 ? 'en' : '' ?></h2>
                        </div>
                        <table>
                            <thead>
                            <tr>
                                <th>Volgorde</th>
                                <th>Naam</th>
                                <th>Beschrijving</th>
                                <th>Maat</th>
                                <th>Prijs (€)</th>
                                <th>Acties</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($producten as $prod): ?>
                                <tr>
                                    <td><?= (int)$prod['volgorde'] ?></td>
                                    <td><?= htmlspecialchars($prod['naam']) ?></td>
                                    <td><?= htmlspecialchars($prod['beschrijving'] ?? '') ?></td>
                                    <td>
                                        <?php if (!empty($prod['maat'])): ?>
                                            <span class="badge-maat"><?= htmlspecialchars($prod['maat']) ?></span>
                                        <?php else: ?>
                                            <span class="maat-empty">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>€<?= number_format($prod['prijs'], 2, ',', '') ?></td>
                                    <td>
                                        <a href="admin_product_bewerken.php?id=<?= $prod['id'] ?>&tab=<?= urlencode($actief) ?>"
                                           class="btn btn-save" style="text-decoration:none;">
                                            <i class="fa-solid fa-pen"></i> Bewerken
                                        </a>
                                        <button type="button" class="btn-delete"
                                                onclick="openModal(<?= $prod['id'] ?>, '<?= htmlspecialchars(addslashes($prod['naam'])) ?>')">
                                            <i class="fa-solid fa-trash"></i> Verwijder
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <!-- Nieuw product toevoegen -->
                <div class="add-section">
                    <h3><i class="fa-solid fa-plus"></i> Nieuw product toevoegen</h3>
                    <form method="POST" action="admin.php?tab=<?= $actief ?>">
                        <input type="hidden" name="add_product"  value="1">
                        <input type="hidden" name="prod_cat_id"  value="<?= $activeCat['id'] ?>">
                        <div class="add-form">
                            <div class="field">
                                <label>Naam</label>
                                <input type="text" name="prod_naam" placeholder="bijv. Kroket" required>
                            </div>
                            <div class="field field-wide">
                                <label>Beschrijving <span class="label-hint">(optioneel)</span></label>
                                <textarea name="prod_beschrijving" placeholder="bijv. Met mosterd en broodkruim" rows="2" class="textarea-full"></textarea>
                            </div>
                            <div class="field">
                                <label>Prijs (€)</label>
                                <input type="number" name="prod_prijs" placeholder="2.50" step="0.01" min="0" required>
                            </div>
                            <div class="field">
                                <label>Maat <span class="label-hint">(optioneel)</span></label>
                                <input type="text" name="prod_maat" placeholder="Klein / Groot">
                            </div>
                            <div class="field">
                                <label>Volgorde</label>
                                <input type="number" name="prod_volgorde" placeholder="99" min="0" class="input-narrow">
                            </div>
                            <div class="field">
                                <label>Vegetarisch</label>
                                <div class="checkbox-wrap">
                                    <input type="checkbox" name="prod_vega" id="prod_vega"
                                           class="checkbox-input">
                                    <label for="prod_vega" class="checkbox-label">
                                        Ja
                                    </label>
                                </div>
                            </div>
                            <div class="field" class="field-end">
                                <button type="submit" class="btn btn-add">
                                    <i class="fa-solid fa-plus"></i> Toevoegen
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            <?php endif; ?>
        </div>
    </div>

    <script>
        // Product verwijder modal
        function openModal(id, naam) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteItemName').textContent = naam;
            document.getElementById('deleteModal').classList.add('open');
        }
        function closeModal() {
            document.getElementById('deleteModal').classList.remove('open');
        }
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        // Bestelling afgerond modal
        function openOrderModal(id, naam) {
            document.getElementById('orderDoneId').value = id;
            document.getElementById('orderDoneNr').textContent = '#' + id;
            document.getElementById('orderDoneNaam').textContent = naam;
            document.getElementById('orderDoneModal').classList.add('open');
        }
        function closeOrderModal() {
            document.getElementById('orderDoneModal').classList.remove('open');
        }
        document.getElementById('orderDoneModal').addEventListener('click', function(e) {
            if (e.target === this) closeOrderModal();
        });

        // Categorie verwijder modal
        function openCatModal(id, naam) {
            document.getElementById('deleteCatId').value = id;
            document.getElementById('deleteCatName').textContent = naam;
            document.getElementById('deleteCatModal').classList.add('open');
        }
        function closeCatModal() {
            document.getElementById('deleteCatModal').classList.remove('open');
        }
        document.getElementById('deleteCatModal').addEventListener('click', function(e) {
            if (e.target === this) closeCatModal();
        });

    </script>

<?php endif; ?>
</body>
</html>