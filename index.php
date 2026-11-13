<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}
require_once 'config/conexion.php';

$stmt = $pdo->query("SELECT p.*, c.nombre AS categoria FROM productos p LEFT JOIN categorias c ON p.id_categoria = c.id_categoria ORDER BY p.id_producto DESC");
$productos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-shop"></i> Sistema de Tienda</a>
            <div class="navbar-nav ms-auto align-items-center">
                <a class="nav-link active" href="index.php">Catálogo</a>
                <a class="nav-link" href="cotizador.php">Cotizador</a>
                <a class="nav-link" href="reporte_metricas.php">Reportes</a>
                <span class="navbar-text text-light ms-3 me-2">Hola, <?= htmlspecialchars($_SESSION['nombre'] ?? '') ?></span>
                <a class="btn btn-outline-light btn-sm" href="logout.php"><i class="bi bi-box-arrow-right"></i> Salir</a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Catálogo de Productos</h2>
            <a href="crear.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nuevo Producto</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['codigo']) ?></td>
                                <td><?= htmlspecialchars($p['nombre']) ?></td>
                                <td><?= htmlspecialchars($p['categoria'] ?? 'Sin categoría') ?></td>
                                <td>$<?= number_format($p['precio'], 2) ?></td>
                                <td><?= $p['stock'] ?></td>
                                <td>
                                    <?php if ($p['activo']): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="editar.php?id=<?= $p['id_producto'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                    <a href="eliminar.php?id=<?= $p['id_producto'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar este producto?');"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
