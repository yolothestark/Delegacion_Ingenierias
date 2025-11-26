<?php
session_start(); // Iniciar la sesión para poder destruirla

// 1. Destruir todas las variables de sesión
$_SESSION = array();

// 2. Borrar la cookie de sesión del navegador
// Esto asegura que el ID de sesión anterior no pueda reutilizarse
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Finalmente, destruir la sesión en el servidor
session_destroy();

// 4. Redirigir al login
header("Location: login.php?logout=1");
exit;
?>