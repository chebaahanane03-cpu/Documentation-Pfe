<?php
session_start();
require 'db.php';
require 'functions.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

// Récupérer le nom avant suppression pour le message flash
$stmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

deleteProduct($pdo, $id);

$_SESSION['flash'] = [
    'type' => 'success',
    'msg'  => $product
        ? '🗑️ Le produit "' . $product['name'] . '" a été supprimé avec succès.'
        : '🗑️ Produit supprimé avec succès.'
];

header("Location: index.php");
exit;
?>
