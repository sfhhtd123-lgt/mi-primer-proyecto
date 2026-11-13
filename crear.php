<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}
require_once 'config/conexion.php';

$mensaje = '';
$categorias = $pdo->query("SELECT * FROM categorias")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo']);
    $nombre = trim($_POST['nombre']);
    $id_categoria = $_POST['id_categoria'];
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $activo = isset($_POST['activo']) ? 1 : 0;

    if (!empty($codigo) && !empty($nombre) && $precio > 0) {
        $stmt = $pdo->prepare("INSERT INTO productos (codigo, nombre, id_categoria, precio, stock, activo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$codigo, $nombre, $id_categoria, $precio, $stock, $activo]);
        header('Location: index.php');
        exit;
    } else {
        $mensaje = "Por favor completa los campos requeridos correctamente.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <!-- BARRA DE NAVEGACIÓN -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-shop"></i> Sistema de Tienda</a>
            <div class="navbar-nav ms-auto align-items-center">
                <a class="nav-link" href="index.php">Catálogo</a>
                <a class="nav-link" href="cotizador.php">Cotizador</a>
                <a class="nav-link" href="reporte_metricas.php">Reportes</a>
                <span class="navbar-text text-light ms-3 me-2">Hola, <?= htmlspecialchars($_SESSION['nombre'] ?? '') ?></span>
                <a class="btn btn-outline-light btn-sm" href="logout.php"><i class="bi bi-box-arrow-right"></i> Salir</a>
            </div>
        </div>
    </nav>

    <div class="container my-5" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-body">
                <h3 class="mb-4">Registrar Nuevo Producto</h3>
                <?php if ($mensaje): ?>
                    <div class="alert alert-danger"><?= $mensaje ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Código</label>
                        <input type="text" name="codigo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre del Producto</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <select name="id_categoria" class="form-select">
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= $c['id_categoria'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Precio ($)</label>
                        <input type="number" step="0.01" name="precio" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" class="form-control" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="activo" class="form-check-input" id="activo" checked>
                        <label class="form-check-label" for="activo">Producto Activo</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Producto</button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
