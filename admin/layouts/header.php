<?php
// 1. Iniciar sesión
session_start();

// Configuración de rutas INTELIGENTE
// Detecta si es localhost (tu PC) o la nube (Render)
$es_local = ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1');
$raiz = $es_local ? "/Delegacion_Ingenierias" : ""; // Sin carpeta en la nube

$ruta_base_publica = $raiz;       // Para ir al inicio, css, imagenes
$ruta_base_admin   = $raiz . "/admin"; // Para enlaces internos del admin

// Incluir archivos necesarios
// Usamos __DIR__ para subir niveles de forma segura
include_once __DIR__ . '/../../includes/db_connect_mongo.php'; 
include_once __DIR__ . '/../auth_check.php';

// Variables para el menú activo
$pagina_actual = basename($_SERVER['PHP_SELF']);
$carpeta_actual = basename(dirname($_SERVER['PHP_SELF']));

$user_rol = isset($_SESSION['user_rol']) ? trim($_SESSION['user_rol']) : 'Viewer';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    
    <!-- CORRECCIÓN CSS: Usamos ruta absoluta dinámica -->
    <link rel="stylesheet" href="<?php echo $ruta_base_publica; ?>/css/admin.css"> 
</head>
<body>

<div class="admin-panel">
    <header class="admin-header">
        <h1>Panel de Administración</h1>
        <div>
            <span style="font-size: 0.8em; margin-right: 10px; background: #eee; padding: 3px 8px; border-radius: 4px;">
                Rol: <?php echo htmlspecialchars($user_rol); ?>
            </span>
            
            Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuario'); ?></strong>
            <a href="<?php echo $ruta_base_admin; ?>/logout.php" class="btn-logout">Salir</a>
        </div>
    </header>

    <nav class="admin-nav">
        <a href="<?php echo $ruta_base_admin; ?>/index.php" 
           class="<?php echo ($pagina_actual == 'index.php' && $carpeta_actual == 'admin') ? 'active' : ''; ?>">
           Inicio
        </a>
        <a href="<?php echo $ruta_base_admin; ?>/noticias/gestionar_noticias.php" 
           class="<?php echo ($carpeta_actual == 'noticias') ? 'active' : ''; ?>">
           Noticias
        </a>
        <a href="<?php echo $ruta_base_admin; ?>/documentos/gestionar_documentos.php" 
           class="<?php echo ($carpeta_actual == 'documentos') ? 'active' : ''; ?>">
           Documentos
        </a>
        <a href="<?php echo $ruta_base_admin; ?>/contacto/gestionar_dmensajes.php" 
           class="<?php echo ($carpeta_actual == 'contacto') ? 'active' : ''; ?>">
           Mensaje
        </a>
        <a href="<?php echo $ruta_base_admin; ?>/soporte/gestionar_soporte.php" 
           class="<?php echo ($carpeta_actual == 'soporte') ? 'active' : ''; ?>">
           Soporte
        </a>
        <a href="<?php echo $ruta_base_admin; ?>/directorio/gestionar_directorio.php" 
           class="<?php echo ($carpeta_actual == 'directorio') ? 'active' : ''; ?>">
           Directorio
        </a>

        <?php if (strcasecmp($user_rol, 'SuperAdmin') === 0): ?>
            <a href="<?php echo $ruta_base_admin; ?>/usuarios/gestionar_usuarios.php" 
               class="<?php echo ($carpeta_actual == 'usuarios') ? 'active' : ''; ?>"
               style="background-color: #444; color: #fff;">
               ★ Usuarios
            </a>
        <?php endif; ?>

    </nav>
    
    <div class="admin-content">