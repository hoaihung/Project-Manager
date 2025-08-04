<h1 style="margin-bottom:1rem;"><?php echo e(__('dashboard')); ?></h1>

<!-- Project filter navigation -->
<?php
    // Determine selected project filter
    $selectedProjectId = $projectFilter ?? 'all';
?>
<ul class="nav nav-pills mb-3">
    <li class="nav-item">
        <a class="nav-link <?php echo ($selectedProjectId === 'all') ? 'active' : ''; ?>" href="index.php?controller=dashboard&project=all">
            <?php echo e(__('all_projects')); ?>
        </a>
    </li>
    <?php foreach ($projects as $proj): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo ((string)$selectedProjectId === (string)$proj['id']) ? 'active' : ''; ?>" href="index.php?controller=dashboard&project=<?php echo e($proj['id']); ?>">
                <?php echo e($proj['name']); ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<!-- Overall task statistics -->
<?php if (isset($taskStats)): ?>
<div class="card">
    <h2 style="margin-bottom:0.5rem;"><?php echo e(__('task_statistics')); ?></h2>
    <div style="display:flex; flex-wrap:wrap; gap:1rem;">
        <div style="flex:1;">
            <strong><?php echo e(__('total')); ?>:</strong> <?php echo e($taskStats['total']); ?>
        </div>
        <div style="flex:1; color:var(--muted);">
            <span><?php echo e(__('todo')); ?>: <?php echo e($taskStats['todo']); ?></span>
        </div>
        <div style="flex:1; color:var(--muted);">
            <span><?php echo e(__('in_progress')); ?>: <?php echo e($taskStats['in_progress']); ?></span>
        </div>
        <div style="flex:1; color:var(--muted);">
            <span><?php echo e(__('bug_review')); ?>: <?php echo e($taskStats['bug_review']); ?></span>
        </div>
        <div style="flex:1; color:var(--muted);">
            <span><?php echo e(__('done')); ?>: <?php echo e($taskStats['done']); ?></span>
        </div>
        <div style="flex:1; color:var(--danger);">
            <span><?php echo e(__('overdue')); ?>: <?php echo e($taskStats['overdue']); ?></span>
        </div>
    </div>
    <!-- Charts showing distribution of tasks by status and priority -->
    <div style="margin-top:1rem; display:flex; flex-wrap:wrap; gap:1rem;">
        <div style="flex:1; min-width:280px;">
            <canvas id="statusChart" width="300" height="200"></canvas>
        </div>
        <div style="flex:1; min-width:280px;">
            <canvas id="priorityChart" width="300" height="200"></canvas>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Projects summary -->
<div class="card">
    <h2 style="margin-bottom:0.5rem;"><?php echo e(__('projects')); ?></h2>
    <table>
        <thead>
            <tr>
                <th><?php echo e(__('project_name')); ?></th>
                <th><?php echo e(__('status')); ?></th>
                <th><?php echo e(__('start_date')); ?></th>
                <th><?php echo e(__('end_date')); ?></th>
                <th><?php echo e(__('tasks')); ?></th>
                <th><?php echo e(__('actions')); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($projects as $project): ?>
            <tr>
                <td><?php echo e($project['name']); ?></td>
                <td><?php echo e($project['status']); ?></td>
                <td><?php echo e($project['start_date']); ?></td>
                <td><?php echo e($project['end_date']); ?></td>
                <td><?php echo e($project['task_count']); ?></td>
                <td>
                    <a class="btn btn-secondary" href="index.php?controller=task&project_id=<?php echo e($project['id']); ?>">
                        <?php echo e(__('tasks')); ?>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <a href="index.php?controller=project&action=create" class="btn btn-primary">
        <?php echo e(__('create_project')); ?>
    </a>
</div>

