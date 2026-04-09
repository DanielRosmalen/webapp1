<?php
ob_start();
session_start();

// ─── DB Connectie ──────────────────────────────────────────────
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

// ─── Auth guard ────────────────────────────────────────────────
if (!($_SESSION['admin'] ?? false)) {
    header('Location: admin.php');
    exit;
}

// ─── Product ophalen ───────────────────────────────────────────
$id  = (int)($_GET['id'] ?? 0);
$tab = $_GET['tab'] ?? 'categorieen';

if ($id <= 0) {
    header('Location: admin.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM `producten` WHERE id = :id");
$stmt->execute([':id' => $id]);
$prod = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prod) {
    header('Location: admin.php');
    exit;
}

$terugLink = 'admin.php?tab=' . urlencode($tab);
$fout      = '';

// ─── Product opslaan ──────────────────────────────────────────
if (isset($_POST['save_product'])) {
    $naam         = trim($_POST['prod_naam'] ?? '');
    $beschrijving = trim($_POST['prod_beschrijving'] ?? '');
    $prijs        = (float)str_replace(',', '.', $_POST['prod_prijs'] ?? '0');
    $volgorde     = (int)($_POST['prod_volgorde'] ?? 99);

    if ($naam === '' || $prijs < 0) {
        $fout = 'Naam mag niet leeg zijn en prijs moet 0 of hoger zijn.';
    } else {
        $db->prepare(
            "UPDATE `producten` SET naam=:n, beschrijving=:b, prijs=:p, volgorde=:v WHERE id=:id"
        )->execute([
            ':n'  => $naam,
            ':b'  => $beschrijving ?: null,
            ':p'  => $prijs,
            ':v'  => $volgorde,
            ':id' => $id,
        ]);
        header('Location: ' . $terugLink . '&saved=product');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product bewerken – Snackcorner Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="Styling/styles.css" />
</head>
<body>

    <div class="topbar">
        <h1><i class="fa-solid fa-fire-flame-curved"></i> Snackcorner Admin</h1>
        <div class="topbar-actions">
            <a href="index.php" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> Bekijk website</a>
            <a href="admin.php?logout=1"><i class="fa-solid fa-right-from-bracket"></i> Uitloggen</a>
        </div>
    </div>

    <div class="wrapper">
        <div class="main">

            <div class="page-title">
                <a href="<?= htmlspecialchars($terugLink) ?>" class="btn btn-ghost">
                    <i class="fa-solid fa-arrow-left"></i> Terug
                </a>
                &nbsp;<i class="fa-solid fa-pen"></i> Product bewerken
            </div>

            <?php if ($fout): ?>
                <div class="alert warning"><?= htmlspecialchars($fout) ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2><?= htmlspecialchars($prod['naam']) ?></h2>
                </div>

                <form method="POST"
                      action="admin_product_bewerken.php?id=<?= $id ?>&tab=<?= urlencode($tab) ?>"
                      style="padding: 1.25rem 1.5rem;">
                    <input type="hidden" name="save_product" value="1">

                    <div class="add-form">
                        <div class="field">
                            <label>Naam</label>
                            <input type="text" name="prod_naam"
                                   value="<?= htmlspecialchars($prod['naam']) ?>" required>
                        </div>
                        <div class="field field-wide">
                            <label>Beschrijving <span class="label-hint">(optioneel)</span></label>
                            <textarea name="prod_beschrijving" rows="3" class="textarea-full"
                                      placeholder="bijv. Met mosterd en broodkruim"><?= htmlspecialchars($prod['beschrijving'] ?? '') ?></textarea>
                        </div>
                        <div class="field">
                            <label>Prijs (€)</label>
                            <input type="text" name="prod_prijs" inputmode="decimal"
                                   value="<?= number_format((float)$prod['prijs'], 2, ',', '') ?>" required>
                        </div>
                        <div class="field">
                            <label>Volgorde</label>
                            <input type="number" name="prod_volgorde" min="0" class="input-narrow"
                                   value="<?= (int)$prod['volgorde'] ?>">
                        </div>
                    </div>

                    <div class="text-right" style="margin-top: 1rem;">
                        <a href="<?= htmlspecialchars($terugLink) ?>" class="btn btn-cancel">Annuleren</a>
                        <button type="submit" class="btn btn-save">
                            <i class="fa-solid fa-floppy-disk"></i> Opslaan
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

</body>
</html>
