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

    // ─── CATEGORIE OPSLAAN (naam/icoon/volgorde) ──────────────
    if (isset($_POST['save_cats'])) {
        $stmt = $db->prepare("UPDATE `categorieen` SET naam=:n, icoon=:i, volgorde=:v WHERE id=:id");
        foreach ($_POST['cat_namen'] as $id => $naam) {
            $naam     = trim($naam);
            $icoon    = trim($_POST['cat_icoontjes'][$id] ?? 'fa-star');
            $volgorde = (int)($_POST['cat_volgorden'][$id] ?? 99);
            if ($naam !== '') {
                $stmt->execute([':n' => $naam, ':i' => $icoon, ':v' => $volgorde, ':id' => (int)$id]);
            }
        }
        $saveMsg = 'success:Categorieën opgeslagen!';
    }

    // ─── PRODUCT VERWIJDEREN ──────────────────────────────────
    if (isset($_POST['delete_product'])) {
        $db->prepare("DELETE FROM `producten` WHERE id = :id")
                ->execute([':id' => (int)$_POST['delete_product_id']]);
        $saveMsg = 'success:Product verwijderd.';
    }

    // ─── PRODUCT TOEVOEGEN ────────────────────────────────────
    if (isset($_POST['add_product'])) {
        $catId    = (int)$_POST['prod_cat_id'];
        $naam     = trim($_POST['prod_naam'] ?? '');
        $prijs    = (float)str_replace(',', '.', $_POST['prod_prijs'] ?? '0');
        $maat     = trim($_POST['prod_maat'] ?? '');
        $vega     = isset($_POST['prod_vega']) ? 1 : 0;
        $volgorde = (int)($_POST['prod_volgorde'] ?? 99);

        if ($naam !== '' && $catId > 0) {
            $db->prepare(
                    "INSERT INTO `producten` (categorie_id, naam, maat, prijs, vega, volgorde)
                 VALUES (:c, :n, :m, :p, :v, :vo)"
            )->execute([
                    ':c'  => $catId,
                    ':n'  => $naam,
                    ':m'  => $maat ?: null,
                    ':p'  => $prijs,
                    ':v'  => $vega,
                    ':vo' => $volgorde,
            ]);
            $saveMsg = 'success:Product toegevoegd!';
        }
    }

    // ─── PRODUCTEN OPSLAAN ────────────────────────────────────
    if (isset($_POST['save_products'])) {
        $stmt = $db->prepare(
                "UPDATE `producten` SET naam=:n, prijs=:p, volgorde=:v WHERE id=:id"
        );
        $fouten = 0;
        foreach ($_POST['prod_namen'] as $id => $naam) {
            $naam  = trim($naam);
            $prijs = (float)str_replace(',', '.', $_POST['prod_prijzen'][$id] ?? '0');
            $vol   = (int)($_POST['prod_volgorden'][$id] ?? 99);
            if ($naam !== '' && $prijs >= 0) {
                $stmt->execute([':n' => $naam, ':p' => $prijs, ':v' => $vol, ':id' => (int)$id]);
            } else {
                $fouten++;
            }
        }
        $saveMsg = $fouten === 0
                ? 'success:Producten opgeslagen!'
                : "warning:Opgeslagen, maar $fouten rijen overgeslagen.";
    }
}

// ─── Data ophalen ─────────────────────────────────────────────
$categorieen = [];
$producten   = [];

