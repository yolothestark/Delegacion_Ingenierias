<?php
session_start();
include '../includes/db_connect.php';

echo "<body style='font-family: sans-serif; padding: 40px; background: #f4f4f4;'>";
echo "<div style='background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto;'>";
echo "<h2 style='color: #004a99; margin-top: 0;'>🕵️‍♂️ Diagnóstico de Roles</h2>";

// 1. REVISAR LA SESIÓN (Lo que tu navegador cree que eres)
echo "<h3>1. Tu Sesión Actual (Navegador)</h3>";
if (isset($_SESSION['username'])) {
    echo "<p>Usuario en sesión: <strong>" . $_SESSION['username'] . "</strong></p>";
    echo "<p>Rol en sesión: <strong style='color: blue; font-size: 1.2em;'>" . ($_SESSION['user_rol'] ?? 'NO DEFINIDO') . "</strong></p>";
} else {
    echo "<p style='color: red;'>No has iniciado sesión.</p>";
}

echo "<hr>";

// 2. REVISAR LA BASE DE DATOS (La verdad absoluta)
echo "<h3>2. Base de Datos (Servidor)</h3>";
$sql = "SELECT username, rol FROM usuarios WHERE username = 'admin'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "<p>El usuario 'admin' en la BD es: <strong style='color: green; font-size: 1.2em;'>" . $row['rol'] . "</strong></p>";
    
    // Análisis del problema
    if ($row['rol'] !== 'SuperAdmin') {
        echo "<div style='background: #ffebee; color: #c62828; padding: 10px; border-radius: 4px; border: 1px solid #ef9a9a;'>";
        echo "<strong>¡PROBLEMA DETECTADO!</strong><br>";
        echo "Tu usuario 'admin' NO es SuperAdmin en la base de datos. Por eso no ves el menú.";
        echo "<br><br>";
        echo "<form method='POST'>";
        echo "<button type='submit' name='fix_admin' style='background: #d32f2f; color: white; border: none; padding: 10px 20px; cursor: pointer; font-weight: bold; border-radius: 4px;'>REPARAR AHORA (Hacer SuperAdmin)</button>";
        echo "</form>";
        echo "</div>";
    } else {
        echo "<p style='color: green;'>✅ El usuario 'admin' está configurado correctamente en la BD.</p>";
    }

} else {
    echo "<p style='color: red;'>El usuario 'admin' no existe en la BD.</p>";
}

// LÓGICA DE REPARACIÓN (Si presionas el botón)
if (isset($_POST['fix_admin'])) {
    $conn->query("UPDATE usuarios SET rol = 'SuperAdmin' WHERE username = 'admin'");
    echo "<script>alert('¡Reparado! Por favor cierra sesión y vuelve a entrar.'); window.location.href='debug_rol.php';</script>";
}

echo "<hr>";
echo "<p style='text-align: center;'><a href='index.php' style='color: #666;'>Volver al Panel</a> | <a href='logout.php' style='color: #d32f2f; font-weight: bold;'>Cerrar Sesión</a></p>";
echo "</div>";
echo "</body>";
?>