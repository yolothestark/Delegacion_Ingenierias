<?php
session_start();
// Sube 2 niveles (a la raíz) y baja a 'includes' para la conexión MongoDB
include '../../includes/db_connect_mongo.php';
// Sube 1 nivel (a 'admin') y busca 'auth_check.php'
include '../auth_check.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $id_integrante_str = $_GET['id']; 

    try {
        // Convertimos el ID string a ObjectId de MongoDB
        $objectId = new MongoDB\BSON\ObjectId($id_integrante_str);
        $coleccionIntegrantes = $db->integrantes;

        // 1. Buscar el documento para obtener la foto
        $miembro = $coleccionIntegrantes->findOne(['_id' => $objectId]);

        if ($miembro) {
            // Verificar si tiene foto y borrarla
            if (!empty($miembro['foto_url'])) {
                
                // Construimos la ruta física absoluta
                // dirname(__DIR__, 2) sube dos niveles desde 'admin/directorio' -> raíz del proyecto
                $ruta_base = dirname(__DIR__, 2);
                $ruta_imagen_relativa = $miembro['foto_url'];
                
                // Quitamos cualquier '/' inicial para evitar duplicados al concatenar
                $ruta_imagen_relativa = ltrim($ruta_imagen_relativa, '/');
                
                $ruta_fisica = $ruta_base . '/' . $ruta_imagen_relativa;

                // Verificamos si existe antes de intentar borrar
                if (file_exists($ruta_fisica) && is_file($ruta_fisica)) {
                    unlink($ruta_fisica); 
                }
            }

            // 2. Borrar el documento de la base de datos
            $resultado = $coleccionIntegrantes->deleteOne(['_id' => $objectId]);

            if ($resultado->getDeletedCount() > 0) {
                // Éxito
                header("Location: gestionar_directorio.php?status=borrado");
            } else {
                // No se borró nada (raro si ya lo encontró antes)
                header("Location: gestionar_directorio.php?status=error_borrar");
            }
        } else {
            // No se encontró el miembro
            header("Location: gestionar_directorio.php?status=error_no_encontrado");
        }

    } catch (Exception $e) {
        // Error al convertir ID o de conexión
        header("Location: gestionar_directorio.php?status=error_excepcion");
    }
    
    exit;

} else {
    header("Location: gestionar_directorio.php");
    exit;
}
?>