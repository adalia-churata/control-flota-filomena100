<?php
// views/layouts/layout.php
// Estructura base: carga header → vista → footer
// $viewName     : nombre de la vista a incluir
// $alertas_count: badge de alertas del topbar
?>
<?php require_once __DIR__ . '/header.php' ?>

<?php
// Incluir la vista correspondiente
$viewFile = __DIR__ . '/../' . $viewName . '.php';

if (file_exists($viewFile)) {
    require $viewFile;
} else {
    echo '<div class="empty-state" style="padding:60px 20px">';
    echo '<p style="font-size:16px;font-weight:600">Vista no encontrada: ' . htmlspecialchars($viewName) . '</p>';
    echo '</div>';
}
?>

<?php require_once __DIR__ . '/footer.php' ?>