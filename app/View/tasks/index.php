<h1 style="margin-bottom:0.5rem;">Dự án: <?php echo e($project['name']); ?></h1>
<!-- Project navigation -->
<?php if (!empty($allProjects)): ?>
    <div class="project-nav" style="margin-bottom:1rem;">
        <?php foreach ($allProjects as $proj): ?>
            <a href="index.php?controller=task&project_id=<?php echo e($proj['id']); ?>&view=<?php echo e($view); ?>" class="btn <?php echo $proj['id']==$project['id'] ? 'btn-secondary' : 'btn-primary'; ?>" style="margin-right:0.25rem; margin-bottom:0.25rem;"><?php echo e($proj['name']); ?></a>
        <?php endforeach; ?>
    </div>
    <?php // JavaScript for collapsing/expanding subtask lists in Kanban view ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Attach click handlers to toggle-subtasks buttons. When clicked, it toggles
        // the visibility of the corresponding subtask-list and updates the icon.
        document.querySelectorAll('.toggle-subtasks').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var taskId = this.getAttribute('data-task-id');
                var list = document.querySelector('.subtask-list[data-parent-id="' + taskId + '"]');
                if (list) {
                    var icon = this.querySelector('i');
                    var hidden = (list.style.display === 'none' || list.style.display === '');
                    if (hidden) {
                        list.style.display = 'block';
                        if (icon) {
                            icon.classList.remove('fa-chevron-right');
                            icon.classList.add('fa-chevron-down');
                        }
                    } else {
                        list.style.display = 'none';
                        if (icon) {
                            icon.classList.remove('fa-chevron-down');
                            icon.classList.add('fa-chevron-right');
                        }
                    }
                }
            });
        });
    });
    </script>
<?php endif; ?>
<div style="margin-bottom:1rem; display:flex; flex-wrap:wrap; gap:0.25rem;">
    <a href="index.php?controller=task&project_id=<?php echo e($project['id']); ?>&view=kanban" class="btn <?php echo $view==='kanban' ? 'btn-secondary' : 'btn-primary'; ?>"><?php echo __('board'); ?></a>
    <a href="index.php?controller=task&project_id=<?php echo e($project['id']); ?>&view=list" class="btn <?php echo $view==='list' ? 'btn-secondary' : 'btn-primary'; ?>"><?php echo __('list'); ?></a>
    <a href="index.php?controller=task&project_id=<?php echo e($project['id']); ?>&view=calendar" class="btn <?php echo $view==='calendar' ? 'btn-secondary' : 'btn-primary'; ?>"><?php echo __('calendar'); ?></a>
    <a href="index.php?controller=task&project_id=<?php echo e($project['id']); ?>&view=gantt" class="btn <?php echo $view==='gantt' ? 'btn-secondary' : 'btn-primary'; ?>"><?php echo __('gantt'); ?></a>
    <a href="index.php?controller=task&project_id=<?php echo e($project['id']); ?>&view=flow" class="btn <?php echo $view==='flow' ? 'btn-secondary' : 'btn-primary'; ?>"><?php echo __('flow'); ?></a>
    <?php if ($view === 'kanban'): ?>
        <?php
            // Determine current group mode. If a group parameter is present in
            // the URL use it directly; otherwise fall back to the value stored
            // in the session (persisted by TaskController). Default to nested
            // grouping when neither exist. Use of $_SESSION here assumes the
            // session has already been started in index.php.
            $currentGroupMode = $_GET['group'] ?? ($_SESSION['task_group_mode'] ?? 'nested');
            $isFlat = ($currentGroupMode === 'flat');
            // Build URL to toggle grouping. Preserve existing query parameters
            // while flipping the group setting. This ensures the next click
            // will switch between flat and nested views.
            $queryParams = $_GET;
            $queryParams['controller'] = 'task';
            $queryParams['project_id'] = $project['id'];
            $queryParams['view'] = 'kanban';
            $queryParams['group'] = $isFlat ? 'nested' : 'flat';
            $toggleUrl = 'index.php?' . http_build_query($queryParams);
            // Choose an appropriate label and icon based on the current mode.
            $toggleLabel = $isFlat ? __('group_subtasks') : __('separate_subtasks');
            $toggleIcon = $isFlat ? 'fa-layer-group' : 'fa-list-ul';
        ?>
        <?php /* Removed the old "Group subtasks" button since a dedicated subtask display mode selector is provided below. */ ?>
    <?php endif; ?>
