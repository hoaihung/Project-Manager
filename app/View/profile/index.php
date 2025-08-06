<h1 style="margin-bottom:1rem;"><?php echo e(__('profile_title')); ?></h1>
<!-- Tabs navigation for profile and notifications -->
<ul class="nav nav-tabs" id="profileTab" style="margin-bottom:1rem;">
    <li class="nav-item">
        <a class="nav-link active" href="#profileInfo" onclick="showTab(event, 'profileInfo')"><?php echo e(__('profile')); ?></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#notifications" onclick="showTab(event, 'notifications')"><?php echo e(__('notifications')); ?></a>
    </li>
</ul>
<div id="profileInfo" class="tab-pane">
    <!-- User info -->
    <div class="card" style="margin-bottom:1rem;">
        <h3 style="margin-top:0;"><?php echo e(__('user_info')); ?></h3>
        <p><strong><?php echo e(__('username')); ?>:</strong> <?php echo e($user['username']); ?></p>
        <p><strong><?php echo e(__('full_name')); ?>:</strong> <?php echo e($user['full_name']); ?></p>
        <p><strong><?php echo e(__('email')); ?>:</strong> <?php echo e($user['email']); ?></p>
        <a href="index.php?controller=profile&action=changePassword" class="btn btn-secondary" style="margin-top:0.5rem;">
            <?php echo e(__('change_password')); ?>
        </a>
    </div>
    <!-- My tasks -->
    <div class="card" style="margin-bottom:1rem;">
        <h3 style="margin-top:0;"><?php echo e(__('my_tasks')); ?></h3>
        <?php if (empty($tasks)): ?>
            <p><?php echo e(__('no_assigned_tasks')); ?></p>
        <?php else: ?>
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th><?php echo e(__('project_name')); ?></th>
                    <th><?php echo e(__('task_name')); ?></th>
                    <th><?php echo e(__('status')); ?></th>
                    <th><?php echo e(__('due_date')); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($tasks as $t): ?>
                <tr>
                    <td><?php echo e($t['project_name']); ?></td>
                    <td><a href="index.php?controller=task&action=edit&id=<?php echo e($t['id']); ?>"><?php echo e($t['name']); ?></a></td>
                    <td><?php echo e(__($t['status'])); ?></td>
                    <td><?php echo e($t['due_date']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    <!-- My comments -->
    <div class="card">
        <h3 style="margin-top:0;"><?php echo e(__('my_comments')); ?></h3>
        <?php if (empty($comments)): ?>
            <p><?php echo e(__('no_comments')); ?></p>
        <?php else: ?>
            <ul style="list-style-type:none; padding-left:0;">
                <?php foreach ($comments as $c): ?>
                    <li style="margin-bottom:0.5rem;">
                        <strong><?php echo e(__('task')); ?>:</strong> <?php echo e($c['task_name']); ?> (<?php echo e($c['project_name']); ?>) <br>
                        <small style="color:var(--muted);"><?php echo e($c['created_at']); ?></small><br>
                        <?php echo e($c['comment']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <!-- Attachments -->
    <div class="card" style="margin-top:1rem;">
        <h3 style="margin-top:0;"><?php echo e(__('my_attachments')); ?></h3>
        <?php
        // Collect all attachments from tasks assigned to user
        $myFiles = [];
        foreach ($tasks as $t) {
            if (!empty($t['files'])) {
                foreach ($t['files'] as $f) {
                    $myFiles[] = [
                        'file_name' => $f['file_name'],
                        'file_path' => $f['file_path'],
                        'task_name' => $t['name'],
                        'project_name' => $t['project_name'],
                    ];
                }
            }
        }
        ?>
        <?php if (empty($myFiles)): ?>
            <p><?php echo e(__('no_attachments')); ?></p>
        <?php else: ?>
            <ul style="list-style-type:none; padding-left:0;">
                <?php foreach ($myFiles as $file): ?>
                    <li style="margin-bottom:0.5rem;">
                        <i class="fa-solid fa-paperclip"></i> <a href="assets/uploads/<?php echo e($file['file_path']); ?>" download><?php echo e($file['file_name']); ?></a>
                        <small style="color:var(--muted);">(<?php echo e($file['task_name']); ?> - <?php echo e($file['project_name']); ?>)</small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
<div id="notifications" class="tab-pane" style="display:none;">
    <!-- Notifications tab content: overdue, due soon, overlap groups -->
    <?php
        $overdue   = $notifications['overdue'] ?? [];
        $dueSoon   = $notifications['due_soon'] ?? [];
        $overlapGroups = $notifications['overlapGroups'] ?? [];
        function renderTable($tasks, $translator) {
            if (empty($tasks)) {
                echo '<p>' . $translator('no_tasks') . '.</p>';
                return;
            }
            echo '<table class="table table-bordered table-sm">';
            echo '<thead><tr>';
            echo '<th>' . $translator('task_name_col') . '</th>';
            echo '<th>' . $translator('project_name_col') . '</th>';
            echo '<th>' . $translator('start_date_col') . '</th>';
            echo '<th>' . $translator('due_date') . '</th>';
            echo '<th>' . $translator('status') . '</th>';
            echo '</tr></thead><tbody>';
            foreach ($tasks as $t) {
                echo '<tr>';
                echo '<td><a href="index.php?controller=task&action=edit&id=' . e($t['id']) . '">' . e($t['name']) . '</a></td>';
                echo '<td>' . e($t['project_name'] ?? '') . '</td>';
                echo '<td>' . e($t['start_date'] ?? '') . '</td>';
                echo '<td>' . e($t['due_date'] ?? '') . '</td>';
                echo '<td>' . __( $t['status'] ) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
    ?>
    <div class="mb-4">
        <h3><?php echo __('overdue'); ?></h3>
        <?php renderTable($overdue, function($k){ return __($k); }); ?>
    </div>
    <div class="mb-4">
        <h3><?php echo __('due_soon'); ?> (3 <?php echo __('days'); ?>)</h3>
        <?php renderTable($dueSoon, function($k){ return __($k); }); ?>
    </div>
    <div class="mb-4">
        <h3><?php echo __('overlap_tasks'); ?></h3>
        <?php if (empty($overlapGroups)): ?>
            <p><?php echo __('no_tasks'); ?>.</p>
        <?php else: ?>
            <?php foreach ($overlapGroups as $idx => $group): ?>
                <?php
                    $range = $group['range'];
                    try {
                        $startDateObj = new \DateTime($range[0]);
                        $endDateObj   = new \DateTime($range[1]);
                        $interval = $startDateObj->diff($endDateObj);
                        $overlapDays = $interval->days + 1;
                    } catch (\Exception $e) {
                        $overlapDays = '';
                    }
                ?>
                <div class="mb-3 p-3 border rounded bg-light shadow-sm">
                    <h5 class="mb-2" style="font-weight:600;">
                        <?php echo __('overlap_tasks'); ?> <?php echo __('from'); ?>
                        <span class="badge bg-secondary"><?php echo e($range[0]); ?></span>
                        <?php echo __('to'); ?>
                        <span class="badge bg-secondary"><?php echo e($range[1]); ?></span>
                    </h5>
                    <ul class="list-unstyled mb-2" style="margin-left:0;">
                        <?php foreach ($group['tasks'] as $gt): ?>
                            <li class="mb-1">
                                <i class="fa fa-tasks text-muted me-1"></i>
                                <a href="index.php?controller=task&action=edit&id=<?php echo e($gt['id']); ?>" class="fw-semibold text-decoration-none">
                                    <?php echo e($gt['name']); ?>
                                </a>
                                <span class="small text-muted">
                                    (<?php echo e($gt['start_date'] ?? ''); ?> – <?php echo e($gt['due_date'] ?? ''); ?>)
                                </span>
                                <?php if (!empty($gt['project_name'])): ?>
                                    <em class="small text-muted">– <?php echo e($gt['project_name']); ?></em>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if (!empty($overlapDays)): ?>
                        <div class="text-muted" style="font-size:0.85rem;">
                            <?php echo __('overlap_tasks'); ?>
                            <?php echo __('from'); ?>
                            <?php echo e($range[0]); ?>
                            <?php echo __('to'); ?>
                            <?php echo e($range[1]); ?>
                            – <strong><?php echo e($overlapDays); ?></strong> <?php echo __('days'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<script>
function showTab(event, id) {
    event.preventDefault();
    // Hide all tab panes
    document.getElementById('profileInfo').style.display = 'none';
    document.getElementById('notifications').style.display = 'none';
    // Remove active class from nav links
    const links = document.querySelectorAll('#profileTab .nav-link');
    links.forEach(function(link) { link.classList.remove('active'); });
    // Show selected pane
    document.getElementById(id).style.display = '';
    // Set active class on clicked link
    event.target.classList.add('active');
}
</script>