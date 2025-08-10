<h1 style="margin-bottom:1rem;"><?php echo e(__('profile_title')); ?></h1>
<!-- Profile page: display user information, tasks, comments and attachments.  The notifications
     tab has been removed in favour of a dedicated notifications page. -->
<div id="profileInfo">
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
        <div class="table-wrapper">
        <table class="table table-bordered table-sm mb-0">
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
        </table>
        </div>
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
<!-- Notifications content removed: notifications now live on their own page (ProfileController::notifications). -->
<script>
// Maintain a no‑op showTab function so that any leftover references
// do not throw errors.  The old tab functionality has been removed.
function showTab(event, id) {
    if (event) event.preventDefault();
}
</script>