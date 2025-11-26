<?php
session_start();

// Sube 2 niveles (a la raíz) y baja a 'includes' para conexión MongoDB
include '../../includes/db_connect_mongo.php';
// Sube 1 nivel (a 'admin') y busca 'auth_check.php'
include '../auth_check.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recoger datos básicos
    $nombre_documento = trim($_POST['nombre_documento']);
    $id_tipo_fk       = (int)$_POST['id_tipo_fk']; // Convertir a entero para buscar en colección de tipos
    $version          = trim($_POST['version']); 
    $id_usuario_fk    = $_SESSION['user_id']; // Asumimos que guardas el ID de usuario en sesión
    $ruta_archivo_db  = ""; 

    // 2. Validaciones de Archivo (Lógica de servidor, igual que antes)
    if (!isset($_FILES['archivo_pdf']) || $_FILES['archivo_pdf']['error'] != 0) {
        header("Location: gestionar_documentos.php?status=error_archivo");
        exit;
    }

    $tipo_archivo = mime_content_type($_FILES['archivo_pdf']['tmp_name']);
    if ($tipo_archivo != "application/pdf") {
        header("Location: gestionar_documentos.php?status=error_tipo");
        exit;
    }

    // 3. Subida física del archivo
    // Sube 2 niveles a la raíz: admin/documentos/ -> raiz/
    $target_dir = dirname(dirname(__DIR__)) . "/uploads/documentos/";
    
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true); 
    }
    
    // Generar nombre único
    $extension = pathinfo($_FILES["archivo_pdf"]["name"], PATHINFO_EXTENSION);
    $nombre_archivo_unico = time() . '_' . uniqid() . '.' . $extension;
    $target_file_path = $target_dir . $nombre_archivo_unico;
    
    if (move_uploaded_file($_FILES["archivo_pdf"]["tmp_name"], $target_file_path)) {
        // Ruta relativa para guardar en BD
        $ruta_archivo_db = "uploads/documentos/" . $nombre_archivo_unico;
    } else {
        header("Location: gestionar_documentos.php?status=error_movida");
        exit;
    }

    try {
        // --- LÓGICA MONGODB ---

        // A. Buscar información del Tipo de Documento para embeberla
        // Esto optimiza la lectura en gestionar_documentos.php
        $coleccionTipos = $db->tipos_documentos; 
        $tipoDoc = $coleccionTipos->findOne(['id_tipo' => $id_tipo_fk]);
        
        $nombre_tipo = $tipoDoc ? $tipoDoc['nombre_tipo'] : 'Desconocido';

        // B. Construir el documento
        $documento = [
            'nombre_documento' => $nombre_documento,
            'version'          => $version,
            'ruta_archivo'     => $ruta_archivo_db,
            'id_usuario_fk'    => $id_usuario_fk, // Referencia al usuario que subió
            
            // Fecha actual (MongoDB no tiene "DEFAULT CURRENT_TIMESTAMP", hay que ponerlo explícito)
            'fecha_subida'     => new MongoDB\BSON\UTCDateTime(),
            
            // Datos embebidos
            'tipo_documento'   => [
                'id'     => $id_tipo_fk,
                'nombre' => $nombre_tipo
            ]
            
            // Opcional: Si quieres mantener compatibilidad estricta con código viejo que busque este campo
            // 'id_tipo_fk' => $id_tipo_fk 
        ];

        // C. Insertar
        $resultado = $db->documentos->insertOne($documento);
        
        if ($resultado->getInsertedCount() > 0) {
            header("Location: gestionar_documentos.php?status=subido"); // Cambié 'creado' por 'subido' para ser más claro
        } else {
            header("Location: gestionar_documentos.php?status=error_db");
        }

    } catch (Exception $e) {
        // Error de conexión o escritura
        // error_log($e->getMessage());
        header("Location: gestionar_documentos.php?status=error_excepcion");
    }
    
    exit;

} else {
    header("Location: subir_documento.php");
    exit;
}
?>