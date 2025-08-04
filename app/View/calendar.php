<?php
// Fallback view for the calendar view.
// If a controller accidentally calls render('calendar'), delegate to the
// unified tasks/index view and set the view mode accordingly.
if (!isset($view)) {
    $view = 'calendar';
}
require __DIR__ . '/tasks/index.php';