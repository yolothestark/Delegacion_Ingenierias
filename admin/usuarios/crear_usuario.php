<?php
    // header.php ya incluye la conexión a MongoDB, aunque aquí no la usemos directamente.
    include '../layouts/header.php';

    // Verificación de seguridad estricta
    $rol_actual = isset($_SESSION['user_rol']) ? trim($_SESSION['user_rol']) : '';

    // Usamos strcasecmp para comparar sin importar mayúsculas/minúsculas
    if (strcasecmp($rol_actual, 'SuperAdmin') !== 0) {
        // Si no es SuperAdmin, lo mandamos al inicio del panel
        header("Location: ../index.php");
        exit;
    }
?>

<h2>Registrar Nuevo Administrador</h2>

<form action="crear_usuario_proceso.php" method="POST" class="admin-form">
    
    <div class="input-group">
        <label for="username">Nombre de Usuario:</label>
        <input type="text" id="username" name="username" required autocomplete="off">
    </div>

    <div class="input-group">
        <label for="email">Correo Electrónico:</label>
        <input type="email" id="email" name="email" required autocomplete="off">
    </div>

    <div class="input-group">
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required autocomplete="new-password">
    </div>

    <div class="input-group">
        <label for="rol">Nivel de Permisos:</label>
        <select id="rol" name="rol" required>
            <option value="Admin">Administrador (Gestión de contenido)</option>
            <option value="SuperAdmin">Super Admin (Gestión total + Usuarios)</option>
        </select>
    </div>
    
    <button type="submit" class="btn-guardar">Registrar Usuario</button>
</form>

<?php
    include '../layouts/admin_footer.php';
?>