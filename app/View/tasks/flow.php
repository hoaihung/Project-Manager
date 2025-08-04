<!-- Navigation bar: reuse view navigation from parent -->
<div style="margin-bottom:1rem; display:flex; flex-wrap:wrap; gap:0.25rem;">
    <a href="index.php?controller=task&project_id=<?php echo e($project['id']); ?>&view=kanban" class="btn <?php echo $view==='kanban' ? 'btn-secondary' : 'btn-primary'; ?>"><?php echo __('board'); ?></a>
    <a href="index.php?controller=task&project_id=<?php echo e($project['id']); ?>&view=list" class="btn <?php echo $view==='list' ? 'btn-secondary' : 'btn-primary'; ?>"><?php echo __('list'); ?></a>
    <a href="index.php?controller=task&project_id=<?php echo e($project['id']); ?>&view=calendar" class="btn <?php echo $view==='calendar' ? 'btn-secondary' : 'btn-primary'; ?>"><?php echo __('calendar'); ?></a>
    <a href="index.php?controller=task&project_id=<?php echo e($project['id']); ?>&view=gantt" class="btn <?php echo $view==='gantt' ? 'btn-secondary' : 'btn-primary'; ?>"><?php echo __('gantt'); ?></a>
    <a href="index.php?controller=task&project_id=<?php echo e($project['id']); ?>&view=flow" class="btn <?php echo $view==='flow' ? 'btn-secondary' : 'btn-primary'; ?>"><?php echo __('flow'); ?></a>
</div>