<?php if (isset($taskStats) && isset($priorityCounts)): ?>
<script>
// Prepare data for status chart (Todo, In Progress, Done, Overdue)
const statusData = {
    labels: ['Todo', 'In Progress', 'Bug/Review', 'Done', 'Overdue'],
    datasets: [{
        label: 'Tasks by Status',
        data: [<?php echo (int)$taskStats['todo']; ?>, <?php echo (int)$taskStats['in_progress']; ?>, <?php echo (int)$taskStats['bug_review']; ?>, <?php echo (int)$taskStats['done']; ?>, <?php echo (int)$taskStats['overdue']; ?>],
        backgroundColor: ['#e5e7eb', '#93c5fd', '#fde68a', '#a7f3d0', '#fecaca'],
        borderWidth: 1
    }]
};
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'bar',
    data: statusData,
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            title: { display: true, text: 'Tasks by Status' }
        },
        scales: { x: { beginAtZero: true }, y: { beginAtZero: true } }
    }
});

// Prepare data for priority chart (High, Normal, Low)
const priorityData = {
    labels: ['High', 'Normal', 'Low'],
    datasets: [{
        label: 'Tasks by Priority',
        data: [<?php echo (int)$priorityCounts['high']; ?>, <?php echo (int)$priorityCounts['normal']; ?>, <?php echo (int)$priorityCounts['low']; ?>],
        backgroundColor: ['#ef4444', '#3b82f6', '#fbbf24'],
        borderWidth: 1
    }]
};
const priorityCtx = document.getElementById('priorityChart').getContext('2d');
new Chart(priorityCtx, {
    type: 'doughnut',
    data: priorityData,
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            title: { display: true, text: 'Tasks by Priority' }
        }
    }
});
</script>
<?php endif; ?>

