<?php
    // 1. Conexión a MongoDB
    include '../../includes/db_connect_mongo.php';
    // 2. Header del Admin
    include '../layouts/header.php'; 
?>

<div class="header-listado">
    <h2>Buzón de Mensajes (Contacto)</h2>
</div>

<!-- Bloque de Feedback -->
<?php if (isset($_GET['status'])): ?>
    <div style="margin-bottom: 15px; padding: 10px; border-radius: 5px; background-color: #f0f0f0; border-left: 5px solid #333;">
        <?php 
            if ($_GET['status'] == 'borrado') echo "✅ Mensaje eliminado correctamente.";
            if ($_GET['status'] == 'error') echo "❌ Error al eliminar el mensaje.";
        ?>
    </div>
<?php endif; ?>

<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Mensaje</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
                // CONSULTA MONGODB
                // Ordenar por fecha_envio descendente (lo más nuevo arriba)
                $opciones = ['sort' => ['fecha_envio' => -1]];
                
                try {
                    $cursor = $db->mensajes_contacto->find([], $opciones);
                    $mensajes = $cursor->toArray();

                    if (count($mensajes) > 0) {
                        foreach($mensajes as $msg) {
                            
                            $id_str = (string)$msg['_id'];
                            $nombre_completo = htmlspecialchars($msg['nombre'] . ' ' . $msg['apellido']);
                            $email = htmlspecialchars($msg['email']);
                            // Cortamos el mensaje si es muy largo
                            $contenido = htmlspecialchars($msg['contenido']);
                            $resumen = (strlen($contenido) > 80) ? substr($contenido, 0, 80) . '...' : $contenido;

                            // Formato de fecha
                            $fecha_texto = "N/A";
                            if (isset($msg['fecha_envio']) && $msg['fecha_envio'] instanceof MongoDB\BSON\UTCDateTime) {
                                $dt = $msg['fecha_envio']->toDateTime();
                                // Ajuste horario si es necesario
                                // $dt->setTimezone(new DateTimeZone('America/Mexico_City'));
                                $fecha_texto = $dt->format('d/m/Y H:i');
                            }
            ?>
                        <tr>
                            <td style="white-space: nowrap;"><?php echo $fecha_texto; ?></td>
                            <td><strong><?php echo $nombre_completo; ?></strong></td>
                            <td>
                                <a href="mailto:<?php echo $email; ?>" style="color: #004a99; text-decoration: none;">
                                    <?php echo $email; ?>
                                </a>
                            </td>
                            <td title="<?php echo $contenido; ?>"><?php echo $resumen; ?></td>
                            <td class="acciones">
                                <!-- Botón para Borrar -->
                                <a href="borrar_mensaje.php?id=<?php echo $id_str; ?>" 
                                   class="btn-borrar" 
                                   onclick="return confirm('¿Estás seguro de borrar este mensaje?');">
                                   Borrar
                                </a>
                            </td>
                        </tr>
            <?php
                        } // Fin foreach
                    } else {
            ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding: 20px;">No hay mensajes nuevos.</td>
                        </tr>
            <?php
                    }
                } catch (Exception $e) {
                    echo "<tr><td colspan='5'>Error al cargar mensajes.</td></tr>";
                } 
            ?>
        </tbody>
    </table>
</div>

<?php
    include '../layouts/admin_footer.php';
?>