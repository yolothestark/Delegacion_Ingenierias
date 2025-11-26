<?php
    // Incluye header (que conecta a MongoDB)
    include '../layouts/header.php';

    // --- SEGURIDAD EXTRA ---
    // Usamos trim() y strcasecmp para ser robustos con la comparación
    $rol_actual = isset($_SESSION['user_rol']) ? trim($_SESSION['user_rol']) : '';

    if (strcasecmp($rol_actual, 'SuperAdmin') !== 0) {
        echo "<script>alert('Acceso Denegado. Solo SuperAdmins.'); window.location.href='../index.php';</script>";
        exit;
    }
?>

<div class="header-listado">
    <h2>Gestión de Usuarios (Admins)</h2>
    <a href="crear_usuario.php" class="btn-crear">
        + Nuevo Usuario
    </a>
</div>

<?php if (isset($_GET['status'])): ?>
    <div style="margin-bottom: 15px; padding: 10px; border-radius: 5px; background-color: #f0f0f0; border-left: 5px solid #333;">
        <?php 
            if ($_GET['status'] == 'creado') echo "Usuario registrado correctamente.";
            if ($_GET['status'] == 'borrado') echo "Usuario eliminado correctamente.";
            if ($_GET['status'] == 'error') echo "Ocurrió un error en la operación.";
        ?>
    </div>
<?php endif; ?>

<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Fecha Registro</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
                // CONSULTA MONGODB
                // Ordenamos por fecha de creación ascendente (1) para imitar el ID autoincremental antiguo
                // O usa -1 si quieres ver los nuevos primero.
                $opciones = ['sort' => ['fecha_creacion' => 1]];
                
                $cursor = $db->usuarios->find([], $opciones);
                $lista_usuarios = $cursor->toArray();

                if (count($lista_usuarios) > 0) {
                    foreach($lista_usuarios as $row) {
                        
                        // 1. Obtener ID como string
                        $id_str = (string)$row['_id'];

                        // 2. Procesar Fecha (UTCDateTime)
                        // Buscamos 'fecha_creacion' (Mongo nuevo) o fallback a 'fecha_registro' (SQL migrado)
                        $fecha_formateada = "N/A";
                        
                        // Caso A: Es un objeto nativo de Mongo
                        if (isset($row['fecha_creacion']) && $row['fecha_creacion'] instanceof MongoDB\BSON\UTCDateTime) {
                            $fecha_formateada = $row['fecha_creacion']->toDateTime()->format('d/m/Y');
                        } 
                        // Caso B: Es un string (datos migrados tal cual)
                        elseif (isset($row['fecha_registro'])) {
                            $fecha_formateada = date('d/m/Y', strtotime($row['fecha_registro']));
                        }
            ?>
                        <tr>
                            <td title="<?php echo $id_str; ?>">
                                <?php echo substr($id_str, -6); ?>...
                            </td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <?php if($row['rol'] === 'SuperAdmin'): ?>
                                    <strong style="color: #d9534f;">★ SuperAdmin</strong>
                                <?php else: ?>
                                    <span style="color: #004a99;">Admin</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $fecha_formateada; ?></td>
                            <td class="acciones">
                                <?php if($id_str !== (string)$_SESSION['user_id']): ?>
                                    <a href="borrar_usuario.php?id=<?php echo $id_str; ?>" 
                                       class="btn-borrar" 
                                       onclick="return confirm('¿Eliminar acceso a este usuario?');">
                                       Borrar
                                    </a>
                                <?php else: ?>
                                    <span style="color: #ccc;">(Tú)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
            <?php
                    } // Fin foreach
                } else {
            ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding: 20px;">No hay usuarios registrados.</td>
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