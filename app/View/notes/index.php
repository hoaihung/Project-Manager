<h1 style="margin-bottom:1rem;"><?php echo e(__('notes') ?: 'Ghi chú'); ?></h1>

<!-- Filter by project: show dropdown to select project or all -->
<form method="get" class="mb-3" style="max-width:300px;">
    <input type="hidden" name="controller" value="note">
    <input type="hidden" name="action" value="index">
    <label for="project_filter" class="form-label small text-muted mb-1"><?php echo e(__('project') ?: 'Dự án'); ?></label>
    <select id="project_filter" name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">
            -- <?php echo e(__('all_projects') ?: 'Tất cả dự án'); ?> --
        </option>
        <?php foreach ($projects as $proj): ?>
            <option value="<?php echo e($proj['id']); ?>" <?php echo ((int)$currentProjectId === (int)$proj['id']) ? 'selected' : ''; ?>><?php echo e($proj['name']); ?></option>
        <?php endforeach; ?>
    </select>
</form>

<div class="mb-3">
    <a href="index.php?controller=note&action=create<?php echo $currentProjectId ? '&project_id=' . e($currentProjectId) : ''; ?>" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-plus"></i> <?php echo e(__('create_note') ?: 'Tạo ghi chú'); ?>
    </a>
</div>

<?php if (empty($notes)): ?>
    <p><?php echo e(__('no_notes') ?: 'Không có ghi chú.'); ?></p>
<?php else: ?>
    <div class="table-responsive bg-white border rounded p-3">
    <table class="table table-sm table-hover mb-0">
        <thead class="table-light">
        <tr>
            <th style="width:20%;"><?php echo e(__('title') ?: 'Tiêu đề'); ?></th>
            <th style="width:30%;"><?php echo e(__('content') ?: 'Nội dung'); ?></th>
            <th style="width:15%;"><?php echo e(__('project') ?: 'Dự án'); ?></th>
            <th style="width:20%;"><?php echo e(__('scope') ?: 'Scope'); ?></th>
            <th style="width:10%;"><?php echo e(__('author') ?: 'Tác giả'); ?></th>
            <th style="width:5%;"><?php echo e(__('actions') ?: 'Thao tác'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($notes as $n): ?>
            <tr>
                <td><?php echo e($n['title'] ?: substr($n['content'], 0, 30) . '...'); ?></td>
                <td><?php echo e(substr(strip_tags($n['content']), 0, 50)); ?>...</td>
                <td><?php echo e($n['project_name'] ?: __('global') ?: 'Toàn hệ thống'); ?></td>
                <td>
                    <?php
                    // Determine scope of the note
                    // Global note: no project and no tasks
                    $noteTasks = [];
                    // We need note ID; accessible via $n['id']
                    $noteTasks = (new \app\Model\Note())->getTasks($n['id']);
                    if (empty($n['project_id']) && empty($noteTasks)) {
                        echo 'Global';
                    } elseif (!empty($noteTasks)) {
                        // Show task names (first 2)
                        $names = array_map(function ($t) { return $t['name']; }, $noteTasks);
                        $list = implode(', ', array_slice($names, 0, 2));
                        if (count($names) > 2) {
                            $list .= ', …';
                        }
                        echo 'Task: ' . e($list);
                    } else {
                        echo 'Project';
                    }
                    ?>
                </td>
                <td><?php echo e($n['author']); ?></td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="index.php?controller=note&action=view&id=<?php echo e($n['id']); ?>" class="btn btn-outline-secondary btn-sm" title="<?php echo e(__('view')); ?>">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <?php if ($n['user_id'] == $_SESSION['user_id'] || ($_SESSION['user']['role_id'] ?? 0) === 1): ?>
                            <a href="index.php?controller=note&action=edit&id=<?php echo e($n['id']); ?>" class="btn btn-outline-primary btn-sm" title="<?php echo e(__('edit')); ?>">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                            <a href="index.php?controller=note&action=delete&id=<?php echo e($n['id']); ?>" class="btn btn-outline-danger btn-sm" title="<?php echo e(__('delete')); ?>" onclick="return confirm('<?php echo e(__('confirm_delete_note') ?: 'Bạn có chắc muốn xóa ghi chú này?'); ?>');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>