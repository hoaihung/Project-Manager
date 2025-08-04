<?php
// header.php
// This layout is included at the top of each page. It defines the document
// structure and navigation bar. Feel free to modify the markup or styling
// to suit your branding. The CSS is defined in public/assets/css/style.css.
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Manager</title>
    <!-- Bootstrap CSS for responsive UI and ready-made components -->
    <!-- Bootstrap CSS without integrity attribute for local development; add integrity on production for security -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
    <!-- Font Awesome CSS for modern icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom stylesheet overrides Bootstrap defaults and defines application-specific styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Alpine.js omitted in development to avoid script errors. If you wish to use Alpine, include it via CDN or minified file separately. -->
    <?php
    // Only include Chart.js on pages that need charts.  Charts are used on the
    // dashboard, workload report and Gantt views.  Loading the library on
    // other pages (e.g. notifications) slows down rendering and is wasted.
    $ctrl  = $_GET['controller'] ?? '';
    $action = $_GET['action'] ?? '';
    // Use a unique variable name for the view parameter to avoid clobbering
    // the $view variable used in the Controller::render() method.  If we
    // accidentally assign to $view here, it will override the view name
    // passed to render() and cause "View not found" errors.
    $viewParam  = $_GET['view'] ?? '';
    $shouldIncludeChart = false;
    // Dashboard uses status and priority charts
    if ($ctrl === '' || $ctrl === 'dashboard') {
        $shouldIncludeChart = true;
    }
    // Workload report uses bar charts
    if ($ctrl === 'report' && $action === 'workload') {
        $shouldIncludeChart = true;
    }
    // Gantt charts reside in the task controller when view=gantt
    if ($ctrl === 'task' && ($viewParam === 'gantt' || $action === 'gantt')) {
        $shouldIncludeChart = true;
    }
    // Include Chart.js only if necessary
    if ($shouldIncludeChart): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <?php endif; ?>
    <?php
    // Only include Mermaid when rendering flow diagrams. Flow diagrams are
    // rendered on the task flow view (controller=task with view or action = flow)
    // and via the legacy flow controller.  Loading Mermaid on every page slows
    // down rendering unnecessarily.
    $shouldIncludeMermaid = false;
    if (($ctrl === 'task' && ($viewParam === 'flow' || $action === 'flow')) || $ctrl === 'flow') {
        $shouldIncludeMermaid = true;
    }
    if ($shouldIncludeMermaid): ?>
    <!-- Include Mermaid for flow diagrams (MIT licensed) -->
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10.9.0/dist/mermaid.min.js"></script>
    <script>
        if (typeof mermaid !== 'undefined') {
            mermaid.initialize({ startOnLoad: true });
        }
    </script>
    <?php endif; ?>

    <?php
    // Dynamically set CSS variables based on admin settings. This loads
    // configured colours and other design parameters from the settings
    // table. These variables override the defaults defined in style.css.
    $settingModel = new \app\Model\Setting();
    $settings = $settingModel->getAll();
    $colors = [
        'color_primary' => $settings['color_primary'] ?? null,
        'color_secondary' => $settings['color_secondary'] ?? null,
        'color_success' => $settings['color_success'] ?? null,
        'color_warning' => $settings['color_warning'] ?? null,
        'color_danger' => $settings['color_danger'] ?? null,
    ];
    ?>
    <style>
        :root {
            <?php foreach ($colors as $key => $val): if ($val): ?>
            --<?php echo substr($key, 6); ?>: <?php echo htmlspecialchars($val); ?>;
            <?php endif; endforeach; ?>
        }
    </style>
