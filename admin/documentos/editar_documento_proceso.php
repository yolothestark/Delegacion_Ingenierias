<?php
session_start();

// Sube 2 niveles y baja a 'includes' para conexión MongoDB
include '../../includes/db_connect_mongo.php';
// Sube 1 nivel
include '../auth_check.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    try {
        // 1. Validar y convertir ID
        if (empty($_POST['id_documento'])) {
            throw new Exception("ID no proporcionado");
        }
        $objectId = new MongoDB\BSON\ObjectId($_POST['id_documento']);

        // 2. Recoger datos del formulario
        $nombre_documento = trim($_POST['nombre_documento']);
        $id_tipo_fk       = (int)$_POST['id_tipo_fk']; // Convertir a entero es vital si tus IDs en 'tipos' son numéricos
        $version          = trim($_POST['version']);

        // 3. Buscar información del Tipo de Documento para embeberla
        // Asumo que tu colección se llama 'tipos_documentos' o similar. 
        // Si se llama diferente (ej: 'categorias'), cambia el nombre aquí abajo:
        $coleccionTipos = $db->tipos_documentos; 
        $tipoDoc = $coleccionTipos->findOne(['id_tipo' => $id_tipo_fk]);

        $nombre_tipo = $tipoDoc ? $tipoDoc['nombre_tipo'] : 'Desconocido';

        // 4. Preparar datos para actualización ($set)
        $datosActualizados = [
            'nombre_documento' => $nombre_documento,
            'version'          => $version,
            'fecha_edicion'    => new MongoDB\BSON\UTCDateTime(),
            
            // Actualizamos el objeto embebido para evitar JOINs en el futuro
            'tipo_documento'   => [
                'id'     => $id_tipo_fk,
                'nombre' => $nombre_tipo
            ]
            // Nota: Mantenemos 'id_tipo_fk' en la raíz si lo necesitas por compatibilidad, 
            // aunque con el array 'tipo_documento' ya no sería estrictamente necesario.
            // 'id_tipo_fk' => $id_tipo_fk 
        ];

        // 5. Ejecutar Update
        $resultado = $db->documentos->updateOne(
            ['_id' => $objectId],
            ['$set' => $datosActualizados]
        );

        // Verificamos si el ID existía (MatchedCount)
        if ($resultado->getMatchedCount() > 0) {
            header("Location: gestionar_documentos.php?status=editado");
        } else {
            header("Location: gestionar_documentos.php?status=error_editar");
        }

    } catch (Exception $e) {
        // Error de ID inválido o conexión
        // error_log($e->getMessage());
        header("Location: gestionar_documentos.php?status=error_excepcion");
    }
    
    exit;

} else {
    header("Location: gestionar_documentos.php");
    exit;
}
?>