<?php
require_once 'db.php';
require_once 'functions.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$product = getOne($pdo, $id);

if (!$product) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    updateProduct(
        $pdo,
        $id,
        $_POST['name'],
        $_POST['price'],
        $_POST['quantity'],
        $_POST['category_id']
    );

    header("Location: index.php");
    exit;
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Produit</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>Modifier un Produit</h2>

    <form method="POST">
        <div class="form-group">
            <label>Nom du produit</label>
            <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>

        <div class="form-group">
            <label>Prix</label>
            <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>
        </div>

        <div class="form-group">
            <label>Quantite</label>
            <input type="number" name="quantity" value="<?= htmlspecialchars($product['quantity']) ?>" required>
        </div>

        <div class="form-group">
            <label>Categorie</label>
            <select name="category_id" required>
                <?php foreach ($categories as $category): ?>
                    <option
                        value="<?= $category['id'] ?>"
                        <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($category['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn">Modifier</button>
    </form>

    <a href="index.php" class="back">Retour a la liste</a>
</div>

</body>
</html>