if ($_SESSION['admin'] ?? false) {
    $categorieen = $db->query(
            "SELECT * FROM `categorieen` ORDER BY volgorde ASC, naam ASC"
    )->fetchAll(PDO::FETCH_ASSOC);

    // Actieve tab bepalen
    $actief = $_GET['tab'] ?? 'categorieen';
    $geldig = ['categorieen'];
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
} else {
    $actief = 'categorieen';
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
        <div style="display:flex;gap:1rem;align-items:center;">
            <a href="index.php" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> Bekijk website</a>
            <a href="admin.php?logout=1"><i class="fa-solid fa-right-from-bracket"></i> Uitloggen</a>
        </div>
    </div>

    <div class="wrapper">

        <!-- SIDEBAR -->
        <nav class="sidebar">
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

            <?php if ($actief === 'categorieen'): ?>
                <!-- ══════════════════════════════════════════
                     TAB: CATEGORIEËN BEHEREN
                ══════════════════════════════════════════ -->
                <div class="page-title">
                    <i class="fa-solid fa-folder-open"></i> Categorieën beheren
                </div>

                <form method="POST" action="admin.php?tab=categorieen">
                    <input type="hidden" name="save_cats" value="1">
                    <div class="card">
                        <div class="card-header">
                            <h2><?= count($categorieen) ?> categorie<?= count($categorieen) !== 1 ? 'ën' : '' ?></h2>
                            <button type="submit" class="btn btn-save">
                                <i class="fa-solid fa-floppy-disk"></i> Opslaan
                            </button>
                        </div>
                        <table>
                            <thead>
                            <tr>
                                <th>Volgorde</th>
                                <th>Naam</th>
                                <th>Font Awesome icoon</th>
                                <th>Preview</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($categorieen as $cat): ?>
                                <tr>
                                    <td>
                                        <input type="number" class="order-input"
                                               name="cat_volgorden[<?= $cat['id'] ?>]"
                                               value="<?= (int)$cat['volgorde'] ?>" min="0">
                                    </td>
                                    <td>
                                        <input type="text" class="name-input"
                                               name="cat_namen[<?= $cat['id'] ?>]"
                                               value="<?= htmlspecialchars($cat['naam']) ?>">
                                    </td>
                                    <td>
                                        <input type="text" class="name-input icon-input"
                                               name="cat_icoontjes[<?= $cat['id'] ?>]"
                                               value="<?= htmlspecialchars($cat['icoon']) ?>"
                                               placeholder="bijv. fa-burger"
                                               oninput="updatePreview(this)">
                                    </td>
                                    <td>
                                        <i class="fa-solid <?= htmlspecialchars($cat['icoon']) ?> icon-preview"></i>
                                    </td>
                                    <td>
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
                    <div style="text-align:right;">
                        <button type="submit" class="btn btn-save">
                            <i class="fa-solid fa-floppy-disk"></i> Opslaan
                        </button>
                    </div>
                </form>

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
                                <input type="number" name="cat_volgorde" placeholder="99" min="0" style="width:80px">
                            </div>
                            <div class="field" style="justify-content:flex-end;">
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

                <form method="POST" action="admin.php?tab=<?= $actief ?>">
                    <input type="hidden" name="save_products" value="1">
                    <div class="card">
                        <div class="card-header">
                            <h2><?= count($producten) ?> product<?= count($producten) !== 1 ? 'en' : '' ?></h2>
                            <button type="submit" class="btn btn-save">
                                <i class="fa-solid fa-floppy-disk"></i> Opslaan
                            </button>
                        </div>
                        <table>
                            <thead>
                            <tr>
                                <th>Volgorde</th>
                                <th>Naam</th>
                                <th>Maat</th>
                                <th>Prijs (€)</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($producten as $prod): ?>
                                <tr>
                                    <td>
                                        <input type="number" class="order-input"
                                               name="prod_volgorden[<?= $prod['id'] ?>]"
                                               value="<?= (int)$prod['volgorde'] ?>" min="0">
                                    </td>
                                    <td>
                                        <input type="text" class="name-input"
                                               name="prod_namen[<?= $prod['id'] ?>]"
                                               value="<?= htmlspecialchars($prod['naam']) ?>">
                                    </td>
                                    <td>
                                        <?php if (!empty($prod['maat'])): ?>
                                            <span class="badge-maat"><?= htmlspecialchars($prod['maat']) ?></span>
                                        <?php else: ?>
                                            <span style="color:#adb5bd;font-size:.8rem;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <input type="text" class="price-input"
                                               name="prod_prijzen[<?= $prod['id'] ?>]"
                                               value="<?= number_format($prod['prijs'], 2, ',', '') ?>"
                                               inputmode="decimal">
                                    </td>
                                    <td>
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
                    <div style="text-align:right;">
                        <button type="submit" class="btn btn-save">
                            <i class="fa-solid fa-floppy-disk"></i> Opslaan
                        </button>
                    </div>
                </form>

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
                            <div class="field">
                                <label>Prijs (€)</label>
                                <input type="number" name="prod_prijs" placeholder="2.50" step="0.01" min="0" required>
                            </div>
                            <div class="field">
                                <label>Maat <span style="font-weight:400;text-transform:none">(optioneel)</span></label>
                                <input type="text" name="prod_maat" placeholder="Klein / Groot">
                            </div>
                            <div class="field">
                                <label>Volgorde</label>
                                <input type="number" name="prod_volgorde" placeholder="99" min="0" style="width:80px">
                            </div>
                            <div class="field">
                                <label>Vegetarisch</label>
                                <div style="display:flex;align-items:center;gap:.4rem;height:38px;">
                                    <input type="checkbox" name="prod_vega" id="prod_vega"
                                           style="width:16px;height:16px;cursor:pointer;">
                                    <label for="prod_vega"
                                           style="font-size:.9rem;text-transform:none;letter-spacing:0;color:#212529;cursor:pointer;">
                                        Ja
                                    </label>
                                </div>
                            </div>
                            <div class="field" style="justify-content:flex-end;">
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

        // Live icoon preview
        function updatePreview(input) {
            var row     = input.closest('tr');
            var preview = row ? row.querySelector('.icon-preview') : null;
            if (!preview) return;
            // Verwijder alle fa-* klassen behalve fa-solid
            preview.className = 'fa-solid ' + input.value.trim() + ' icon-preview';
        }
    </script>

<?php endif; ?>
</body>
</html>