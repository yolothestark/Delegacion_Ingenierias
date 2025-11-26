<?php
// 1. Conexión MySQL
include 'includes/db_connect.php'; 

// 2. Conexión MongoDB
require_once 'vendor/autoload.php';

// --- TU CONTRASEÑA DE MONGODB ---
$password = "gamepass21"; 
$uri = "mongodb+srv://angelanguiano7655_db_user:" . $password . "@mrfrog.mgyl372.mongodb.net/?appName=MrFrog";

try {
    $mongoClient = new MongoDB\Client($uri);
    $mongoDb = $mongoClient->delegacion_db;
    
    echo "<h1>🚀 Iniciando Migración (Parte 3: Contacto y Soporte)...</h1>";

    // ---------------------------------------------------------
    // 1. MIGRAR MENSAJES DE CONTACTO
    // ---------------------------------------------------------
    echo "<h3>✉️ Migrando Mensajes de Contacto...</h3>";
    $sql = "SELECT * FROM mensajes_contacto";
    $result = $conn->query($sql);
    
    $col = $mongoDb->mensajes_contacto;
    $col->drop(); // Limpiamos por si acaso

    while ($row = $result->fetch_assoc()) {
        $documento = [
            'nombre' => $row['nombre'],
            'apellido' => $row['apellido'],
            'email' => $row['email'],
            'contenido' => $row['contenido'],
            // Convertimos la fecha a formato MongoDB BSON Date
            'fecha_envio' => new MongoDB\BSON\UTCDateTime(strtotime($row['fecha_envio']) * 1000)
        ];
        $col->insertOne($documento);
    }
    echo "✅ Mensajes de contacto migrados.<br>";

    // ---------------------------------------------------------
    // 2. MIGRAR TICKETS DE SOPORTE
    // ---------------------------------------------------------
    echo "<h3>🛠️ Migrando Tickets de Soporte...</h3>";
    $sql = "SELECT * FROM soporte_tickets";
    $result = $conn->query($sql);
    
    $col = $mongoDb->soporte_tickets;
    $col->drop();

    while ($row = $result->fetch_assoc()) {
        $documento = [
            'nombre_usuario' => $row['nombre_usuario'],
            'email' => $row['email'],
            'categoria_problema' => $row['categoria_problema'],
            'descripcion' => $row['descripcion'],
            'estado' => $row['estado'], // Pendiente, En Proceso, Resuelto
            'fecha_creacion' => new MongoDB\BSON\UTCDateTime(strtotime($row['fecha_creacion']) * 1000)
        ];
        $col->insertOne($documento);
    }
    echo "✅ Tickets de soporte migrados.<br>";


    echo "<h2>🏁 ¡MIGRACIÓN PARCIAL COMPLETADA!</h2>";
    echo "<p>Las tablas de Contacto y Soporte ahora están en MongoDB Atlas.</p>";
    echo "<p>Con esto, ya tienes las 10 colecciones listas.</p>";

} catch (Exception $e) {
    die("Error fatal: " . $e->getMessage());
}
?>