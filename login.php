<?php
session_start();
require_once 'config/conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    $rol_seleccionado = $_POST['rol'] ?? ''; // Rol elegido en el select

    if (empty($usuario) || empty($password) || empty($rol_seleccionado)) {
        $error = "Por favor, complete todos los campos y seleccione un rol.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = "El usuario '$usuario' no existe en la base de datos.";
        } else {
            // Verificamos el hash con password_verify
            if (password_verify($password, $user['password'])) {
                
                // Opcional: Validar que el rol seleccionado coincida con el que tiene en la base de datos
                if ($user['rol'] !== $rol_seleccionado) {
                    $error = "El rol seleccionado no corresponde a este usuario.";
                } else {
                    $_SESSION['id_usuario'] = $user['id_usuario'];
                    $_SESSION['usuario'] = $user['usuario'];
                    $_SESSION['nombre'] = $user['nombre'];
                    $_SESSION['rol'] = $user['rol']; // Guardamos el rol real de la BD
                    
                    header('Location: index.php');
                    exit;
                }
            } else {
                $error = "La contraseña es incorrecta para este usuario.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Sistema de Tienda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">
    <div class="card shadow-sm p-4" style="width: 100%; max-width: 400px;">
        <div class="text-center mb-4">
            <i class="bi bi-shop text-primary" style="font-size: 3rem;"></i>
            <h3 class="fw-bold">Iniciar Sesión</h3>
            <p class="text-muted">Sistema de Tienda</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Tipo de Usuario (Rol)</label>
                <select name="rol" class="form-select" required>
                    <option value="" disabled selected>Seleccione su rol...</option>
                    <option value="admin">Administrador</option>
                    <option value="distribuidor">Distribuidor</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Usuario</label>
                <input type="text" name="usuario" class="form-control" required placeholder="Ej. admin">
            </div>
            <div class="mb-4">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required placeholder="********">
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">Ingresar</button>
        </form>
    </div>
</body>
</html>