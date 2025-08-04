<?php
// Fallback view for the Kanban board.
//
// In earlier iterations of this project the TaskController attempted to
// render a view named `kanban`. The updated implementation consolidates
// all task views into a single template at `tasks/index.php` which
// switches between Kanban, list and calendar views via the `$view`
// variable. To maintain backwards compatibility and avoid fatal
// "View kanban not found" errors, this file includes the new
// consolidated template. Variables such as `$project`, `$tasks`, `$view`
// and `$users` are passed through from the controller.

// If this file is rendered directly, ensure `$view` is set to
// "kanban" so the tasks/index view shows the Kanban board.
if (!isset($view)) {
    $view = 'kanban';
}

// Delegate to the unified tasks view. The relative path points up one
// directory into the tasks folder.
require __DIR__ . '/tasks/index.php';