<?php

session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php');
    exit;
}
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}
require_once 'config/conexion.php';

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id_producto = ?");
    $stmt->execute([$id]);
}

header('Location: index.php');
exit;
?>
