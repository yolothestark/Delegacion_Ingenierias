<?php
session_start();

// Sube 2 niveles y baja a 'includes' para conexión MongoDB
include '../../includes/db_connect_mongo.php';
// Sube 1 nivel
include '../auth_check.php';

// Verificación estricta de Rol
// Usamos trim() para evitar problemas con espacios en blanco
$rol_actual = isset($_SESSION['user_rol']) ? trim($_SESSION['user_rol']) : '';

if (strcasecmp($rol_actual, 'SuperAdmin') !== 0) {
    die("Acceso Denegado. Solo los SuperAdmin pueden borrar usuarios.");
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $id_a_borrar_str = $_GET['id'];
    $id_usuario_actual = $_SESSION['user_id'];

    // 1. Evitar que se borre a sí mismo
    // Comparamos como strings para evitar errores de tipos (ObjectId vs String)
    if ((string)$id_a_borrar_str === (string)$id_usuario_actual) {
        echo "<script>alert('No puedes borrar tu propia cuenta.'); window.location.href='gestionar_usuarios.php';</script>";
        exit;
    }

    try {
        // 2. Convertir a ObjectId
        $objectId = new MongoDB\BSON\ObjectId($id_a_borrar_str);

        // 3. Ejecutar borrado
        // Asumimos que la colección se llama 'usuarios'
        $resultado = $db->usuarios->deleteOne(['_id' => $objectId]);
        
        if ($resultado->getDeletedCount() > 0) {
            header("Location: gestionar_usuarios.php?status=borrado");
        } else {
            // El ID tenía formato válido pero no existía en la BD
            // Ocurre si recargas la página después de borrar
            header("Location: gestionar_usuarios.php?status=error");
        }

    } catch (Exception $e) {
        // Error si el ID no tiene formato hexadecimal válido
        header("Location: gestionar_usuarios.php?status=error");
    }
    
    exit;

} else {
    // Si no hay ID en la URL
    header("Location: gestionar_usuarios.php");
    exit;
}
?>