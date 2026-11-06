<?php
require_once 'config/conexion.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM productos WHERE id_producto = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch();

if (!$producto) {
    header('Location: index.php');
    exit;
}

$categorias = $pdo->query("SELECT * FROM categorias")->fetchAll();
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo']);
    $nombre = trim($_POST['nombre']);
    $id_categoria = $_POST['id_categoria'];
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $activo = isset($_POST['activo']) ? 1 : 0;

    if (!empty($codigo) && !empty($nombre) && $precio > 0) {
        $stmt = $pdo->prepare("UPDATE productos SET codigo = ?, nombre = ?, id_categoria = ?, precio = ?, stock = ?, activo = ? WHERE id_producto = ?");
        $stmt->execute([$codigo, $nombre, $id_categoria, $precio, $stock, $activo, $id]);
        header('Location: index.php');
        exit;
    } else {
        $mensaje = "Complete los campos requeridos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container my-5" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-body">
                <h3 class="mb-4">Editar Producto</h3>
                <?php if ($mensaje): ?>
                    <div class="alert alert-danger"><?= $mensaje ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Código</label>
                        <input type="text" name="codigo" class="form-control" value="<?= htmlspecialchars($producto['codigo']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre del Producto</label>
                        <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($producto['nombre']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <select name="id_categoria" class="form-select">
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= $c['id_categoria'] ?>" <?= $c['id_categoria'] == $producto['id_categoria'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Precio ($)</label>
                        <input type="number" step="0.01" name="precio" class="form-control" value="<?= $producto['precio'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" class="form-control" value="<?= $producto['stock'] ?>" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="activo" class="form-check-input" id="activo" <?= $producto['activo'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="activo">Producto Activo</label>
                    </div>
                    <button type="submit" class="btn btn-warning">Actualizar Producto</button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>