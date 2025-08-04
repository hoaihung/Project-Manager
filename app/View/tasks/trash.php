<h1 style="margin-bottom:1rem;">Thùng rác</h1>
<p class="text-muted">Danh sách các công việc đã bị xóa (dạng mềm). Bạn có thể khôi phục hoặc xóa vĩnh viễn.</p>

<?php if (empty($tasks)): ?>
    <div class="alert alert-info">Không có công việc nào đã bị xóa.</div>
<?php else: ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th><?php echo e(__('task_name_col')); ?></th>
                <th><?php echo e(__('project_name_col')); ?></th>
                <th>Người xóa</th>
                <th>Thời gian xóa</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks as $task): ?>
                <?php
                    $info = $deletionInfo[$task['id']] ?? null;
                    $deletedById = $info['deleted_by'] ?? null;
                    $deletedByName = $deletedByNames[$deletedById] ?? '';
                    $timestamp = $info['timestamp'] ?? '';
                ?>
                <tr>
                    <td><?php echo e($task['name']); ?></td>
                    <td><?php echo e($task['project_name']); ?></td>
                    <td><?php echo e($deletedByName); ?></td>
                    <td><?php echo e($timestamp); ?></td>
                    <td>
                        <a href="index.php?controller=task&action=restore&id=<?php echo e($task['id']); ?>" class="btn btn-sm btn-success" onclick="return confirm('Bạn có chắc muốn khôi phục công việc này?');">Khôi phục</a>
                        <a href="index.php?controller=task&action=forceDelete&id=<?php echo e($task['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa vĩnh viễn công việc này? Không thể hoàn tác.');">Xóa vĩnh viễn</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>