<?php
session_start();

// Sube 2 niveles y baja a 'includes' para conexión MongoDB
include '../../includes/db_connect_mongo.php';
// Sube 1 nivel
include '../auth_check.php';

// Seguridad Extra: Solo SuperAdmin pasa
// Usamos trim() para asegurar que no haya espacios invisibles que rompan la lógica
$rol_actual = isset($_SESSION['user_rol']) ? trim($_SESSION['user_rol']) : '';

if (strcasecmp($rol_actual, 'SuperAdmin') !== 0) {
    die("Acceso Denegado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recoger y limpiar datos
    // MongoDB no necesita real_escape_string, pero sí es bueno hacer trim()
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $rol      = $_POST['rol'];

    try {
        $coleccionUsuarios = $db->usuarios;

        // 2. Verificar si el usuario ya existe (Username O Email)
        // Equivalente a: WHERE username = '...' OR email = '...'
        $usuarioExistente = $coleccionUsuarios->findOne([
            '$or' => [
                ['username' => $username],
                ['email'    => $email]
            ]
        ]);
        
        if ($usuarioExistente) {
            echo "<script>alert('Error: El usuario o correo ya existen.'); window.location.href='crear_usuario.php';</script>";
            exit;
        }

        // 3. Encriptar contraseña (Igual que antes, PHP estándar)
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // 4. Construir documento e Insertar
        $nuevoUsuario = [
            'username'       => $username,
            'email'          => $email,
            'password_hash'  => $password_hash,
            'rol'            => $rol,
            'fecha_creacion' => new MongoDB\BSON\UTCDateTime() // Timestamp automático
        ];

        $resultado = $coleccionUsuarios->insertOne($nuevoUsuario);
        
        if ($resultado->getInsertedCount() > 0) {
            header("Location: gestionar_usuarios.php?status=creado");
        } else {
            // Error raro de escritura
            header("Location: gestionar_usuarios.php?status=error");
        }

    } catch (Exception $e) {
        // Manejo de errores de conexión
        // error_log($e->getMessage());
        header("Location: gestionar_usuarios.php?status=error_excepcion");
    }
    
    exit;
} else {
    // Si intentan entrar directo
    header("Location: crear_usuario.php");
    exit;
}
?>