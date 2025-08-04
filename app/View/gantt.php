<?php
// This file proxies to the tasks/gantt.php view. It is provided so that
// controllers that call render('gantt') or render('flow') directly will
// still find a valid view. The actual implementation lives in
// app/View/tasks/gantt.php.
include __DIR__ . '/tasks/gantt.php';