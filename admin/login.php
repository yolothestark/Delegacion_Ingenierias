<?php
session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: index.php');
    exit;
}

$error_message = '';
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Detectar ruta base para el CSS y el botón "Regresar"
$es_local = ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1');
$ruta_base_publica = $es_local ? "/Delegacion_Ingenierias" : "";

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Administración</title>
    <!-- CORRECCIÓN: Ruta absoluta dinámica al CSS -->
    <link rel="stylesheet" href="<?php echo $ruta_base_publica; ?>/css/admin.css">
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
        
        <!-- Enlace de regreso corregido -->
        <a href="<?php echo $ruta_base_publica; ?>/index.php" class="btn-regresar">
            &larr; Regresar al sitio principal
        </a>

    </div>
</body>
</html>