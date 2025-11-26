<?php
/*
 * ARCHIVO DE CONEXIÓN A LA BASE DE DATOS
 *
 * Configuración para Laragon (o XAMPP por defecto)
 * Servidor (Host): localhost
 * Usuario (User): root
 * Contraseña (Pass): '' (vacía)
 * Base de Datos (DB): delegacion_db (la que creamos)
 */

// --- 1. Definir las credenciales ---
$db_host = 'localhost';
$db_user = 'root';
$db_pass = ''; // Por defecto, Laragon no usa contraseña
$db_name = 'delegacion_db';

// --- 2. Crear la conexión usando MySQLi ---
// (MySQLi es la versión moderna de las funciones de MySQL en PHP)
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// --- 3. Verificar si la conexión falló ---
if ($conn->connect_error) {
    // Si hay un error, detenemos la página y mostramos el error.
    // Es útil para depurar (saber qué salió mal).
    die("Error de Conexión: No se pudo conectar a la base de datos. " . $conn->connect_error);
}

// --- 4. Establecer el juego de caracteres a UTF-8 ---
// ¡MUY IMPORTANTE! Esto asegura que las tildes, 'ñ' y otros
// caracteres en español se muestren y guarden correctamente.
if (!$conn->set_charset("utf8")) {
    // Si falla, muestra un error
    printf("Error al cargar el conjunto de caracteres utf8: %s\n", $conn->error);
    exit();
}

// Si el script llega hasta aquí, la variable $conn está
// lista para ser usada en cualquier archivo que incluya este.

?>