</div>

<!-- Filter form applicable to all views -->
<div class="task-filter" style="margin-bottom:1rem;">
    <form method="get" class="row g-2 align-items-end">
        <input type="hidden" name="controller" value="task">
        <input type="hidden" name="project_id" value="<?php echo e($project['id']); ?>">
        <input type="hidden" name="view" value="<?php echo e($view); ?>">
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
            <a href="index.php?controller=task&project_id=<?php echo e($project['id']); ?>&view=<?php echo e($view); ?>" class="btn btn-secondary btn-sm"><?php echo __('clear'); ?></a>
        </div>
    </form>
</div>

<?php if ($view !== 'kanban'): ?>
<div style="margin-bottom:1rem;">
    <a href="index.php?controller=task&action=create&project_id=<?php echo e($project['id']); ?>&view=<?php echo e($view); ?>" class="btn btn-primary">➕ Thêm task</a>
</div>
<?php endif; ?>

<?php if ($view === 'kanban'): ?>
        <?php
        // Determine current grouping mode again for the data attribute used by
        // client-side scripts. Use the same logic as above to fall back to
        // the session value if no query parameter is provided.
        $currentGroupMode = $_GET['group'] ?? ($_SESSION['task_group_mode'] ?? 'nested');
        ?>
        <!-- Group mode selector: collapsed, nested (expanded), flat -->
        <div class="mb-3">
            <?php
                $modes = [
                    'collapsed' => __('collapse_subtasks'),
                    'nested'    => __('expand_subtasks'),
                    'flat'      => __('separate_subtasks'),
                ];
            ?>
            <div class="btn-group" role="group">
                <?php foreach ($modes as $modeKey => $modeLabel):
                    $params = $_GET;
                    $params['controller'] = 'task';
                    $params['project_id'] = $project['id'];
                    $params['view'] = 'kanban';
                    $params['group'] = $modeKey;
                    $url = 'index.php?' . http_build_query($params);
                    $active = ($currentGroupMode === $modeKey) ? 'btn-secondary' : 'btn-outline-secondary';
                ?>
                    <a href="<?php echo e($url); ?>" class="btn <?php echo e($active); ?> btn-sm" style="text-transform:none;">
                        <?php echo e($modeLabel); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="kanban-board" x-data="{}" data-group-mode="<?php echo e($currentGroupMode); ?>">
        <?php
        // Define statuses including the new Bug/Review state.  The order here controls
        // the order of columns from left to right.  Users can drag tasks between
        // these columns.  The 'bug_review' status sits between In Progress and Done.
        $statuses = [
            'todo' => __('todo'),
            'in_progress' => __('in_progress'),
            'bug_review' => __('bug_review'),
            'done' => __('done')
        ];
        // Define pastel background colours for each column status
        $columnBg = [
            'todo' => 'var(--status-todo)',
            'in_progress' => 'var(--status-in-progress)',
            'bug_review' => 'var(--status-bug-review)',
            'done' => 'var(--status-done)'
        ];
        foreach ($statuses as $statusKey => $statusLabel):
            $columnTasks = $tasks[$statusKey] ?? [];
        ?>
        <div class="kanban-column" data-status="<?php echo e($statusKey); ?>" style="background-color: <?php echo $columnBg[$statusKey]; ?>;">
            <!-- Column header with status label and add task button -->
            <h4 style="display:flex; justify-content:space-between; align-items:center; padding:0.5rem; background-color:rgba(255,255,255,0.5); margin:0; border-bottom:1px solid var(--border);">
                <span><?php echo e($statusLabel); ?> <span style="font-size:0.8rem; color:var(--muted);">(<?php echo count($columnTasks); ?>)</span></span>
                <?php /** All project members can add subtasks */ ?>
                <a href="index.php?controller=task&action=create&project_id=<?php echo e($project['id']); ?>&status=<?php echo e($statusKey); ?>&view=kanban" class="btn btn-primary btn-sm" style="font-size:0.9rem;">
                    <i class="fa-solid fa-plus"></i>
                </a>
            </h4>
            <div class="kanban-items" style="padding:0.5rem; flex-grow:1;">
                <?php foreach ($columnTasks as $task): ?>
                    <?php
                        // Determine styling for tasks: warning if done but subtasks incomplete, overdue and due soon
                        $warningTask = ($task['status'] === 'done' && $task['subtask_total'] > $task['subtask_done']);
                        $nowDate    = date('Y-m-d');
                        // Only flag overdue or due soon if task is not completed
                        $overdue    = (!empty($task['due_date']) && $task['due_date'] < $nowDate && $task['status'] !== 'done');
                        $dueSoon    = (!empty($task['due_date']) && $task['due_date'] >= $nowDate && $task['due_date'] <= date('Y-m-d', strtotime('+3 days')) && $task['status'] !== 'done');
                        $itemStyle = '';
                        if ($warningTask) {
                            $itemStyle = 'border:2px solid var(--danger);';
                        } elseif ($overdue) {
                            // pastel red for overdue
                            $itemStyle = 'border:2px solid #b91c1c; background-color: var(--status-overdue);';
                        } elseif ($dueSoon) {
                            // pastel yellow for upcoming
                            $itemStyle = 'border:2px solid #ca8a04; background-color: var(--status-due-soon);';
                        }
                    ?>
                    <div class="kanban-item" data-id="<?php echo e($task['id']); ?>" data-subtask="<?php echo !empty($task['is_subtask']) ? 'true' : 'false'; ?>" data-due-date="<?php echo e($task['due_date']); ?>" style="<?php echo $itemStyle; ?>">
                        <!-- Task name line -->
                        <div style="display:flex; align-items:center; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:0.25rem;">
                                <?php if (!empty($task['is_subtask'])): ?>
                                    <i class="fa-solid fa-code-branch" title="Subtask"></i>
                                <?php endif; ?>
                                <strong><a href="index.php?controller=task&action=edit&id=<?php echo e($task['id']); ?>&view=kanban"><?php echo e($task['name']); ?></a></strong>
                            </div>
                            <!-- Quick add subtask icon only for top-level tasks -->
                            <?php // Show the quick add subtask button whenever this is a top-level task ?>
                            <?php if (empty($task['parent_id'])): ?>
                                <?php /** All project members can add subtasks */ ?>
                                <a href="index.php?controller=task&action=create&project_id=<?php echo e($project['id']); ?>&parent_id=<?php echo e($task['id']); ?>&view=kanban" title="<?php echo __('create_subtask'); ?>" class="btn btn-secondary" style="padding:0.1rem 0.3rem; font-size:0.65rem;">
                                    <i class="fa-solid fa-plus"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        <!-- Priority label with icon -->
                        <?php
                            $priority = $task['priority'] ?? 'normal';
                            $priorityClass = 'priority-normal';
                            $priorityIcon = '<i class="fa-solid fa-flag"></i>';
                            // Define colours and icons based on priority
                            if ($priority === 'urgent') {
                                $priorityClass = 'priority-urgent';
                                $priorityIcon = '<i class="fa-solid fa-flag"></i>';
                            } elseif ($priority === 'high') {
                                $priorityClass = 'priority-high';
                                $priorityIcon = '<i class="fa-solid fa-flag"></i>';
                            } elseif ($priority === 'low') {
                                $priorityClass = 'priority-low';
                                $priorityIcon = '<i class="fa-solid fa-flag"></i>';
                            }
                        ?>
                        <div class="priority-label <?php echo e($priorityClass); ?>" style="margin-top:0.25rem; font-size:0.7rem; display:inline-block; padding:0.1rem 0.3rem; border-radius:0.25rem;">
                            <?php echo $priorityIcon . ' ' . __($priority); ?>
                        </div>
                        <!-- Icons row: description and attachments -->
                        <div style="margin-top:0.25rem; font-size:0.7rem; display:flex; gap:0.4rem; align-items:center;">
                            <?php if (!empty($task['description'])): ?>
                                <span title="<?php echo e(__('description')); ?>"><i class="fa-solid fa-file-lines"></i></span>
                            <?php endif; ?>
                            <?php if (!empty($task['comment_count']) && $task['comment_count'] > 0): ?>
                                <span title="<?php echo e(__('comments')); ?>"><i class="fa-solid fa-comments"></i> <?php echo e($task['comment_count']); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($task['attachment_count']) && $task['attachment_count'] > 0): ?>
                                <span title="<?php echo e(__('attachments')); ?>"><i class="fa-solid fa-paperclip"></i> <?php echo e($task['attachment_count']); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($task['subtask_total']) && $task['subtask_total'] > 0): ?>
                                <span title="<?php echo e(__('subtasks')); ?>"><i class="fa-solid fa-layer-group"></i> <?php echo e($task['subtask_done']); ?>/<?php echo e($task['subtask_total']); ?></span>
                            <?php endif; ?>
                        </div>
                        <!-- Tags -->
                        <?php if (!empty($task['tags'])): ?>
                            <div class="tags" style="margin-top:0.25rem;">
                                <?php $tagsArr = array_filter(array_map('trim', explode(',', $task['tags']))); ?>
                                <?php foreach ($tagsArr as $tag): ?>
                                    <span class="tag-label" style="display:inline-block; background-color:var(--surface); border:1px solid var(--border); border-radius:0.25rem; padding:0.1rem 0.3rem; margin-right:0.1rem; font-size:0.6rem; color:var(--muted);">
                                        🏷️ <?php echo e($tag); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <!-- Subtask progress bar if subtasks exist -->
                        <?php if ($task['subtask_total'] > 0): ?>
                            <?php $percent = ($task['subtask_total'] > 0) ? intval(($task['subtask_done'] / $task['subtask_total']) * 100) : 0; ?>
                            <div class="progress-container" style="margin-top:0.25rem;">
                                <div class="progress-bar" style="height:4px; width:100%; background-color:#e5e7eb; border-radius:2px;">
                                    <div style="height:4px; width:<?php echo $percent; ?>%; background-color: var(--secondary); border-radius:2px;"></div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <!-- Assignee and due date line -->
                        <div style="margin-top:0.25rem; font-size:0.7rem; color:var(--muted);">
                            <?php if (!empty($task['assignee'])): ?>
                                <span>👤 <?php echo e($task['assignee']); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($task['due_date'])): ?>
                                <span style="margin-left:0.5rem;">📅 <?php echo e($task['due_date']); ?></span>
                            <?php endif; ?>
                        </div>
                        <!-- Show parent reference for flattened subtasks -->
                        <?php if (!empty($task['is_subtask']) && !empty($task['parent_name'])): ?>
                        <div class="parent-ref" style="margin-top:0.25rem; font-size:0.6rem; color:var(--muted);">
                            <?php echo e(__('parent')); ?>: <?php echo e($task['parent_name']); ?>
                        </div>
                        <?php endif; ?>
                        <!-- Display subtasks inline. When group mode is 'collapsed' the list is initially hidden; a toggle button is shown to expand/collapse. -->
                        <?php if (!empty($task['subtasks'])): ?>
                            <?php
                                // Determine group mode again: collapsed means hide subtasks by default
                                $subListStyle = ($currentGroupMode === 'collapsed') ? 'display:none;' : '';
                                // Determine if we need a toggle button for collapsed mode
                                $showToggle = ($currentGroupMode === 'collapsed');
                            ?>
                            <?php if ($showToggle): ?>
                                <button type="button" class="btn btn-link p-0 toggle-subtasks" data-task-id="<?php echo e($task['id']); ?>" title="<?php echo e(__('toggle_subtasks')); ?>" style="font-size:0.7rem;">
                                    <i class="fa-solid fa-chevron-right"></i> <?php echo e(__('subtasks')); ?>
                                </button>
                            <?php endif; ?>
                            <div class="subtask-list" data-parent-id="<?php echo e($task['id']); ?>" style="margin-top:0.5rem; margin-left:0.5rem; <?php echo $subListStyle; ?>">
                                <?php foreach ($task['subtasks'] as $sub): ?>
                                    <?php
                                        $subWarn = ($task['status'] === 'done' && $task['subtask_total'] > $task['subtask_done'] && $sub['status'] !== 'done');
                                        $subStyle = 'background-color: var(--surface); border:1px solid var(--border);';
                                        if ($subWarn) {
                                            $subStyle = 'background-color:#fee2e2; border:1px solid #fecaca;';
                                        }
                                        $subPriority = $sub['priority'] ?? 'normal';
                                        $subPrClass = 'priority-normal';
                                        $subIcon = '<i class="fa-solid fa-flag"></i>';
                                        if ($subPriority === 'urgent') { $subPrClass = 'priority-urgent'; }
                                        elseif ($subPriority === 'high') { $subPrClass = 'priority-high'; }
                                        elseif ($subPriority === 'low') { $subPrClass = 'priority-low'; }
                                    ?>
                                    <div class="subtask-item" data-id="<?php echo e($sub['id']); ?>" data-subtask="true" style="<?php echo $subStyle; ?> border-radius:0.25rem; padding:0.3rem; margin-bottom:0.3rem;">
                                        <strong style="font-size:0.7rem;"><a href="index.php?controller=task&action=edit&id=<?php echo e($sub['id']); ?>&view=kanban"><?php echo e($sub['name']); ?></a></strong>
                                        <span class="<?php echo e($subPrClass); ?>" style="font-size:0.55rem; margin-left:0.25rem; padding:0.05rem 0.25rem; border-radius:0.2rem; border:1px solid var(--border); background-color:var(--surface); color:var(--muted);">
                                            <?php echo $subIcon . ' ' . __($subPriority); ?>
                                        </span>
                                        <?php if (!empty($sub['tags'])): ?>
                                            <?php $subTags = array_filter(array_map('trim', explode(',', $sub['tags']))); ?>
                                            <?php foreach ($subTags as $stag): ?>
                                                <span style="font-size:0.55rem; margin-left:0.2rem; padding:0.05rem 0.2rem; border-radius:0.2rem; border:1px solid var(--border); background-color:var(--surface); color:var(--muted);">
                                                    🏷️ <?php echo e($stag); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <?php if (!empty($sub['assignee']) || !empty($sub['due_date'])): ?>
                                            <div style="font-size:0.55rem; color:var(--muted); margin-top:0.2rem;">
                                                <?php if (!empty($sub['assignee'])): ?>
                                                    <span>👤 <?php echo e($sub['assignee']); ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($sub['due_date'])): ?>
                                                    <span style="margin-left:0.3rem;">📅 <?php echo e($sub['due_date']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Bottom add task button for column -->
            <div style="padding:0.5rem; border-top:1px solid var(--border); text-align:center;">
                <a href="index.php?controller=task&action=create&project_id=<?php echo e($project['id']); ?>&status=<?php echo e($statusKey); ?>&view=kanban" class="btn btn-outline-primary btn-sm" style="font-size:0.75rem;">
                    <i class="fa-solid fa-plus"></i> <?php echo e(__('create_task')); ?>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php elseif ($view === 'list'): ?>
    <div class="card">
        <!-- Sort and group form (filter by tag handled globally) -->
        <form method="get" style="margin-bottom:0.5rem; display:flex; flex-wrap:wrap; gap:0.5rem; align-items:flex-end;">
            <input type="hidden" name="controller" value="task">
            <input type="hidden" name="project_id" value="<?php echo e($project['id']); ?>">
            <input type="hidden" name="view" value="list">
            <?php $sort = $_GET['sort'] ?? ''; ?>
            <div>
                <label for="sort" style="font-size:0.75rem; color:var(--muted);">Sort by:</label><br>
                <select id="sort" name="sort" style="padding:0.3rem; border:1px solid var(--border); border-radius:0.25rem; font-size:0.75rem;">
                    <option value="" <?php echo $sort === '' ? 'selected' : ''; ?>>---</option>
                    <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name A-Z</option>
                    <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name Z-A</option>
                    <option value="priority_asc" <?php echo $sort === 'priority_asc' ? 'selected' : ''; ?>>Priority Low-High</option>
                    <option value="priority_desc" <?php echo $sort === 'priority_desc' ? 'selected' : ''; ?>>Priority High-Low</option>
                    <option value="due_date_asc" <?php echo $sort === 'due_date_asc' ? 'selected' : ''; ?>>Due date ↑</option>
                    <option value="due_date_desc" <?php echo $sort === 'due_date_desc' ? 'selected' : ''; ?>>Due date ↓</option>
                    <option value="status_asc" <?php echo $sort === 'status_asc' ? 'selected' : ''; ?>>Status A-Z</option>
                    <option value="status_desc" <?php echo $sort === 'status_desc' ? 'selected' : ''; ?>>Status Z-A</option>
                </select>
            </div>

            <div>
                <label for="group_by" style="font-size:0.75rem; color:var(--muted);">Group by:</label><br>
                <?php $groupBy = $_GET['group_by'] ?? ''; ?>
                <select id="group_by" name="group_by" style="padding:0.3rem; border:1px solid var(--border); border-radius:0.25rem; font-size:0.75rem;">
                    <option value="" <?php echo $groupBy === '' ? 'selected' : ''; ?>><?php echo __('none'); ?></option>
                    <option value="status" <?php echo $groupBy === 'status' ? 'selected' : ''; ?>><?php echo __('status'); ?></option>
                    <option value="assignee" <?php echo $groupBy === 'assignee' ? 'selected' : ''; ?>><?php echo __('assigned_to'); ?></option>
                    <option value="priority" <?php echo $groupBy === 'priority' ? 'selected' : ''; ?>><?php echo __('priority'); ?></option>
                    <option value="due_date" <?php echo $groupBy === 'due_date' ? 'selected' : ''; ?>><?php echo __('due_date'); ?></option>
                    <option value="tags" <?php echo $groupBy === 'tags' ? 'selected' : ''; ?>><?php echo __('tags'); ?></option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary" style="padding:0.3rem 0.6rem; font-size:0.75rem;">Apply</button>
            </div>
        </form>
        <table>
            <thead>
                <tr>
                    <th><?php echo e(__('task_name')); ?></th>
                    <th><?php echo e(__('status')); ?></th>
                    <th>Priority</th>
                    <th>Start&nbsp;Date</th>
                    <th>Due&nbsp;Date</th>
                    <!-- Removed Tags and Assignee columns; assignee is shown under task name -->
                    <th><?php echo e(__('actions')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Flatten tasks and subtasks for filtering/sorting
                $list = [];
                foreach ($tasks as $statusKey => $items) {
                    foreach ($items as $item) {
                        $list[] = $item;
                        if (!empty($item['subtasks'])) {
                            foreach ($item['subtasks'] as $sub) {
                                $list[] = $sub;
                            }
                        }
                    }
                }
                // Filter by tag
                $tagFilter = trim($_GET['tag_filter'] ?? '');
                if ($tagFilter !== '') {
                    $list = array_filter($list, function ($t) use ($tagFilter) {
                        $tags = $t['tags'] ?? '';
                        return stripos($tags, $tagFilter) !== false;
                    });
                }
                // Sort with ascending/descending support
                $sortBy = $_GET['sort'] ?? '';
                if ($sortBy !== '') {
                    $desc = false;
                    $field = $sortBy;
                    if (str_ends_with($sortBy, '_desc')) {
                        $desc = true;
                        $field = substr($sortBy, 0, -5);
                    } elseif (str_ends_with($sortBy, '_asc')) {
                        $field = substr($sortBy, 0, -4);
                    }
                    usort($list, function ($a, $b) use ($field, $desc) {
                        $valA = $a[$field] ?? '';
                        $valB = $b[$field] ?? '';
                        // Custom ordering for priority (high->normal->low)
                        if ($field === 'priority') {
                            // Define custom ordering: urgent first, then high, normal, low
                            $order = ['urgent' => 1, 'high' => 2, 'normal' => 3, 'low' => 4];
                            $valA = $order[$a['priority']] ?? 5;
                            $valB = $order[$b['priority']] ?? 5;
                        }
                        if ($valA == $valB) return 0;
                        if ($desc) {
                            return $valA < $valB ? 1 : -1;
                        } else {
                            return $valA < $valB ? -1 : 1;
                        }
                    });
                }
                ?>
                <?php
                    if (empty($list)) {
                        echo '<tr><td colspan="8">' . __('no_tasks') . '</td></tr>';
                    } else {
                        // Grouping and pagination
                        $groupBy = $_GET['group_by'] ?? '';
                        $groupedList = [];
                        if ($groupBy !== '') {
                            foreach ($list as $t) {
                                switch ($groupBy) {
                                    case 'status':
                                        $key = $t['status'];
                                        break;
                                    case 'assignee':
                                        $key = $t['assignee'] ?: __('unassigned');
                                        break;
                                    case 'priority':
                                        $key = $t['priority'] ?? 'normal';
                                        break;
                                    case 'due_date':
                                        $key = $t['due_date'] ?? __('no_due_date');
                                        break;
                                    case 'tags':
                                        $tags = array_filter(array_map('trim', explode(',', $t['tags'] ?? '')));
                                        $key = !empty($tags) ? $tags[0] : __('no_tags');
                                        break;
                                    default:
                                        $key = '';
                                }
                                $groupedList[$key][] = $t;
                            }
                            // Custom ordering for certain group types
                            if ($groupBy === 'status') {
                                // Order: bug_review > in_progress > todo > done
                                $statusOrder = ['bug_review', 'in_progress', 'todo', 'done'];
                                uksort($groupedList, function($a, $b) use ($statusOrder) {
                                    $posA = array_search($a, $statusOrder);
                                    $posB = array_search($b, $statusOrder);
                                    $posA = ($posA === false) ? count($statusOrder) : $posA;
                                    $posB = ($posB === false) ? count($statusOrder) : $posB;
                                    return $posA <=> $posB;
                                });
                            } elseif ($groupBy === 'priority') {
                                // Order: urgent > high > normal > low
                                $priorityOrder = ['urgent', 'high', 'normal', 'low'];
                                uksort($groupedList, function($a, $b) use ($priorityOrder) {
                                    $posA = array_search($a, $priorityOrder);
                                    $posB = array_search($b, $priorityOrder);
                                    $posA = ($posA === false) ? count($priorityOrder) : $posA;
                                    $posB = ($posB === false) ? count($priorityOrder) : $posB;
                                    return $posA <=> $posB;
                                });
                            } elseif ($groupBy === 'assignee') {
                                // Sort alphabetically but move Unassigned to the end
                                uksort($groupedList, function($a, $b) {
                                    $aLower = strtolower($a);
                                    $bLower = strtolower($b);
                                    if ($aLower === 'unassigned') return 1;
                                    if ($bLower === 'unassigned') return -1;
                                    return $aLower <=> $bLower;
                                });
                            } else {
                                ksort($groupedList);
                            }
                        }
                        // Pagination settings
                        $perPage = 10;
                        $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
                        if ($groupBy === '') {
                            $totalRows = count($list);
                            $totalPages = ceil($totalRows / $perPage);
                            $offset = ($currentPage - 1) * $perPage;
                            $pagedData = array_slice($list, $offset, $perPage);
                            foreach ($pagedData as $task) {
                                include __DIR__ . '/partials/list_row.php';
                            }
                        } else {
                            $groupKeys = array_keys($groupedList);
                            $totalRows = count($groupKeys);
                            $totalPages = ceil($totalRows / $perPage);
                            $offset = ($currentPage - 1) * $perPage;
                            $pagedKeys = array_slice($groupKeys, $offset, $perPage);
                            foreach ($pagedKeys as $key) {
                                echo '<tr><td colspan="8" style="background-color:var(--surface); font-weight:bold;">' . e(ucfirst($key)) . '</td></tr>';
                                foreach ($groupedList[$key] as $task) {
                                    include __DIR__ . '/partials/list_row.php';
                                }
                            }
                        }
                    }
                ?>
            </tbody>
        </table>
        <?php if (!empty($list) && $totalPages > 1): ?>
            <nav aria-label="Task page navigation">
                <ul class="pagination">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <?php
                            // Build query params for pagination preserving other filters
                            $urlParams = $_GET;
                            $urlParams['page'] = $p;
                            $queryString = http_build_query($urlParams);
                        ?>
                        <li class="page-item <?php echo $p == $currentPage ? 'active' : ''; ?>">
                            <a class="page-link" href="index.php?<?php echo $queryString; ?>"><?php echo $p; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
<?php elseif ($view === 'calendar'): ?>
    <div class="card">
        <?php
        // Navigation between months
        $currentMonth = $_GET['month'] ?? date('Y-m');
        // Compute previous and next months
        $curDateNav = DateTime::createFromFormat('Y-m', $currentMonth);
        $prevMonth = (clone $curDateNav)->modify('-1 month')->format('Y-m');
        $nextMonth = (clone $curDateNav)->modify('+1 month')->format('Y-m');
        ?>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <a href="index.php?controller=task&project_id=<?php echo e($project['id']); ?>&view=calendar&month=<?php echo e($prevMonth); ?>" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-chevron-left"></i></a>
                <span class="fw-bold mx-2"><?php echo date('F Y', strtotime($currentMonth . '-01')); ?></span>
                <a href="index.php?controller=task&project_id=<?php echo e($project['id']); ?>&view=calendar&month=<?php echo e($nextMonth); ?>" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-chevron-right"></i></a>
            </div>
        </div>
        <?php
        // Build events across date ranges. Only tasks with start or due dates are considered.
        $events = [];
        foreach ($tasks as $status => $items) {
            foreach ($items as $task) {
                $start = $task['start_date'] ?? null;
                $end = $task['due_date'] ?? null;
                if ($start === null && $end === null) {
                    // skip tasks without any date
                } else {
                    $s = $start ? new DateTime($start) : ($end ? new DateTime($end) : null);
                    $e = $end ? new DateTime($end) : ($start ? new DateTime($start) : null);
                    if ($s && $e) {
                        if ($s > $e) { $tmp = $s; $s = $e; $e = $tmp; }
                        $current = clone $s;
                        while ($current <= $e) {
                            $key = $current->format('Y-m-d');
                            $events[$key][] = $task;
                            $current->modify('+1 day');
                        }
                    }
                }
                // Include subtasks
                if (!empty($task['subtasks'])) {
                    foreach ($task['subtasks'] as $sub) {
                        $s2 = $sub['start_date'] ?? null;
                        $e2 = $sub['due_date'] ?? null;
                        if ($s2 === null && $e2 === null) continue;
                        $ss = $s2 ? new DateTime($s2) : ($e2 ? new DateTime($e2) : null);
                        $ee = $e2 ? new DateTime($e2) : ($s2 ? new DateTime($s2) : null);
                        if ($ss && $ee) {
                            if ($ss > $ee) { $tmp2 = $ss; $ss = $ee; $ee = $tmp2; }
                            $cur = clone $ss;
                            while ($cur <= $ee) {
                                $key2 = $cur->format('Y-m-d');
                                $events[$key2][] = $sub;
                                $cur->modify('+1 day');
                            }
                        }
                    }
                }
            }
        }
        // Sort events by date
        ksort($events);
        // Build month grid
        $currentMonth = $_GET['month'] ?? date('Y-m');
        $year = (int)substr($currentMonth, 0, 4);
        $mon = (int)substr($currentMonth, 5, 2);
        $startDate = new DateTime(sprintf('%04d-%02d-01', $year, $mon));
        $endOfMonth = clone $startDate;
        $endOfMonth->modify('last day of this month');
        $dayCount = (int)$endOfMonth->format('j');
        $firstWeekday = (int)$startDate->format('N');
        // Map of events for each day of this month
        $monthEvents = [];
        for ($d=1; $d<= $dayCount; $d++) {
            $ds = sprintf('%04d-%02d-%02d', $year, $mon, $d);
            $monthEvents[$ds] = $events[$ds] ?? [];
        }
        ?>
        <div class="calendar-grid" style="display:grid; grid-template-columns: repeat(7, 1fr); gap:0.25rem;">
            <?php
            // Headers for weekdays (Mon-Sun)
            $weekdays = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
            foreach ($weekdays as $wd) {
                echo '<div style="font-weight:bold; text-align:center;">' . $wd . '</div>';
            }
            // Render 6 rows (weeks) to cover the month
            $cell=1;
            $emptyBefore = $firstWeekday - 1;
            for ($row=0; $row<6; $row++) {
                for ($col=0; $col<7; $col++, $cell++) {
                    $dayNum = $cell - $emptyBefore;
                    if ($dayNum < 1 || $dayNum > $dayCount) {
                        echo '<div style="min-height:80px; background-color:var(--surface); border:1px solid var(--border);"></div>';
                    } else {
                        $dstr = sprintf('%04d-%02d-%02d',$year,$mon,$dayNum);
                        echo '<div style="min-height:80px; background-color:var(--surface); border:1px solid var(--border); padding:0.25rem; font-size:0.7rem;">
                        <div style="font-weight:bold;">' . $dayNum . '</div>';
                        if (!empty($monthEvents[$dstr])) {
                            foreach ($monthEvents[$dstr] as $ev) {
                                // Determine pastel colour based on status using CSS variables
                                $bg = 'var(--status-todo)';
                                switch ($ev['status']) {
                                    case 'todo':
                                        $bg = 'var(--status-todo)';
                                        break;
                                    case 'in_progress':
                                        $bg = 'var(--status-in-progress)';
                                        break;
                                    case 'bug_review':
                                        $bg = 'var(--status-bug-review)';
                                        break;
                                    case 'done':
                                        $bg = 'var(--status-done)';
                                        break;
                                }
                                echo '<div style="margin-top:0.2rem; padding:0.1rem 0.2rem; border-radius:0.25rem; background-color:' . $bg . '; color:#374151; font-size:0.6rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">';
                                echo '<a href="index.php?controller=task&action=edit&id=' . $ev['id'] . '" style="color:inherit; text-decoration:none;">' . htmlspecialchars($ev['name']) . '</a>';
                                echo '</div>';
                            }
                        }
                        echo '</div>';
                    }
                }
            }
            ?>
        </div>
    </div>
<?php elseif ($view === 'gantt'): ?>
    <?php include __DIR__ . '/gantt.php'; ?>
<?php elseif ($view === 'flow'): ?>
    <?php include __DIR__ . '/flow.php'; ?>
<?php else: ?>
    <p>Unknown view.</p>
<?php endif; ?>