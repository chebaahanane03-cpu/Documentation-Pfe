<?php

require_once 'db.php';
require_once 'functions.php';

$products=getProducts($pdo);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">
<link rel="stylesheet"
href="style.css">

</head>

<body>

<h1> Novalya-Stock-Bijoux </h1>

<a href="add.php">
Ajouter Produit
</a>

<table>

<tr>

<th>ID</th>
<th>Nom</th>
<th>Prix</th>
<th>Quantité</th>
<th>Catégorie</th>
<th>Actions</th>

</tr>

<?php foreach($products as $p): ?>

<tr>

<td><?=htmlspecialchars($p['id'])?></td>

<td><?=htmlspecialchars($p['name'])?></td>

<td><?=htmlspecialchars($p['price'])?></td>

<td><?=htmlspecialchars($p['quantity'])?></td>

<td><?=htmlspecialchars($p['categories'])?></td>

<td>

<a href="edit.php?id=<?=urlencode($p['id'])?>">

Modifier

</a>

<a href="delete.php?id=<?=urlencode($p['id'])?>">

Supprimer

</a>

</td>

</tr>

<?php endforeach; ?>

</table>

</body>
</html>
