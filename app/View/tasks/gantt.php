<!-- Navigation bar: reuse view navigation from parent -->
<div style="margin-bottom:1rem; display:flex; flex-wrap:wrap; gap:0.25rem;">
    <a href="index.php?controller=task&project_id=<?php echo e($project['id']); ?>&view=kanban" class="btn <?php echo $view==='kanban' ? 'btn-secondary' : 'btn-primary'; ?>"><?php echo __('board'); ?></a>
    <a href="index.php?controller=task&project_id=<?php echo e($project['id']); ?>&view=list" class="btn <?php echo $view==='list' ? 'btn-secondary' : 'btn-primary'; ?>"><?php echo __('list'); ?></a>
    <a href="index.php?controller=task&project_id=<?php echo e($project['id']); ?>&view=calendar" class="btn <?php echo $view==='calendar' ? 'btn-secondary' : 'btn-primary'; ?>"><?php echo __('calendar'); ?></a>
    <a href="index.php?controller=task&project_id=<?php echo e($project['id']); ?>&view=gantt" class="btn <?php echo $view==='gantt' ? 'btn-secondary' : 'btn-primary'; ?>"><?php echo __('gantt'); ?></a>
    <!-- Flow view removed from navigation -->
</div>

<!-- Filter form similar to other views -->
<div class="task-filter" style="margin-bottom:1rem;">
    <form method="get" class="row g-2 align-items-end">
        <input type="hidden" name="controller" value="task">
        <input type="hidden" name="project_id" value="<?php echo e($project['id']); ?>">
        <input type="hidden" name="view" value="gantt">
        <div class="col-sm-2">
            <label for="tag_filter" class="form-label small text-muted mb-0"><?php echo __('tag_label'); ?></label>
            <input type="text" id="tag_filter" name="tag_filter" class="form-control form-control-sm" value="<?php echo e($_GET['tag_filter'] ?? ''); ?>" placeholder="<?php echo __('tags_placeholder'); ?>">
        </div>
        <div class="col-sm-2">
            <label for="user_filter" class="form-label small text-muted mb-0"><?php echo __('user_label'); ?></label>
            <select id="user_filter" name="user_filter" class="form-select form-select-sm">
                <option value=""><?php echo __('any'); ?></option>
                <?php foreach ($users as $u): ?>
                    <option value="<?php echo e($u['id']); ?>" <?php echo (isset($_GET['user_filter']) && $_GET['user_filter'] == $u['id']) ? 'selected' : ''; ?>><?php echo e($u['full_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-2">
            <label for="priority_filter" class="form-label small text-muted mb-0"><?php echo __('priority_label'); ?></label>
            <select id="priority_filter" name="priority_filter" class="form-select form-select-sm">
                <option value=""><?php echo __('any'); ?></option>
                <option value="high" <?php echo (isset($_GET['priority_filter']) && $_GET['priority_filter'] === 'high') ? 'selected' : ''; ?>><?php echo __('high'); ?></option>
                <option value="normal" <?php echo (isset($_GET['priority_filter']) && $_GET['priority_filter'] === 'normal') ? 'selected' : ''; ?>><?php echo __('normal'); ?></option>
                <option value="low" <?php echo (isset($_GET['priority_filter']) && $_GET['priority_filter'] === 'low') ? 'selected' : ''; ?>><?php echo __('low'); ?></option>
            </select>
        </div>
        <div class="col-sm-2">
            <label for="start_filter" class="form-label small text-muted mb-0"><?php echo __('from'); ?></label>
            <input type="date" id="start_filter" name="start_filter" class="form-control form-control-sm" value="<?php echo e($_GET['start_filter'] ?? ''); ?>">
        </div>
        <div class="col-sm-2">
            <label for="end_filter" class="form-label small text-muted mb-0"><?php echo __('to'); ?></label>
            <input type="date" id="end_filter" name="end_filter" class="form-control form-control-sm" value="<?php echo e($_GET['end_filter'] ?? ''); ?>">
        </div>
        <div class="col-sm-auto d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm"><?php echo __('apply'); ?></button>
            <a href="index.php?controller=task&project_id=<?php echo e($project['id']); ?>&view=gantt" class="btn btn-secondary btn-sm"><?php echo __('clear'); ?></a>
        </div>
    </form>
</div>

<h2 style="margin-bottom:1rem;"><?php echo __('gantt_view'); ?> - <?php echo __('project'); ?>: <?php echo e($project['name']); ?></h2>
<div class="card">
    <!-- Canvas for the Gantt-style bar chart -->
    <canvas id="ganttChart" width="800" height="400"></canvas>
    <?php
    // Flatten tasks and subtasks for display and build chart data
    $flat = [];
    foreach ($tasks as $status => $items) {
        foreach ($items as $task) {
            $flat[] = $task;
            if (!empty($task['subtasks'])) {
                foreach ($task['subtasks'] as $sub) {
                    $flat[] = $sub;
                }
            }
        }
    }
    // Determine earliest start date across tasks for calculating offsets
    $earliest = null;
    foreach ($flat as $t) {
        if (!empty($t['start_date'])) {
            $date = new DateTime($t['start_date']);
            if (!$earliest || $date < $earliest) {
                $earliest = $date;
            }
        }
    }
    // Build JS arrays for labels, offsets and durations
    $labels = [];
    $offsets = [];
    $durations = [];
    $backgroundColors = [];
    foreach ($flat as $t) {
        // Include assignees in label for clarity
        $label = $t['name'];
        if (!empty($t['assignees'])) {
            $label .= ' (' . $t['assignees'] . ')';
        }
        $labels[] = addslashes($label);
        // Compute offset from earliest start to this task's start (days)
        if (!empty($t['start_date']) && $earliest) {
            $s = new DateTime($t['start_date']);
            $offset = $earliest->diff($s)->days;
        } else {
            $offset = 0;
        }
        // Duration in days
        if (!empty($t['start_date']) && !empty($t['due_date'])) {
            $s = new DateTime($t['start_date']);
            $d = new DateTime($t['due_date']);
            $interval = $s->diff($d);
            $duration = $interval->days;
        } else {
            $duration = 1;
        }
        $offsets[] = $offset;
        $durations[] = $duration;
        // Color by status
        // Choose pastel colours matching status definitions
        switch ($t['status']) {
            case 'in_progress':
                $color = '#dbeafe'; // blue pastel
                break;
            case 'bug_review':
                $color = '#fdf2f8'; // pink pastel
                break;
            case 'done':
                $color = '#ecfdf5'; // green pastel
                break;
            case 'todo':
            default:
                $color = '#f3f4f6'; // grey pastel
                break;
        }
        $backgroundColors[] = $color;
    }
    ?>
    <!-- Table of tasks for quick access -->
    <!-- Wrap the tasks table in a scrollable container to prevent the Gantt view from becoming too tall -->
    <div style="margin-top:1rem; overflow-x:auto; max-height:300px; overflow-y:auto;">
        <table style="width:100%; border-collapse:collapse;" class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th style="text-align:left; padding:0.25rem; border-bottom:1px solid var(--border);"><?php echo __('task_name_col'); ?></th>
                    <th style="text-align:left; padding:0.25rem; border-bottom:1px solid var(--border);"><?php echo __('start_date_col'); ?></th>
                    <th style="text-align:left; padding:0.25rem; border-bottom:1px solid var(--border);"><?php echo __('end_date_col'); ?></th>
                    <th style="text-align:left; padding:0.25rem; border-bottom:1px solid var(--border);"><?php echo __('status'); ?></th>
                    <th style="text-align:left; padding:0.25rem; border-bottom:1px solid var(--border);"><?php echo __('assigned_to'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($flat as $t): ?>
                    <tr>
                        <td style="padding:0.25rem; border-bottom:1px solid var(--border);">
                            <a href="index.php?controller=task&action=edit&id=<?php echo e($t['id']); ?>">
                                <?php echo e($t['name']); ?><?php if (!empty($t['parent_id'])) echo ' (sub)'; ?>
                            </a>
                        </td>
                        <td style="padding:0.25rem; border-bottom:1px solid var(--border);"><?php echo e($t['start_date']); ?></td>
                        <td style="padding:0.25rem; border-bottom:1px solid var(--border);"><?php echo e($t['due_date']); ?></td>
                        <td style="padding:0.25rem; border-bottom:1px solid var(--border);"><?php echo e($t['status']); ?></td>
                        <td style="padding:0.25rem; border-bottom:1px solid var(--border);"><?php echo e($t['assignees']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
    // Generate Gantt-style chart using Chart.js stacked horizontal bar
    const ganttCtx = document.getElementById('ganttChart').getContext('2d');
    const ganttData = {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [
            {
                label: 'Start Offset',
                data: <?php echo json_encode($offsets); ?>,
                backgroundColor: 'rgba(0,0,0,0)',
                stack: 'combined',
            },
            {
                label: 'Duration',
                data: <?php echo json_encode($durations); ?>,
                backgroundColor: <?php echo json_encode($backgroundColors); ?>,
                stack: 'combined',
            }
        ]
    };
    new Chart(ganttCtx, {
        type: 'bar',
        data: ganttData,
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.chart.data.labels[context.dataIndex];
                            const duration = context.chart.data.datasets[1].data[context.dataIndex];
                            return label + ': ' + duration + ' ngày';
                        }
                    }
                },
                title: { display: true, text: 'Task Timeline' }
            },
            scales: {
                x: {
                    stacked: true,
                    title: { display: true, text: 'Ngày từ mốc bắt đầu' }
                },
                y: { stacked: true }
            }
        }
    });
    </script>
</div>