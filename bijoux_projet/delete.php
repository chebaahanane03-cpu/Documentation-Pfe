<?php

require 'db.php';
require 'functions.php';

if (!isset($_GET['id'])) {
header("Location:index.php");
exit;
}

$id=$_GET['id'];

deleteProduct($pdo,$id);

header("Location:index.php");
exit;

?>
