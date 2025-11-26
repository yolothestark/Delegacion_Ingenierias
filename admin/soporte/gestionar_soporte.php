<?php
    // Sube 1 nivel a 'admin/layouts/' y carga la conexión MongoDB ($db)
    include '../layouts/header.php'; 
?>

<div class="header-listado">
    <h2>Mesa de Ayuda - Tickets de Soporte</h2>
</div>

<?php if (isset($_GET['status'])): ?>
    <div style="margin-bottom: 15px; padding: 10px; border-radius: 5px; background-color: #f0f0f0; border-left: 5px solid #333;">
        <?php 
            if ($_GET['status'] == 'actualizado') echo "Estado del ticket actualizado.";
            if ($_GET['status'] == 'error') echo "Hubo un error al actualizar.";
        ?>
    </div>
<?php endif; ?>

<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Problema</th>
                <th>Descripción</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php
                // --- LÓGICA DE ORDENAMIENTO PERSONALIZADO EN MONGODB ---
                // Queremos el orden: Pendiente (1) -> En Proceso (2) -> Resuelto (3)
                // Usamos 'aggregate' en lugar de 'find' para crear un campo temporal de ordenamiento.
                
                $pipeline = [
                    [
                        '$addFields' => [
                            'peso_estado' => [
                                '$switch' => [
                                    'branches' => [
                                        ['case' => ['$eq' => ['$estado', 'Pendiente']], 'then' => 1],
                                        ['case' => ['$eq' => ['$estado', 'En Proceso']], 'then' => 2],
                                        ['case' => ['$eq' => ['$estado', 'Resuelto']], 'then' => 3]
                                    ],
                                    'default' => 4 // Cualquier otro estado va al final
                                ]
                            ]
                        ]
                    ],
                    // Ordenamos primero por el peso (prioridad) y luego por fecha descendente
                    ['$sort' => ['peso_estado' => 1, 'fecha_creacion' => -1]]
                ];

                // Ejecutamos la agregación
                $cursor = $db->soporte_tickets->aggregate($pipeline);
                $tickets = $cursor->toArray();

                if (count($tickets) > 0) {
                    foreach($tickets as $row) {
                        
                        // 1. Procesar ID
                        $id_str = (string)$row['_id'];

                        // 2. Definir color del estado
                        $estado_color = "background-color: #999;";
                        if(isset($row['estado'])) {
                            if($row['estado'] == 'Pendiente') $estado_color = "background-color: #d9534f;"; // Rojo
                            if($row['estado'] == 'En Proceso') $estado_color = "background-color: #f0ad4e;"; // Naranja
                            if($row['estado'] == 'Resuelto') $estado_color = "background-color: #5cb85c;"; // Verde
                        }

                        // 3. Procesar Fecha
                        $fecha_formateada = "N/A";
                        if (isset($row['fecha_creacion'])) {
                            if ($row['fecha_creacion'] instanceof MongoDB\BSON\UTCDateTime) {
                                $fecha_formateada = $row['fecha_creacion']->toDateTime()->format('d/m/y H:i');
                            } else {
                                $fecha_formateada = $row['fecha_creacion']; // Fallback string
                            }
                        }
            ?>
                        <tr>
                            <td title="<?php echo $id_str; ?>">#<?php echo substr($id_str, -6); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['nombre_usuario']); ?></strong><br>
                                <small><?php echo htmlspecialchars($row['email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($row['categoria_problema']); ?></td>
                            <td>
                                <div style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?php echo htmlspecialchars($row['descripcion']); ?>
                                </div>
                            </td>
                            <td><?php echo $fecha_formateada; ?></td>
                            <td>
                                <span style="color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; <?php echo $estado_color; ?>">
                                    <?php echo htmlspecialchars($row['estado']); ?>
                                </span>
                            </td>
                            <td>
                                <form action="cambiar_estado.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="id_ticket" value="<?php echo $id_str; ?>">
                                    
                                    <select name="nuevo_estado" onchange="this.form.submit()" style="padding: 5px; border-radius: 4px; border: 1px solid #ccc;">
                                        <option value="" disabled selected>Cambiar...</option>
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="En Proceso">En Proceso</option>
                                        <option value="Resuelto">Resuelto</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
            <?php
                    } 
                } else {
            ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding: 20px;">No hay tickets de soporte registrados.</td>
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