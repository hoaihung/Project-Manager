<?php
// Render a single note.  Expect $note and $tasks variables.
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 style="margin:0; font-size:1.3rem;">
            <?php echo e($note['title'] ?: __('note') ?: 'Ghi chú'); ?>
        </h2>
        <div class="d-flex gap-1">
            <?php if ($note['user_id'] == ($_SESSION['user_id'] ?? 0) || (($_SESSION['user']['role_id'] ?? 0) === 1)): ?>
                <a href="index.php?controller=note&action=edit&id=<?php echo e($note['id']); ?>" class="btn btn-sm btn-outline-secondary" title="<?php echo e(__('edit')); ?>">
                    <i class="fa-solid fa-pencil"></i>
                </a>
                <a href="index.php?controller=note&action=delete&id=<?php echo e($note['id']); ?>" class="btn btn-sm btn-outline-danger" title="<?php echo e(__('delete')); ?>" onclick="return confirm('<?php echo e(__('confirm_delete_note') ?: 'Bạn có chắc muốn xóa ghi chú này?'); ?>');">
                    <i class="fa-solid fa-trash"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="mb-2 text-muted">
            <span class="me-3"><strong><?php echo e(__('project') ?: 'Dự án'); ?>:</strong> <?php echo e($note['project_name'] ?: __('global') ?: 'Toàn hệ thống'); ?></span>
            <span class="me-3"><strong><?php echo e(__('author') ?: 'Tác giả'); ?>:</strong> <?php echo e($note['author']); ?></span>
            <span><strong><?php echo e(__('created_at') ?: 'Ngày tạo'); ?>:</strong> <?php echo e(date('d/m/Y H:i', strtotime($note['created_at']))); ?></span>
        </div>
        <div class="mb-4" style="border:1px solid var(--border); border-radius:0.25rem; padding:0.75rem; background-color:var(--surface);">
            <?php echo markdown_to_html($note['content']); ?>
        </div>
        <?php if (!empty($tasks)): ?>
            <h4 style="font-size:1rem;"><?php echo e(__('linked_tasks') ?: 'Công việc liên kết'); ?></h4>
            <ul>
                <?php foreach ($tasks as $t): ?>
                    <li><a href="index.php?controller=task&action=edit&id=<?php echo e($t['id']); ?>" class="link-primary"><?php echo e($t['name']); ?></a> <span class="text-muted small">(<?php echo e($t['project_name']); ?>)</span></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <div class="card-footer text-end">
        <a href="index.php?controller=note<?php echo $note['project_id'] ? '&project_id=' . e($note['project_id']) : ''; ?>" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> <?php echo e(__('back')); ?>
        </a>
    </div>
</div>