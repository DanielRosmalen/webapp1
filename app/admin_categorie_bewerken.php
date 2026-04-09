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

// ─── Categorie ophalen ─────────────────────────────────────────
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: admin.php?tab=categorieen');
    exit;
}

$stmt = $db->prepare("SELECT * FROM `categorieen` WHERE id = :id");
$stmt->execute([':id' => $id]);
$cat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cat) {
    header('Location: admin.php?tab=categorieen');
    exit;
}

$terugLink = 'admin.php?tab=categorieen';
$fout      = '';

// ─── Categorie opslaan ─────────────────────────────────────────
if (isset($_POST['save_cat'])) {
    $naam     = trim($_POST['cat_naam'] ?? '');
    $icoon    = trim($_POST['cat_icoon'] ?? 'fa-star');
    $volgorde = (int)($_POST['cat_volgorde'] ?? 99);

    if ($naam === '') {
        $fout = 'Naam mag niet leeg zijn.';
    } else {
        $db->prepare(
            "UPDATE `categorieen` SET naam=:n, icoon=:i, volgorde=:v WHERE id=:id"
        )->execute([
            ':n'  => $naam,
            ':i'  => $icoon,
            ':v'  => $volgorde,
            ':id' => $id,
        ]);
        header('Location: ' . $terugLink . '&saved=categorie');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorie bewerken – Snackcorner Admin</title>
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
                &nbsp;<i class="fa-solid fa-pen"></i> Categorie bewerken
            </div>

            <?php if ($fout): ?>
                <div class="alert warning"><?= htmlspecialchars($fout) ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2>
                        <i class="fa-solid <?= htmlspecialchars($cat['icoon']) ?>" id="iconPreview"></i>
                        <?= htmlspecialchars($cat['naam']) ?>
                    </h2>
                </div>

                <form method="POST"
                      action="admin_categorie_bewerken.php?id=<?= $id ?>"
                      style="padding: 1.25rem 1.5rem;">
                    <input type="hidden" name="save_cat" value="1">

                    <div class="add-form">
                        <div class="field">
                            <label>Naam</label>
                            <input type="text" name="cat_naam"
                                   value="<?= htmlspecialchars($cat['naam']) ?>" required>
                        </div>
                        <div class="field">
                            <label>Font Awesome icoon</label>
                            <input type="text" name="cat_icoon" class="icon-input"
                                   value="<?= htmlspecialchars($cat['icoon']) ?>"
                                   placeholder="bijv. fa-burger"
                                   oninput="updatePreview(this)">
                        </div>
                        <div class="field">
                            <label>Preview</label>
                            <i class="fa-solid <?= htmlspecialchars($cat['icoon']) ?> icon-preview" id="iconPreviewField"></i>
                        </div>
                        <div class="field">
                            <label>Volgorde</label>
                            <input type="number" name="cat_volgorde" min="0" class="input-narrow"
                                   value="<?= (int)$cat['volgorde'] ?>">
                        </div>
                    </div>

                    <p class="icon-hint" style="margin-top: 0.75rem;">
                        <i class="fa-solid fa-circle-info"></i>
                        Zoek een icoon op <a href="https://fontawesome.com/icons" target="_blank">fontawesome.com/icons</a>
                        en gebruik de naam zonder het <code>fa-solid</code> prefix, bijv. <code>fa-pizza-slice</code>.
                    </p>

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

    <script>
        function updatePreview(input) {
            var preview = document.getElementById('iconPreviewField');
            if (preview) preview.className = 'fa-solid ' + input.value.trim() + ' icon-preview';
        }
    </script>

</body>
</html>
