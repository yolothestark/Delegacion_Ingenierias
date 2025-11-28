<?php
session_start();

// Conexión
include '../../includes/db_connect_mongo.php';
// Auth Check
include '../auth_check.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $id_str = $_GET['id'];

    try {
        // Convertir ID
        $objectId = new MongoDB\BSON\ObjectId($id_str);
        
        // Ejecutar borrado en colección 'mensajes_contacto'
        $resultado = $db->mensajes_contacto->deleteOne(['_id' => $objectId]);
        
        if ($resultado->getDeletedCount() > 0) {
            header("Location: gestionar_mensajes.php?status=borrado");
        } else {
            header("Location: gestionar_mensajes.php?status=error");
        }

    } catch (Exception $e) {
        header("Location: gestionar_mensajes.php?status=error");
    }
    exit;

} else {
    header("Location: gestionar_mensajes.php");
    exit;
}
?>