<!-- Filter form similar to other views -->
<div class="task-filter" style="margin-bottom:1rem;">
    <form method="get" style="display:flex; flex-wrap:wrap; gap:0.5rem; align-items:flex-end;">
        <input type="hidden" name="controller" value="task">
        <input type="hidden" name="project_id" value="<?php echo e($project['id']); ?>">
        <input type="hidden" name="view" value="flow">
        <div>
            <label style="font-size:0.75rem; color:var(--muted);" for="tag_filter"><?php echo __('tag_label'); ?></label><br>
            <input type="text" id="tag_filter" name="tag_filter" value="<?php echo e($_GET['tag_filter'] ?? ''); ?>" placeholder="<?php echo __('tags_placeholder'); ?>" style="padding:0.3rem; border:1px solid var(--border); border-radius:0.25rem; font-size:0.75rem;">
        </div>
        <div>
            <label style="font-size:0.75rem; color:var(--muted);" for="user_filter"><?php echo __('user_label'); ?></label><br>
            <select id="user_filter" name="user_filter" style="padding:0.3rem; border:1px solid var(--border); border-radius:0.25rem; font-size:0.75rem;">
                <option value=""><?php echo __('any'); ?></option>
                <?php foreach ($users as $u): ?>
                    <option value="<?php echo e($u['id']); ?>" <?php echo (isset($_GET['user_filter']) && $_GET['user_filter'] == $u['id']) ? 'selected' : ''; ?>><?php echo e($u['full_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size:0.75rem; color:var(--muted);" for="priority_filter"><?php echo __('priority_label'); ?></label><br>
            <select id="priority_filter" name="priority_filter" style="padding:0.3rem; border:1px solid var(--border); border-radius:0.25rem; font-size:0.75rem;">
                <option value=""><?php echo __('any'); ?></option>
                <option value="high" <?php echo (isset($_GET['priority_filter']) && $_GET['priority_filter'] === 'high') ? 'selected' : ''; ?>><?php echo __('high'); ?></option>
                <option value="normal" <?php echo (isset($_GET['priority_filter']) && $_GET['priority_filter'] === 'normal') ? 'selected' : ''; ?>><?php echo __('normal'); ?></option>
                <option value="low" <?php echo (isset($_GET['priority_filter']) && $_GET['priority_filter'] === 'low') ? 'selected' : ''; ?>><?php echo __('low'); ?></option>
            </select>
        </div>
        <div>
            <label style="font-size:0.75rem; color:var(--muted);" for="start_filter"><?php echo __('from'); ?></label><br>
            <input type="date" id="start_filter" name="start_filter" value="<?php echo e($_GET['start_filter'] ?? ''); ?>" style="padding:0.3rem; border:1px solid var(--border); border-radius:0.25rem; font-size:0.75rem;">
        </div>
        <div>
            <label style="font-size:0.75rem; color:var(--muted);" for="end_filter"><?php echo __('to'); ?></label><br>
            <input type="date" id="end_filter" name="end_filter" value="<?php echo e($_GET['end_filter'] ?? ''); ?>" style="padding:0.3rem; border:1px solid var(--border); border-radius:0.25rem; font-size:0.75rem;">
        </div>
        <div>
            <button type="submit" class="btn btn-primary" style="padding:0.3rem 0.6rem; font-size:0.75rem;"><?php echo __('apply'); ?></button>
        </div>
        <div>
            <a href="index.php?controller=task&project_id=<?php echo e($project['id']); ?>&view=flow" class="btn btn-secondary" style="padding:0.3rem 0.6rem; font-size:0.75rem;"><?php echo __('clear'); ?></a>
        </div>
    </form>
</div>

<h2 style="margin-bottom:1rem;"><?php echo __('flow_view'); ?> - <?php echo __('project'); ?>: <?php echo e($project['name']); ?></h2>
<div class="card">
    <p><?php echo __('flow_description'); ?></p>
    <?php
    // Build a mapping of task ID to name (include assignee for clarity)
    $idToName = [];
    $all = [];
    foreach ($tasks as $status => $items) {
        foreach ($items as $task) {
            $label = $task['name'];
            if (!empty($task['assignee'])) {
                $label .= ' (' . $task['assignee'] . ')';
            }
            $idToName[$task['id']] = $label;
            $all[$task['id']] = $task;
            if (!empty($task['subtasks'])) {
                foreach ($task['subtasks'] as $sub) {
                    $slabel = $sub['name'];
                    if (!empty($sub['assignee'])) {
                        $slabel .= ' (' . $sub['assignee'] . ')';
                    }
                    $idToName[$sub['id']] = $slabel;
                    $all[$sub['id']] = $sub;
                }
            }
        }
    }
    // Build mermaid graph lines.  Use IDs as node identifiers to avoid spaces.
    $lines = [];
    if (!empty($dependencies)) {
        foreach ($dependencies as $dep) {
            $fromId = $dep['depends_on_id'];
            $toId = $dep['task_id'];
            if (isset($idToName[$fromId]) && isset($idToName[$toId])) {
                $fromLabel = addslashes($idToName[$fromId]);
                $toLabel = addslashes($idToName[$toId]);
                $lines[] = "{$fromId}[\"{$fromLabel}\"] --> {$toId}[\"{$toLabel}\"]";
            }
        }
    }
    // If no dependencies, create a simple tree from parent/subtask relationships
    if (empty($lines)) {
        foreach ($tasks as $status => $items) {
            foreach ($items as $task) {
                if (!empty($task['subtasks'])) {
                    foreach ($task['subtasks'] as $sub) {
                        $parentId = $task['id'];
                        $childId = $sub['id'];
                        $fromLabel = addslashes($idToName[$parentId]);
                        $toLabel = addslashes($idToName[$childId]);
                        $lines[] = "{$parentId}[\"{$fromLabel}\"] --> {$childId}[\"{$toLabel}\"]";
                    }
                }
            }
        }
    }
    // Join lines into a single definition.  If still empty, show message.
    if (!empty($lines)) {
        // Define class definitions for statuses using pastel colours
        $classDefs = [];
        $classDefs[] = 'classDef status-todo fill:#f3f4f6;';
        $classDefs[] = 'classDef status-in_progress fill:#dbeafe;';
        $classDefs[] = 'classDef status-bug_review fill:#fdf2f8;';
        $classDefs[] = 'classDef status-done fill:#ecfdf5;';
        $classLines = [];
        foreach ($all as $tid => $taskObj) {
            $st = $taskObj['status'] ?? 'todo';
            $className = 'status-' . $st;
            $classLines[] = "class {$tid} {$className}";
        }
        $definition = "graph TD;\n" . implode("\n", $lines) . "\n" . implode("\n", $classDefs) . "\n" . implode("\n", $classLines);
        echo '<div class="mermaid" style="overflow-x:auto;">' . $definition . '</div>';
    } else {
        echo '<p>' . __('no_relationships') . '</p>';
    }
    ?>
</div>