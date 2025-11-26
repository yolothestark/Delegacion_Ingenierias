<?php
    // Sube 2 niveles para conexión MongoDB
    include '../../includes/db_connect_mongo.php';
    include '../layouts/header.php'; 
?>

<div class="header-listado">
    <h2>Gestionar Documentos</h2>
    <a href="subir_documento.php" class="btn-crear">
        + Subir Nuevo Documento
    </a>
</div>

<?php if (isset($_GET['status'])): ?>
    <div style="margin-bottom: 15px; padding: 10px; border-radius: 5px; background-color: #f0f0f0; border-left: 5px solid #333;">
        <?php 
            switch($_GET['status']) {
                case 'subido': echo "Documento subido correctamente."; break;
                case 'editado': echo "Documento actualizado correctamente."; break;
                case 'borrado': echo "Documento eliminado correctamente."; break;
                case 'error_borrar': echo "Error al intentar borrar el documento."; break;
                default: echo "Operación realizada.";
            }
        ?>
    </div>
<?php endif; ?>

<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre del Documento</th>
                <th>Tipo</th>
                <th>Versión</th>
                <th>Fecha de Subida</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
                // DEFINICIÓN DE CONSULTA
                // Ordenar por fecha_subida DESC (lo más nuevo primero) -> -1
                $opciones = ['sort' => ['fecha_subida' => -1]];
                
                $cursor = $db->documentos->find([], $opciones);
                $lista_docs = $cursor->toArray();

                if (count($lista_docs) > 0) {
                    foreach($lista_docs as $doc) {
                        
                        // 1. Procesar ID
                        $id_str = (string)$doc['_id'];

                        // 2. Procesar Datos Embebidos (Tipo)
                        // Buscamos en ['tipo_documento']['nombre'], si no existe ponemos N/A
                        $nombre_tipo = $doc['tipo_documento']['nombre'] ?? 'N/A';

                        // 3. Procesar Fecha
                        // MongoDB devuelve un objeto UTCDateTime, hay que convertirlo a DateTime de PHP
                        $fecha_formateada = "Fecha inválida";
                        if (isset($doc['fecha_subida'])) {
                            if ($doc['fecha_subida'] instanceof MongoDB\BSON\UTCDateTime) {
                                $dt = $doc['fecha_subida']->toDateTime();
                                // Ajustar a zona horaria local si es necesario, ej: America/Mexico_City
                                // $dt->setTimezone(new DateTimeZone('America/Mexico_City'));
                                $fecha_formateada = $dt->format('d/m/Y H:i');
                            } else {
                                // Si por error se guardó como string
                                $fecha_formateada = $doc['fecha_subida'];
                            }
                        }
            ?>
                        <tr>
                            <td title="<?php echo $id_str; ?>">
                                <?php echo substr($id_str, -6); ?>...
                            </td>
                            <td><?php echo htmlspecialchars($doc['nombre_documento']); ?></td>
                            <td><?php echo htmlspecialchars($nombre_tipo); ?></td>
                            <td><?php echo htmlspecialchars($doc['version'] ?? 'N/A'); ?></td>
                            <td><?php echo $fecha_formateada; ?></td>
                            <td class="acciones">
                                <a href="editar_documento.php?id=<?php echo $id_str; ?>" class="btn-editar">Editar</a>
                                <a href="borrar_documentos.php?id=<?php echo $id_str; ?>" 
                                   class="btn-borrar" 
                                   onclick="return confirm('¿Estás seguro de que quieres borrar este documento?');">
                                   Borrar
                                </a>
                            </td>
                        </tr>
            <?php
                    } // Fin del foreach
                } else {
            ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 20px;">No hay documentos para mostrar. Sube uno nuevo.</td>
                    </tr>
            <?php
                } 
            ?>
        </tbody>
    </table>
</div>

<?php
    include '../layouts/admin_footer.php';
?>