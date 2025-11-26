<?php
session_start();

// Sube 2 niveles (a la raíz) y baja a 'includes' para conexión MongoDB
include '../../includes/db_connect_mongo.php';
// Sube 1 nivel (a 'admin') para checar sesión
include '../auth_check.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Recogemos datos y aseguramos tipos (MongoDB es estricto con los tipos)
    $nombre_completo = trim($_POST['nombre_completo']);
    $id_cargo_fk     = (int)$_POST['id_cargo_fk']; // Importante: cast a int para buscar correctamente
    $id_depto_fk     = (int)$_POST['id_depto_fk']; 
    $email           = !empty($_POST['email']) ? trim($_POST['email']) : null;
    $telefono        = !empty($_POST['telefono']) ? trim($_POST['telefono']) : null;
    $bio             = !empty($_POST['bio']) ? trim($_POST['bio']) : null;
    $orden           = (int)($_POST['orden'] ?? 100);

    $foto_url_db = null; 

    // --- 1. Lógica de Subida de Archivo (Filesystem) ---
    // Esto no cambia con la base de datos, se mantiene igual.
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
        
        $target_dir = dirname(dirname(__DIR__)) . "/uploads/perfiles/"; 
        
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true); 
        }

        // Usamos time() para evitar colisiones de nombre
        $extension = pathinfo($_FILES["foto_perfil"]["name"], PATHINFO_EXTENSION);
        $nombre_archivo_unico = time() . '_' . uniqid() . '.' . $extension;
        $target_file_path = $target_dir . $nombre_archivo_unico;

        if (move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $target_file_path)) {
            // Guardamos la ruta relativa para usarla en el <img src="...">
            $foto_url_db = "uploads/perfiles/" . $nombre_archivo_unico; 
        }
    }

    try {
        // --- 2. LÓGICA MONGODB ---
        
        // A. Buscar información extra para embeber (Pattern: Extended Reference)
        // Asumimos que en tus colecciones 'cargos' y 'departamentos' tienes un campo 'id_cargo' e 'id_depto' tipo entero.
        // Si usas el _id nativo de Mongo, habría que usar new MongoDB\BSON\ObjectId($id...).
        
        $cargoDoc = $db->cargos->findOne(['id_cargo' => $id_cargo_fk]);
        $deptoDoc = $db->departamentos->findOne(['id_depto' => $id_depto_fk]);

        $nombre_cargo = $cargoDoc ? $cargoDoc['nombre_cargo'] : 'Sin asignar';
        $nombre_depto = $deptoDoc ? $deptoDoc['nombre_depto'] : 'Sin asignar';

        // B. Construir el documento BSON
        $documento = [
            'nombre_completo' => $nombre_completo,
            'email'           => $email,
            'telefono'        => $telefono,
            'bio'             => $bio,
            'orden'           => $orden,
            'foto_url'        => $foto_url_db,
            'fecha_creacion'  => new MongoDB\BSON\UTCDateTime(), // Timestamp automático
            
            // EMBEDDING: Guardamos ID y Nombre para no tener que consultar otras tablas al listar
            'cargo' => [
                'id'     => $id_cargo_fk,
                'nombre' => $nombre_cargo
            ],
            'departamento' => [
                'id'     => $id_depto_fk,
                'nombre' => $nombre_depto
            ]
        ];

        // C. Insertar en la colección 'integrantes'
        $resultado = $db->integrantes->insertOne($documento);

        if ($resultado->getInsertedCount() > 0) {
            header("Location: gestionar_directorio.php?status=creado");
        } else {
            header("Location: gestionar_directorio.php?status=error_db");
        }

    } catch (Exception $e) {
        // Manejo de errores
        // error_log($e->getMessage()); // Descomentar para ver errores en logs del servidor
        header("Location: gestionar_directorio.php?status=error_excepcion");
    }
    
    exit;

} else {
    // Si intentan entrar directo sin POST
    header("Location: crear_miembro.php");
    exit;
}
?>