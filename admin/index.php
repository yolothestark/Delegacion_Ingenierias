<?php
    // 1. Incluimos el encabezado (que ya tiene la conexión $db de MongoDB)
    // El header carga session_start() y db_connect_mongo.php
    include 'layouts/header.php';

    // --- CONSULTAS PARA LAS ESTADÍSTICAS (MIGRADO A MONGODB) ---
    
    // 1. Total Noticias
    // Equivalente a: SELECT COUNT(*) FROM noticias
    $total_noticias = $db->noticias->countDocuments();

    // 2. Total Documentos
    // Equivalente a: SELECT COUNT(*) FROM documentos
    $total_docs = $db->documentos->countDocuments();

    // 3. Total Miembros del Directorio
    // Equivalente a: SELECT COUNT(*) FROM integrantes
    $total_miembros = $db->integrantes->countDocuments();

    // 4. Tickets de Soporte PENDIENTES (Importante para atención)
    // Equivalente a: SELECT COUNT(*) FROM soporte_tickets WHERE estado='Pendiente'
    $total_tickets = $db->soporte_tickets->countDocuments(['estado' => 'Pendiente']);
?>

<h2>Dashboard</h2>
<p>Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>. Aquí tienes un resumen de la actividad reciente.</p>

<div class="stats-grid">
    
    <div class="stat-card">
        <div class="stat-number"><?php echo $total_noticias; ?></div>
        <div class="stat-label">Noticias Publicadas</div>
        <a href="noticias/gestionar_noticias.php" class="stat-link">Gestionar &rarr;</a>
    </div>

    <div class="stat-card">
        <div class="stat-number"><?php echo $total_docs; ?></div>
        <div class="stat-label">Documentos Subidos</div>
        <a href="documentos/gestionar_documentos.php" class="stat-link">Gestionar &rarr;</a>
    </div>

    <div class="stat-card">
        <div class="stat-number"><?php echo $total_miembros; ?></div>
        <div class="stat-label">Miembros en Directorio</div>
        <a href="directorio/gestionar_directorio.php" class="stat-link">Gestionar &rarr;</a>
    </div>

    <div class="stat-card <?php echo ($total_tickets > 0) ? 'card-warning' : ''; ?>">
        <div class="stat-number"><?php echo $total_tickets; ?></div>
        <div class="stat-label">Tickets Pendientes</div>
        <a href="soporte/gestionar_soporte.php" class="stat-link">Atender Tickets &rarr;</a>
    </div>

</div>

<div style="margin-top: 40px; padding: 20px; background: #f9f9f9; border-radius: 8px;">
    <h3>⚡ Acciones Rápidas</h3>
    <ul style="margin-top: 10px; padding-left: 20px; line-height: 1.6;">
        <li>Para agregar contenido nuevo, ve a la sección correspondiente en el menú superior.</li>
        <li>Revisa periódicamente la sección de <strong>Soporte</strong> para atender reportes de usuarios.</li>
        <li>Asegúrate de que la información en el <strong>Directorio</strong> esté actualizada.</li>
    </ul>
</div>

<?php
    // 2. Incluimos el pie de página
    include 'layouts/admin_footer.php'; 
?>