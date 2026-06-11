<?php

function getProducts($pdo){

$sql="SELECT products.*,
categories.nom AS categories

FROM products

JOIN categories

ON products.category_id=
categories.id";

$stmt=$pdo->query($sql);

return $stmt->fetchAll();

}



function addProduct(
$pdo,
$name,
$price,
$quantity,
$categories
){

$sql="INSERT INTO products
(name,price,quantity,category_id)

VALUES
(?,?,?,?)";

$stmt=$pdo->prepare($sql);

return $stmt->execute([
$name,
$price,
$quantity,
$categories
]);

}


function deleteProduct($pdo,$id){

$sql="DELETE FROM products
WHERE id=?";

$stmt=$pdo->prepare($sql);

return $stmt->execute([$id]);

}



function getOne($pdo,$id){

$sql="SELECT * FROM products
WHERE id=?";

$stmt=$pdo->prepare($sql);

$stmt->execute([$id]);

return $stmt->fetch();

}


function updateProduct(
$pdo,
$id,
$name,
$price,
$quantity,
$categories
){

$sql="UPDATE products
SET
name=?,
price=?,
quantity=?,
category_id=?

WHERE id=?";


$stmt=$pdo->prepare($sql);

return $stmt->execute([

$name,
$price,
$quantity,
$categories,
$id

]);

}

function getProductsByCategory($pdo,$category){

$sql="SELECT products.*,
categories.nom AS categories

FROM products

JOIN categories
ON products.category_id=categories.id

WHERE products.category_id=?";

$stmt=$pdo->prepare($sql);

$stmt->execute([$category]);

return $stmt->fetchAll();

}
?>
