<?php
// Fallback view for the list view.
// If a controller accidentally calls render('list'), this file delegates to
// the unified tasks/index template and forces the list view mode.
if (!isset($view)) {
    $view = 'list';
}
require __DIR__ . '/tasks/index.php';