<?php
/**
 * Partial: Notificaciones flash
 */
$flashes = [
    'success' => ['class' => 'alert-success', 'icon' => '✓'],
    'error'   => ['class' => 'alert-danger',  'icon' => '✕'],
    'warning' => ['class' => 'alert-warning', 'icon' => '⚠'],
    'info'    => ['class' => 'alert-info',    'icon' => 'ℹ'],
];
foreach ($flashes as $key => $conf) {
    $msg = \App\Core\Session::flash($key);
    if (!empty($msg) && is_string($msg)) {
        echo '<div class="alert ' . $conf['class'] . '" data-autodismiss="1" role="alert">';
        echo '<strong>' . $conf['icon'] . '</strong> ' . e($msg);
        echo '</div>';
    }
}
$errors = \App\Core\Session::flash('errors');
if (is_array($errors) && count($errors)) {
    echo '<div class="alert alert-danger" role="alert"><strong>Errores en el formulario:</strong><ul class="mb-0 mt-2" style="padding-left:1.2rem">';
    foreach ($errors as $field => $list) {
        if (is_array($list)) {
            foreach ($list as $m) {
                echo '<li>' . e($m) . '</li>';
            }
        } else {
            echo '<li>' . e($list) . '</li>';
        }
    }
    echo '</ul></div>';
}