</head>
<body>
    <?php
    // Language switch: if a lang query parameter is provided, store it in session
    if (isset($_GET['lang'])) {
        $langParam = $_GET['lang'];
        if (in_array($langParam, ['vi', 'en'])) {
            $_SESSION['locale'] = $langParam;
        }
    }
    ?>
    <header class="navbar">
        <div class="container nav-container">
            <div class="nav-left">
                <!-- Sidebar toggle button: shows/hides the left sidebar -->
                <button id="sidebarToggle" class="btn btn-outline-secondary btn-sm" type="button" style="margin-right:0.5rem;">☰</button>
                <a href="index.php" class="brand">Project Manager</a>
            </div>
            <?php if (!empty($_SESSION['user_id'])): ?>
            <nav class="nav-right">
                <a href="index.php"><?php echo e(__('dashboard')); ?></a>
                <a href="index.php?controller=project"><?php echo e(__('projects')); ?></a>
                <a href="index.php?controller=profile"><?php echo e(__('profile')); ?></a>
                <a href="index.php?controller=profile&action=notifications"><?php echo e(__('notifications')); ?></a>
                <?php
                // Determine current user
                $userModel = new \app\Model\User();
                $currentUser = $userModel->findById($_SESSION['user_id']);
                $isAdmin = $currentUser && $currentUser['role_name'] === 'admin';
                ?>
                <?php if ($isAdmin): ?>
                    <!-- Consolidate admin links into a dropdown to reduce clutter -->
                    <div class="dropdown d-inline-block">
                        <a class="btn btn-link dropdown-toggle p-0" href="#" role="button" id="adminTools" data-bs-toggle="dropdown" aria-expanded="false" style="color:var(--link-color);">
                            <?php echo e(__('admin_tools') ?? 'Admin'); ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="adminTools">
                            <li><a class="dropdown-item" href="index.php?controller=user"><?php echo e(__('users')); ?></a></li>
                            <li><a class="dropdown-item" href="index.php?controller=log"><?php echo e(__('log')); ?></a></li>
                            <li><a class="dropdown-item" href="index.php?controller=task&action=trash"><?php echo e(__('trash')); ?></a></li>
                            <li><a class="dropdown-item" href="index.php?controller=tag"><?php echo e(__('tags')); ?></a></li>
                            <li><a class="dropdown-item" href="index.php?controller=report&action=workload"><?php echo e(__('reports')); ?></a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Non‑admin users still need access to tags and reports -->
                    <a href="index.php?controller=tag"><?php echo e(__('tags')); ?></a>
                    <a href="index.php?controller=report&action=workload"><?php echo e(__('reports')); ?></a>
                <?php endif; ?>
                <a href="index.php?controller=auth&action=logout"><?php echo e(__('logout')); ?></a>

                <!-- Language switcher -->
                <div class="dropdown d-inline-block" style="margin-left:1rem;">
                    <?php $currentLocale = $_SESSION['locale'] ?? 'vi'; ?>
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="langDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo strtoupper($currentLocale); ?>
                    </button>
                    <?php
                        // Build language switch links preserving the current URI and all query parameters except 'lang'.
                        // We parse the current request URI to extract query parameters. Using $_SERVER['REQUEST_URI'] ensures
                        // we maintain the same controller/view when switching languages.  If there are no query params,
                        // we default to index.php with the lang parameter only.
                        $currentUri = $_SERVER['REQUEST_URI'] ?? 'index.php';
                        $parsed = parse_url($currentUri);
                        $query = [];
                        if (!empty($parsed['query'])) {
                            parse_str($parsed['query'], $query);
                        }
                        unset($query['lang']);
                        $paramsVi = $query;
                        $paramsVi['lang'] = 'vi';
                        $paramsEn = $query;
                        $paramsEn['lang'] = 'en';
                        $path = $parsed['path'] ?? 'index.php';
                        $hrefVi = $path . '?' . http_build_query($paramsVi);
                        $hrefEn = $path . '?' . http_build_query($paramsEn);
                    ?>
                    <ul class="dropdown-menu" aria-labelledby="langDropdown">
                        <li><a class="dropdown-item" href="<?php echo e($hrefVi); ?>"><?php echo __('vietnamese'); ?></a></li>
                        <li><a class="dropdown-item" href="<?php echo e($hrefEn); ?>"><?php echo __('english'); ?></a></li>
                    </ul>
                </div>
            </nav>
            <?php endif; ?>
        </div>
    </header>
    <!-- Begin application layout: sidebar + main content -->
    <div class="app-container" style="display:flex;">
        <?php if (!empty($_SESSION['user_id'])): ?>
        <!-- Sidebar for quick navigation -->
        <?php
        // Load all projects for sidebar navigation
        try {
            $projModelForSidebar = new \app\Model\Project();
            $sidebarProjects = $projModelForSidebar->all();
        } catch (\Throwable $e) {
            $sidebarProjects = [];
        }
        ?>
        <aside class="sidebar" style="width:220px; background-color: var(--surface); border-right:1px solid var(--border); padding:1rem 0.5rem;">
            <div class="sidebar-heading" style="font-weight:600; margin-bottom:0.5rem; padding-left:0.75rem;">
                <?php echo __('navigation') ?? 'Navigation'; ?>
            </div>
            <nav>
                <a href="index.php" class="sidebar-link" style="display:block; padding:0.5rem 0.75rem; border-radius:0.375rem; margin-bottom:0.25rem; color:var(--text);">
                    <?php echo __('dashboard'); ?>
                </a>
                <a href="index.php?controller=project" class="sidebar-link" style="display:block; padding:0.5rem 0.75rem; border-radius:0.375rem; margin-bottom:0.5rem; color:var(--text);">
                    <?php echo __('projects'); ?>
                </a>
                <?php if (!empty($sidebarProjects)): ?>
                    <div class="sidebar-heading" style="font-weight:600; margin-top:0.5rem; margin-bottom:0.25rem; padding-left:0.75rem; font-size:0.9rem;">
                        <?php echo __('my_projects') ?? 'My Projects'; ?>
                    </div>
                    <?php foreach ($sidebarProjects as $sp): ?>
                        <a href="index.php?controller=task&project_id=<?php echo e($sp['id']); ?>&view=kanban" class="sidebar-link" style="display:block; padding:0.4rem 0.75rem; border-radius:0.375rem; margin-bottom:0.15rem; font-size:0.8rem; color:var(--text);">
                            <?php echo e($sp['name']); ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </nav>
        </aside>
        <main class="main-container" style="flex:1; padding-left:1rem;">
        <?php else: ?>
        <main class="main-container">
        <?php endif; ?>