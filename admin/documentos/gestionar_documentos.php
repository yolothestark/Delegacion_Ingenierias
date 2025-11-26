<?php
    // Sube 2 niveles para conexión MongoDB
    include '../../includes/db_connect_mongo.php';
    
    // Incluimos el header del panel
    include '../layouts/header.php'; 
?>

<div class="header-listado">
    <h2>Gestionar Documentos</h2>
    <a href="subir_documento.php" class="btn-crear">
        + Subir Nuevo Documento
    </a>
</div>

<!-- Bloque de Feedback (Mensajes de éxito/error) -->
<?php if (isset($_GET['status'])): ?>
    <?php
        // Definir estilo según si es error o éxito
        $es_error = strpos($_GET['status'], 'error') !== false;
        $estilo = $es_error 
            ? "background-color: #f8d7da; border-left: 5px solid #dc3545; color: #721c24;" 
            : "background-color: #d4edda; border-left: 5px solid #28a745; color: #155724;";
    ?>
    <div style="margin-bottom: 15px; padding: 15px; border-radius: 5px; <?php echo $estilo; ?>">
        <?php 
            switch($_GET['status']) {
                case 'subido': echo "✅ Documento subido correctamente."; break;
                case 'editado': echo "✅ Documento actualizado correctamente."; break;
                case 'borrado': echo "✅ Documento eliminado correctamente."; break;
                
                // Errores específicos
                case 'error_archivo': echo "❌ Error al subir el archivo. Probablemente excede el tamaño máximo permitido (2MB)."; break;
                case 'error_tipo': echo "❌ Error: El archivo no es un PDF válido."; break;
                case 'error_movida': echo "❌ Error: No se pudo guardar el archivo en la carpeta (Permisos o ruta)."; break;
                case 'error_db': echo "❌ Error al guardar en la base de datos."; break;
                case 'error_borrar': echo "❌ Error al intentar borrar el documento."; break;
                case 'error_excepcion': echo "❌ Ocurrió un error técnico en el servidor."; break;
                
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
                <th>Estado</th> <!-- Nueva columna para depuración -->
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
                
                try {
                    $cursor = $db->documentos->find([], $opciones);
                    $lista_docs = $cursor->toArray();

                    if (count($lista_docs) > 0) {
                        foreach($lista_docs as $doc) {
                            
                            // 1. Procesar ID
                            $id_str = (string)$doc['_id'];

                            // 2. Procesar Datos (Con protección ??)
                            $nombre_doc  = $doc['nombre_documento'] ?? 'Sin Nombre';
                            $nombre_tipo = $doc['tipo_documento']['nombre'] ?? 'N/A';
                            $version     = $doc['version'] ?? '1.0';
                            $ruta_archivo = $doc['ruta_archivo'] ?? '';

                            // 3. VERIFICACIÓN DE ARCHIVO FÍSICO (DEPURACIÓN)
                            $archivo_existe = false;
                            if (!empty($ruta_archivo)) {
                                // Construimos la ruta absoluta en el servidor
                                // dirname(__DIR__, 2) nos lleva a la raíz del proyecto
                                $ruta_absoluta = dirname(dirname(__DIR__)) . '/' . ltrim($ruta_archivo, '/');
                                $archivo_existe = file_exists($ruta_absoluta);
                            }

                            // Icono de estado
                            $icono_estado = $archivo_existe 
                                ? '<span style="font-size: 1.2em;" title="Archivo OK">📄</span>' 
                                : '<span style="font-size: 1.2em; cursor:help;" title="¡Alerta! El archivo PDF no se encuentra en el servidor (Posible reinicio de Render)">⚠️</span>';


                            // 4. Procesar Fecha
                            $fecha_formateada = "Fecha inválida";
                            if (isset($doc['fecha_subida'])) {
                                if ($doc['fecha_subida'] instanceof MongoDB\BSON\UTCDateTime) {
                                    $dt = $doc['fecha_subida']->toDateTime();
                                    $fecha_formateada = $dt->format('d/m/Y H:i');
                                } else {
                                    $fecha_formateada = $doc['fecha_subida'];
                                }
                            }
            ?>
                        <tr>
                            <!-- Mostramos ID corto visualmente -->
                            <td title="<?php echo $id_str; ?>">
                                <?php echo substr($id_str, -6); ?>...
                            </td>
                            <td style="text-align: center;">
                                <?php echo $icono_estado; ?>
                            </td>
                            <td><?php echo htmlspecialchars($nombre_doc); ?></td>
                            <td><?php echo htmlspecialchars($nombre_tipo); ?></td>
                            <td><?php echo htmlspecialchars($version); ?></td>
                            <td><?php echo $fecha_formateada; ?></td>
                            <td class="acciones">
                                <a href="editar_documento.php?id=<?php echo $id_str; ?>" class="btn-editar">Editar</a>
                                <a href="borrar_documento.php?id=<?php echo $id_str; ?>" 
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
                            <td colspan="7" style="text-align:center; padding: 20px;">No hay documentos para mostrar. Sube uno nuevo.</td>
                        </tr>
            <?php
                    } 
                } catch (Exception $e) {
                    echo "<tr><td colspan='7'>Error al cargar documentos: " . $e->getMessage() . "</td></tr>";
                }
            ?>
        </tbody>
    </table>
</div>

<?php
    include '../layouts/admin_footer.php';
?>