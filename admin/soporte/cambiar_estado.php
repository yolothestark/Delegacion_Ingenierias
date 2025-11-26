<?php
session_start();

// Sube 2 niveles y baja a 'includes' para conexión MongoDB
include '../../includes/db_connect_mongo.php';
// Sube 1 nivel
include '../auth_check.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validamos que lleguen los datos
    if (empty($_POST['id_ticket']) || empty($_POST['nuevo_estado'])) {
        header("Location: gestionar_soporte.php?status=error");
        exit;
    }

    $id_ticket_str = $_POST['id_ticket'];
    $nuevo_estado = $_POST['nuevo_estado'];
    
    try {
        // 1. Convertir el ID string a ObjectId
        $objectId = new MongoDB\BSON\ObjectId($id_ticket_str);

        // 2. Ejecutar la actualización
        // Usamos $set para modificar solo el campo 'estado' sin borrar el resto
        // Opcional: Agregamos fecha de actualización si te sirve de auditoría
        $resultado = $db->soporte_tickets->updateOne(
            ['_id' => $objectId],
            [
                '$set' => [
                    'estado' => $nuevo_estado,
                    'fecha_actualizacion' => new MongoDB\BSON\UTCDateTime()
                ]
            ]
        );

        // 3. Verificar resultados
        // getMatchedCount() > 0 significa que encontró el ticket (aunque el estado fuera el mismo)
        if ($resultado->getMatchedCount() > 0) {
            header("Location: gestionar_soporte.php?status=actualizado");
        } else {
            // No encontró el ID
            header("Location: gestionar_soporte.php?status=error");
        }

    } catch (Exception $e) {
        // Error de formato de ID o conexión
        // error_log($e->getMessage());
        header("Location: gestionar_soporte.php?status=error");
    }

    exit;

} else {
    // Si intentan entrar directo sin POST
    header("Location: gestionar_soporte.php");
    exit;
}
?>