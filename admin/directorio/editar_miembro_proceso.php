<?php
session_start();

// Sube 2 niveles y baja a 'includes' para conexión MongoDB
include '../../includes/db_connect_mongo.php';
// Sube 1 nivel
include '../auth_check.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    try {
        // Validación básica del ID
        if (empty($_POST['id_integrante'])) {
            throw new Exception("ID no proporcionado");
        }

        // Convertimos el ID string (que viene del form) a ObjectId
        $objectId = new MongoDB\BSON\ObjectId($_POST['id_integrante']);

        // Recogemos y saneamos datos
        $nombre_completo = trim($_POST['nombre_completo']);
        $id_cargo_fk     = (int)$_POST['id_cargo_fk']; // Cast a int vital
        $id_depto_fk     = (int)$_POST['id_depto_fk']; // Cast a int vital
        $email           = !empty($_POST['email']) ? trim($_POST['email']) : null;
        $telefono        = !empty($_POST['telefono']) ? trim($_POST['telefono']) : null;
        $bio             = !empty($_POST['bio']) ? trim($_POST['bio']) : null;
        $orden           = (int)($_POST['orden'] ?? 100);

        // Recuperamos la URL actual por si no se sube foto nueva
        $foto_url_db = $_POST['foto_url_actual'] ?? null;

        // --- LÓGICA DE FOTOS (File System) ---
        // Se mantiene casi igual, solo verificamos si hay nueva subida
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
            
            // 1. Borrar imagen anterior si existe y no es nula
            if (!empty($foto_url_db)) {
                // Sube 2 niveles desde admin/directorio/ -> Raíz
                $ruta_fisica_vieja = dirname(dirname(__DIR__)) . "/" . ltrim($foto_url_db, '/');
                if (file_exists($ruta_fisica_vieja) && is_file($ruta_fisica_vieja)) {
                    unlink($ruta_fisica_vieja);
                }
            }

            // 2. Subir imagen nueva
            $target_dir = dirname(dirname(__DIR__)) . "/uploads/perfiles/";
            
            // Crear directorio si no existe (seguridad extra)
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            $extension = pathinfo($_FILES["foto_perfil"]["name"], PATHINFO_EXTENSION);
            $nombre_archivo_unico = time() . '_' . uniqid() . '.' . $extension;
            $target_file_path = $target_dir . $nombre_archivo_unico;

            if (move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $target_file_path)) {
                $foto_url_db = "uploads/perfiles/" . $nombre_archivo_unico; 
            }
        }

        // --- LÓGICA MONGODB ---

        // 1. Buscar Nombres de Cargo y Depto actualizados
        // Es necesario para mantener la consistencia del documento embebido
        $cargoDoc = $db->cargos->findOne(['id_cargo' => $id_cargo_fk]);
        $deptoDoc = $db->departamentos->findOne(['id_depto' => $id_depto_fk]);

        $nombre_cargo = $cargoDoc ? $cargoDoc['nombre_cargo'] : 'Sin asignar';
        $nombre_depto = $deptoDoc ? $deptoDoc['nombre_depto'] : 'Sin asignar';

        // 2. Preparar el array de actualización ($set)
        $datosActualizados = [
            'nombre_completo' => $nombre_completo,
            'email'           => $email,
            'telefono'        => $telefono,
            'bio'             => $bio,
            'orden'           => $orden,
            'foto_url'        => $foto_url_db,
            'fecha_edicion'   => new MongoDB\BSON\UTCDateTime(), // Timestamp de actualización
            
            // Actualizamos los sub-documentos
            'cargo' => [
                'id'     => $id_cargo_fk,
                'nombre' => $nombre_cargo
            ],
            'departamento' => [
                'id'     => $id_depto_fk,
                'nombre' => $nombre_depto
            ]
        ];

        // 3. Ejecutar Update
        $resultado = $db->integrantes->updateOne(
            ['_id' => $objectId],
            ['$set' => $datosActualizados]
        );

        // Verificamos si encontró el documento (MatchedCount)
        // Nota: ModifiedCount puede ser 0 si guardaste sin cambiar ningún dato, 
        // pero eso cuenta como "éxito" en la lógica de usuario.
        if ($resultado->getMatchedCount() > 0) {
            header("Location: gestionar_directorio.php?status=editado");
        } else {
            // Si no hizo match es que el ID no existía
            header("Location: gestionar_directorio.php?status=error_editar");
        }

    } catch (Exception $e) {
        // Log del error si es necesario y redirección
        // error_log($e->getMessage());
        header("Location: gestionar_directorio.php?status=error_excepcion");
    }
    
    exit;

} else {
    header("Location: gestionar_directorio.php");
    exit;
}
?>