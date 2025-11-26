<?php
// ¡session_start() SE HA QUITADO DE AQUÍ!
// Porque 'admin/layouts/header.php' ya lo inicia.

// 2. Seguridad: Chequear si el usuario está logueado
// Si no existe el "pase" 'loggedin', lo sacamos al login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    
    // Redirige al 'login.php' que está en ESTA MISMA carpeta ('admin')
    header('Location: login.php');
    exit;
}
?>