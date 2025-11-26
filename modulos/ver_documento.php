<?php
    // 1. Conexión a MongoDB (subimos un nivel desde Modulos/)
    include '../includes/db_connect_mongo.php';
    

    // Inicializamos variables
    $doc = null;
    $error = null;
    $titulo_pagina = "Detalle de Documento";
    $ruta_fisica = ""; // Inicializar para evitar warning
    $ruta_web = "";

    // 2. Verificar ID
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        
        $id_doc_str = $_GET['id'];
        
        try {
            // Convertir string a ObjectId
            $objectId = new MongoDB\BSON\ObjectId($id_doc_str);
            
            // Obtener datos del documento
            $doc = $db->documentos->findOne(['_id' => $objectId]);
            
            if ($doc) {
                $titulo_pagina = $doc['nombre_documento']; // Título de la pestaña
                
                // Construir ruta absoluta para verificar y relativa para mostrar
                $ruta_db = $doc['ruta_archivo'];
                
                // Limpiamos slash inicial por seguridad
                $ruta_limpia = ltrim($ruta_db, '/');
                
                // Ruta física (Servidor)
                $ruta_fisica = dirname(__DIR__) . '/' . $ruta_limpia;
                
                // Ruta web (Navegador) - Subimos un nivel con ../
                $ruta_web = "../" . $ruta_limpia;

            } else {
                $error = "Documento no encontrado.";
                $titulo_pagina = "Error - No encontrado";
            }

        } catch (Exception $e) {
            $error = "ID de documento inválido.";
            $titulo_pagina = "Error";
        }
        
    } else {
        $error = "ID no proporcionado.";
        $titulo_pagina = "Error";
    }

    // 3. Header (layouts)
    include '../includes/header.php';
?>

<main class="container-detalle">

    <?php if ($error || !file_exists($ruta_fisica)): ?>
        
        <div style="text-align: center; padding: 50px;">
            <h2 style="color: #d9534f;">Lo sentimos</h2>
            <p><?php echo $error ?? "El archivo físico no existe en el servidor."; ?></p>
            <a href="documentos.php" class="btn-principal" style="background-color: #003b46; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Volver a Documentos</a>
        </div>

    <?php else: ?>

        <?php
            // Formatear fecha de MongoDB
            $fecha_texto = "N/A";
            if (isset($doc['fecha_subida']) && $doc['fecha_subida'] instanceof MongoDB\BSON\UTCDateTime) {
                $fecha_texto = $doc['fecha_subida']->toDateTime()->format('d/m/Y');
            } elseif (isset($doc['fecha_subida'])) {
                // Fallback para strings antiguos
                $fecha_texto = date('d/m/Y', strtotime($doc['fecha_subida']));
            }
        ?>

        <!-- ENCABEZADO DEL VISOR -->
        <div class="visor-header">
            <div class="visor-info">
                <h1 class="visor-titulo"><?php echo htmlspecialchars($doc['nombre_documento']); ?></h1>
                <p class="visor-meta">
                    Versión: <?php echo htmlspecialchars($doc['version'] ?? '1.0'); ?> | 
                    Fecha: <?php echo $fecha_texto; ?>
                </p>
            </div>
            <div class="visor-actions">
                <!-- Botón para descargar el archivo (backup) -->
                <a href="<?php echo $ruta_web; ?>" download class="btn-descargar-visor">
                    ⬇ Descargar Archivo
                </a>
                <a href="documentos.php" class="btn-volver-visor">
                    &times; Cerrar
                </a>
            </div>
        </div>

        <!-- EL VISOR (IFRAME) -->
        <div class="visor-container">
            <!-- 
                 El iframe carga el PDF. 
                 height="800px" asegura que sea alto y legible.
            -->
            <iframe src="<?php echo $ruta_web; ?>" width="100%" height="800px" style="border: none; background-color: #f4f4f4;">
                Tu navegador no soporta la visualización de PDFs. 
                <a href="<?php echo $ruta_web; ?>">Descárgalo aquí</a>.
            </iframe>
        </div>

    <?php endif; ?>

</main>

<?php
    include '../includes/footer.php';
?>

<!-- Estilos Específicos del Visor -->
<style>
.visor-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
    flex-wrap: wrap;
    gap: 20px;
}
.visor-titulo {
    font-size: 1.8em;
    color: #002f55;
    margin: 0 0 5px 0;
}
.visor-meta {
    color: #666;
    font-size: 0.9em;
    margin: 0;
}
.visor-container {
    border: 1px solid #ddd;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    border-radius: 8px;
    overflow: hidden;
}
.btn-descargar-visor {
    background-color: #004a99;
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    margin-right: 10px;
}
.btn-volver-visor {
    color: #d9534f;
    text-decoration: none;
    font-weight: bold;
    font-size: 1.2em;
    padding: 5px 10px;
}
</style>