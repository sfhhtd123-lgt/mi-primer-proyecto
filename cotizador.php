<?php
session_start();
// Si el usuario no es admin, lo expulsamos al catálogo o al cotizador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php');
    exit;
}
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}
require_once 'config/conexion.php';

$mensaje = '';
$error = '';

$stmt_prod = $pdo->query("SELECT p.id_producto, p.codigo, p.nombre, p.precio, c.nombre AS categoria 
                          FROM productos p 
                          LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
                          WHERE p.activo = 1 
                          ORDER BY p.nombre ASC");
$productos = $stmt_prod->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['procesar_cotizacion'])) {
    $cliente = trim($_POST['cliente'] ?? '');
    $productos_seleccionados = $_POST['productos'] ?? [];

    if (empty($cliente)) {
        $error = "El nombre del cliente es obligatorio.";
    } elseif (empty($productos_seleccionados)) {
        $error = "Debe seleccionar al menos un producto y especificar una cantidad.";
    } else {
        try {
            // INICIO DE TRANSACCIÓN PDO
            $pdo->beginTransaction();

            $subtotal_acumulado = 0;
            $items_a_insertar = [];

            foreach ($productos_seleccionados as $id_prod => $cantidad) {
                $cantidad = intval($cantidad);
                if ($cantidad > 0) {
                    $stmt_p = $pdo->prepare("SELECT precio FROM productos WHERE id_producto = ?");
                    $stmt_p->execute([$id_prod]);
                    $prod_info = $stmt_p->fetch();

                    if ($prod_info) {
                        $precio_unitario = floatval($prod_info['precio']);
                        $subtotal_item = $precio_unitario * $cantidad;
                        $subtotal_acumulado += $subtotal_item;

                        $items_a_insertar[] = [
                            'id_producto' => $id_prod,
                            'cantidad' => $cantidad,
                            'precio_unitario' => $precio_unitario,
                            'subtotal' => $subtotal_item
                        ];
                    }
                }
            }

            if (empty($items_a_insertar)) {
                throw new Exception("Las cantidades seleccionadas no son válidas.");
            }

            // Cálculo IVA (13%)
            $iva = $subtotal_acumulado * 0.13;
            $total = $subtotal_acumulado + $iva;

            // Insertar cabecera de cotización
            $stmt_cot = $pdo->prepare("INSERT INTO cotizaciones (cliente, subtotal, iva, total) VALUES (?, ?, ?, ?)");
            $stmt_cot->execute([$cliente, $subtotal_acumulado, $iva, $total]);
            $id_cotizacion = $pdo->lastInsertId();

            // Insertar detalle
            $stmt_det = $pdo->prepare("INSERT INTO detalle_cotizacion (id_cotizacion, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
            foreach ($items_a_insertar as $item) {
                $stmt_det->execute([
                    $id_cotizacion,
                    $item['id_producto'],
                    $item['cantidad'],
                    $item['precio_unitario'],
                    $item['subtotal']
                ]);
            }

            $pdo->commit();
            $mensaje = "¡Cotización guardada exitosamente con el ID #$id_cotizacion! Total con IVA (13%): $" . number_format($total, 2);

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error al procesar la cotización: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotizador de Ventas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-shop"></i> Sistema de Tienda</a>
            <div class="navbar-nav ms-auto align-items-center">
                <a class="nav-link" href="index.php">Catálogo</a>
                <a class="nav-link active" href="cotizador.php">Cotizador</a>
                <a class="nav-link" href="reporte_metricas.php">Reportes</a>
                <span class="navbar-text text-light ms-3 me-2">Hola, <?= htmlspecialchars($_SESSION['nombre'] ?? '') ?></span>
                <a class="btn btn-outline-light btn-sm" href="logout.php"><i class="bi bi-box-arrow-right"></i> Salir</a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <h2>Cotizador de Ventas</h2>
        <p class="text-muted">Calculadora en tiempo real / Servidor PHP (Incluye cálculo de IVA 13%)</p>

        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?= $mensaje ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="card shadow-sm p-4">
            <div class="mb-3">
                <label class="form-label fw-bold">Nombre del Cliente</label>
                <input type="text" name="cliente" class="form-control" required placeholder="Ej. Juan Pérez">
            </div>

            <h4 class="mt-4 mb-3">Seleccione Productos y Cantidades</h4>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Precio Unitario</th>
                            <th style="width: 150px;">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['codigo']) ?></td>
                            <td><?= htmlspecialchars($p['nombre']) ?></td>
                            <td><?= htmlspecialchars($p['categoria']) ?></td>
                            <td>$<?= number_format($p['precio'], 2) ?></td>
                            <td>
                                <input type="number" name="productos[<?= $p['id_producto'] ?>]" class="form-control" value="0" min="0">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <button type="submit" name="procesar_cotizacion" class="btn btn-success btn-lg mt-3">
                <i class="bi bi-calculator"></i> Procesar y Guardar Cotización
            </button>
        </form>
    </div>
</body>
</html>