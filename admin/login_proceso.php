<?php
// ¡¡IMPORTANTE!! Iniciar la sesión ANTES de cualquier otra cosa.
session_start();

// 1. Incluimos la conexión a MongoDB
// Ajusta la ruta si 'includes' está en otro nivel, pero basándonos en tu estructura previa:
include '../includes/db_connect_mongo.php'; 

// 2. Verificamos que los datos lleguen por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Usamos trim() para limpiar espacios accidentales al inicio/final
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        // 3. Buscamos al usuario en la colección
        // Equivalente a: SELECT * FROM usuarios WHERE username = ?
        $coleccionUsuarios = $db->usuarios;
        $user = $coleccionUsuarios->findOne(['username' => $username]);

        // 4. Verificamos si el usuario existe
        if ($user) {
            
            // 5. Verificamos la contraseña
            // password_verify funciona igual con hashes guardados en Mongo o MySQL
            if (password_verify($password, $user['password_hash'])) {
                
                // ¡ÉXITO!
                session_regenerate_id(true); // Seguridad extra contra fijación de sesión
                
                // --- ASIGNACIÓN DE VARIABLES DE SESIÓN ---
                $_SESSION['loggedin'] = true;
                
                // IMPORTANTE: MongoDB usa '_id' (ObjectId). 
                // Lo convertimos a string para guardarlo en sesión y usarlo fácilmente después.
                $_SESSION['user_id']  = (string)$user['_id'];
                
                $_SESSION['username'] = $user['username'];
                
                // Guardamos el ROL. Asegúrate de que en tu BD el campo se llame 'rol'.
                $_SESSION['user_rol'] = $user['rol']; 
                // -----------------------------------------

                // 6. Redirigimos al Dashboard
                header("Location: index.php");
                exit;
                
            } else {
                // Contraseña incorrecta
                $_SESSION['error_message'] = 'Contraseña incorrecta.';
                header("Location: login.php");
                exit;
            }
        } else {
            // Usuario no encontrado
            $_SESSION['error_message'] = 'Usuario no encontrado.';
            header("Location: login.php");
            exit;
        }

    } catch (Exception $e) {
        // Error de conexión o base de datos
        $_SESSION['error_message'] = 'Error del sistema. Intente más tarde.';
        header("Location: login.php");
        exit;
    }

} else {
    // Si intentan entrar directo sin POST
    header("Location: login.php");
    exit;
}
?>