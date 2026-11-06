<?php
require_once 'config/conexion.php';

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id_producto = ?");
    $stmt->execute([$id]);
}

header('Location: index.php');
exit;
?>