<!-- Upcoming tasks -->
<div class="card">
    <h2 style="margin-bottom:0.5rem;"><?php echo e(__('upcoming_tasks_7_days')); ?></h2>
    <?php if (empty($upcomingTasks)): ?>
        <p><?php echo e(__('no_upcoming_tasks')); ?></p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th><?php echo e(__('task_name')); ?></th>
                    <th><?php echo e(__('project_name')); ?></th>
                    <th><?php echo e(__('due_date')); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($upcomingTasks as $task): ?>
                <tr>
                    <td><?php echo e($task['name']); ?></td>
                    <td><?php echo e($task['project_name']); ?></td>
                    <td><?php echo e($task['due_date']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Upcoming projects -->
<div class="card">
    <h2 style="margin-bottom:0.5rem;"><?php echo e(__('upcoming_projects_7_days')); ?></h2>
    <?php if (empty($upcomingProjects)): ?>
        <p><?php echo e(__('no_upcoming_projects')); ?></p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th><?php echo e(__('project_name')); ?></th>
                    <th><?php echo e(__('end_date')); ?></th>
                    <th><?php echo e(__('tasks')); ?></th>
                    <th><?php echo e(__('status')); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($upcomingProjects as $project): ?>
                <tr>
                    <td><?php echo e($project['name']); ?></td>
                    <td><?php echo e($project['end_date']); ?></td>
                    <td><?php echo e($project['task_count']); ?></td>
                    <td><?php echo e($project['status']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Tasks due today and tomorrow -->
<div class="card">
    <h2 style="margin-bottom:0.5rem;"><?php echo e(__('today_and_tomorrow_tasks')); ?></h2>
    <div style="display:flex; flex-wrap:wrap; gap:1rem;">
        <div style="flex:1; min-width:220px;">
            <h4><?php echo e(__('today')); ?> (<?php echo date('d/m'); ?>)</h4>
            <h5 style="font-size:0.9rem; margin-top:0.5rem;"><?php echo e(__('my_tasks')); ?></h5>
            <?php if (empty($todayTasksMy)): ?>
                <p><?php echo e(__('no_tasks')); ?></p>
            <?php else: ?>
                <ul class="list-unstyled">
                    <?php foreach ($todayTasksMy as $t): ?>
                        <li class="mb-2">
                            <div class="p-2 border rounded" style="background-color:var(--surface);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($t['parent_id'])): ?>
                                            <i class="fa-solid fa-code-branch text-muted me-1" title="<?php echo e(__('subtask_of')); ?>"></i>
                                        <?php endif; ?>
                                        <div>
                                            <a href="index.php?controller=task&action=edit&id=<?php echo e($t['id']); ?>" class="fw-bold" style="color:var(--primary); text-decoration:none;">
                                                <?php echo e($t['name']); ?>
                                            </a>
                                            <span class="text-muted small"> (<?php echo e($t['project_name']); ?>)</span>
                                        </div>
                                    </div>
                                    <div>
                                        <?php
                                            $p = $t['priority'] ?? 'normal';
                                            $pClass = 'bg-secondary';
                                            $pIcon = 'fa-flag';
                                            if ($p === 'high') { $pClass = 'bg-danger'; $pIcon = 'fa-triangle-exclamation'; }
                                            elseif ($p === 'low') { $pClass = 'bg-info'; $pIcon = 'fa-arrow-down'; }
                                        ?>
                                        <span class="badge <?php echo $pClass; ?> text-white" title="<?php echo e(__('priority_label')); ?>">
                                            <i class="fa-solid <?php echo $pIcon; ?>"></i> <?php echo ucfirst($p); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-1 text-muted small">
                                    <?php if (!empty($t['due_date'])): ?>
                                        <?php echo e(__('due_date')); ?>: <?php echo e($t['due_date']); ?>
                                    <?php endif; ?>
                                    <?php if (!empty($t['parent_name'])): ?>
                                        <span class="ms-2"><i class="fa-solid fa-arrow-turn-up"></i> <?php echo e($t['parent_name']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <h5 style="font-size:0.9rem; margin-top:1rem;"><?php echo e(__('other_tasks')); ?></h5>
            <?php if (empty($todayTasksOthers)): ?>
                <p><?php echo e(__('no_tasks')); ?></p>
            <?php else: ?>
                <ul class="list-unstyled">
                    <?php foreach ($todayTasksOthers as $t): ?>
                        <li class="mb-2">
                            <div class="p-2 border rounded" style="background-color:var(--surface);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($t['parent_id'])): ?>
                                            <i class="fa-solid fa-code-branch text-muted me-1" title="<?php echo e(__('subtask_of')); ?>"></i>
                                        <?php endif; ?>
                                        <div>
                                            <a href="index.php?controller=task&action=edit&id=<?php echo e($t['id']); ?>" class="fw-bold" style="color:var(--primary); text-decoration:none;">
                                                <?php echo e($t['name']); ?>
                                            </a>
                                            <span class="text-muted small"> (<?php echo e($t['project_name']); ?>)</span>
                                        </div>
                                    </div>
                                    <div>
                                        <?php
                                            $p = $t['priority'] ?? 'normal';
                                            $pClass = 'bg-secondary';
                                            $pIcon = 'fa-flag';
                                            if ($p === 'high') { $pClass = 'bg-danger'; $pIcon = 'fa-triangle-exclamation'; }
                                            elseif ($p === 'low') { $pClass = 'bg-info'; $pIcon = 'fa-arrow-down'; }
                                        ?>
                                        <span class="badge <?php echo $pClass; ?> text-white" title="<?php echo e(__('priority_label')); ?>">
                                            <i class="fa-solid <?php echo $pIcon; ?>"></i> <?php echo ucfirst($p); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-1 text-muted small">
                                    <?php if (!empty($t['due_date'])): ?>
                                        <?php echo e(__('due_date')); ?>: <?php echo e($t['due_date']); ?>
                                    <?php endif; ?>
                                    <?php if (!empty($t['parent_name'])): ?>
                                        <span class="ms-2"><i class="fa-solid fa-arrow-turn-up"></i> <?php echo e($t['parent_name']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <div style="flex:1; min-width:220px;">
            <h4><?php echo e(__('tomorrow')); ?> (<?php echo date('d/m', strtotime('+1 day')); ?>)</h4>
            <h5 style="font-size:0.9rem; margin-top:0.5rem;"><?php echo e(__('my_tasks')); ?></h5>
            <?php if (empty($tomorrowTasksMy)): ?>
                <p><?php echo e(__('no_tasks')); ?></p>
            <?php else: ?>
                <ul class="list-unstyled">
                    <?php foreach ($tomorrowTasksMy as $t): ?>
                        <li class="mb-2">
                            <div class="p-2 border rounded" style="background-color:var(--surface);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($t['parent_id'])): ?>
                                            <i class="fa-solid fa-code-branch text-muted me-1" title="<?php echo e(__('subtask_of')); ?>"></i>
                                        <?php endif; ?>
                                        <div>
                                            <a href="index.php?controller=task&action=edit&id=<?php echo e($t['id']); ?>" class="fw-bold" style="color:var(--primary); text-decoration:none;">
                                                <?php echo e($t['name']); ?>
                                            </a>
                                            <span class="text-muted small"> (<?php echo e($t['project_name']); ?>)</span>
                                        </div>
                                    </div>
                                    <div>
                                        <?php
                                            $p = $t['priority'] ?? 'normal';
                                            $pClass = 'bg-secondary';
                                            $pIcon = 'fa-flag';
                                            if ($p === 'high') { $pClass = 'bg-danger'; $pIcon = 'fa-triangle-exclamation'; }
                                            elseif ($p === 'low') { $pClass = 'bg-info'; $pIcon = 'fa-arrow-down'; }
                                        ?>
                                        <span class="badge <?php echo $pClass; ?> text-white" title="<?php echo e(__('priority_label')); ?>">
                                            <i class="fa-solid <?php echo $pIcon; ?>"></i> <?php echo ucfirst($p); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-1 text-muted small">
                                    <?php if (!empty($t['due_date'])): ?>
                                        <?php echo e(__('due_date')); ?>: <?php echo e($t['due_date']); ?>
                                    <?php endif; ?>
                                    <?php if (!empty($t['parent_name'])): ?>
                                        <span class="ms-2"><i class="fa-solid fa-arrow-turn-up"></i> <?php echo e($t['parent_name']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <h5 style="font-size:0.9rem; margin-top:1rem;"><?php echo e(__('other_tasks')); ?></h5>
            <?php if (empty($tomorrowTasksOthers)): ?>
                <p><?php echo e(__('no_tasks')); ?></p>
            <?php else: ?>
                <ul class="list-unstyled">
                    <?php foreach ($tomorrowTasksOthers as $t): ?>
                        <li class="mb-2">
                            <div class="p-2 border rounded" style="background-color:var(--surface);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($t['parent_id'])): ?>
                                            <i class="fa-solid fa-code-branch text-muted me-1" title="<?php echo e(__('subtask_of')); ?>"></i>
                                        <?php endif; ?>
                                        <div>
                                            <a href="index.php?controller=task&action=edit&id=<?php echo e($t['id']); ?>" class="fw-bold" style="color:var(--primary); text-decoration:none;">
                                                <?php echo e($t['name']); ?>
                                            </a>
                                            <span class="text-muted small"> (<?php echo e($t['project_name']); ?>)</span>
                                        </div>
                                    </div>
                                    <div>
                                        <?php
                                            $p = $t['priority'] ?? 'normal';
                                            $pClass = 'bg-secondary';
                                            $pIcon = 'fa-flag';
                                            if ($p === 'high') { $pClass = 'bg-danger'; $pIcon = 'fa-triangle-exclamation'; }
                                            elseif ($p === 'low') { $pClass = 'bg-info'; $pIcon = 'fa-arrow-down'; }
                                        ?>
                                        <span class="badge <?php echo $pClass; ?> text-white" title="<?php echo e(__('priority_label')); ?>">
                                            <i class="fa-solid <?php echo $pIcon; ?>"></i> <?php echo ucfirst($p); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-1 text-muted small">
                                    <?php if (!empty($t['due_date'])): ?>
                                        <?php echo e(__('due_date')); ?>: <?php echo e($t['due_date']); ?>
                                    <?php endif; ?>
                                    <?php if (!empty($t['parent_name'])): ?>
                                        <span class="ms-2"><i class="fa-solid fa-arrow-turn-up"></i> <?php echo e($t['parent_name']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>