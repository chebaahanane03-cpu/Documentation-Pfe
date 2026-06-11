<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];
$product = getOne($pdo, $id);

if (!$product) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $price    = $_POST['price'];
    $quantity = $_POST['quantity'];
    $catId    = $_POST['category_id'];

    updateProduct($pdo, $id, $name, $price, $quantity, $catId);

    $_SESSION['flash'] = [
        'type' => 'success',
        'msg'  => '✅ Le produit "' . htmlspecialchars($name) . '" a été mis à jour avec succès !'
    ];
    header("Location: index.php");
    exit;
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Produit · Novalya</title>
    <meta name="description" content="Modifier un produit existant dans le stock Novalya.">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="form-page-body">

<div class="container">

    <div class="container-header">
        <div class="container-header-icon">✏️</div>
        <div>
            <h2>Modifier le Produit</h2>
            <p>Mettez à jour les informations du bijou</p>
        </div>
    </div>

    <div class="container-body">

        <!-- Badge produit -->
        <div class="edit-badge">
            <span class="edit-badge-label">📝 Produit en cours de modification</span>
            <span class="edit-badge-name"><?= htmlspecialchars($product['name']) ?></span>
        </div>

        <form method="POST" id="edit-form" novalidate autocomplete="off">

            <div class="form-group">
                <label for="name">Nom du produit <span style="color:var(--red)">*</span></label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?= htmlspecialchars($product['name']) ?>"
                    required
                >
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price">Prix (Dh) <span style="color:var(--red)">*</span></label>
                    <input
                        type="number"
                        id="price"
                        name="price"
                        step="0.01"
                        min="0"
                        value="<?= htmlspecialchars($product['price']) ?>"
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="quantity">Quantité <span style="color:var(--red)">*</span></label>
                    <input
                        type="number"
                        id="quantity"
                        name="quantity"
                        min="0"
                        value="<?= htmlspecialchars($product['quantity']) ?>"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="category_id">Catégorie <span style="color:var(--red)">*</span></label>
                <div class="select-wrapper" style="width:100%">
                    <select id="category_id" name="category_id" required style="border-radius:var(--r-md)">
                        <?php foreach ($categories as $cat): ?>
                            <option
                                value="<?= $cat['id'] ?>"
                                <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($cat['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="select-arrow">▾</span>
                </div>
            </div>

            <button type="submit" class="btn" id="btn-submit-edit">
                💾 Enregistrer les modifications
            </button>

        </form>

        <a href="index.php" class="back" id="link-back-edit">← Retour à la liste</a>
    </div>
</div>

<script>
document.getElementById('edit-form').addEventListener('submit', function(e) {
    let valid = true;
    this.querySelectorAll('[required]').forEach(function(el) {
        if (!el.value.trim()) {
            el.classList.add('error');
            valid = false;
        } else {
            el.classList.remove('error');
        }
    });
    if (!valid) {
        e.preventDefault();
        this.querySelector('.error').focus();
    }
});
document.querySelectorAll('[required]').forEach(function(el) {
    el.addEventListener('input', function() { this.classList.remove('error'); });
});
</script>

</body>
</html>
