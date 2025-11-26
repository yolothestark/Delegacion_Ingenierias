<?php
session_start();

// Si el usuario YA está logueado, lo mandamos al dashboard
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: index.php'); // Redirige al index.php DENTRO de admin/
    exit;
}

// Comprobar si hay un mensaje de error (generado en login_proceso.php)
$error_message = '';
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Borrar el mensaje para no mostrarlo de nuevo
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Administración</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    
    <div class="login-container">
        <form action="login_proceso.php" method="POST">
            <h2>Acceso de Administración</h2>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-box">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="input-group">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="input-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn-login">Ingresar</button>
        </form>
        
        <a href="../index.php" class="btn-regresar">
            &larr; Regresar al sitio principal
        </a>

    </div>
</body>
</html>