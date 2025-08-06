<h1 style="margin-bottom:1rem;"><?php echo e(__('edit_note') ?: 'Chỉnh sửa ghi chú'); ?></h1>

<form method="post" action="">
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="project_id" class="form-label"><?php echo e(__('project') ?: 'Dự án'); ?></label>
            <select name="project_id" id="project_id" class="form-select" onchange="onProjectChange(this.value)">
                <option value="">-- <?php echo e(__('global') ?: 'Toàn hệ thống'); ?> --</option>
                <?php foreach ($projects as $proj): ?>
                    <option value="<?php echo e($proj['id']); ?>" <?php echo ($note['project_id'] && $note['project_id'] == $proj['id']) ? 'selected' : ''; ?>><?php echo e($proj['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label for="title" class="form-label"><?php echo e(__('title') ?: 'Tiêu đề'); ?></label>
            <input type="text" name="title" id="title" class="form-control" value="<?php echo e($note['title']); ?>" placeholder="<?php echo e(__('optional') ?: 'Không bắt buộc'); ?>">
        </div>
    </div>
    <div class="mb-3">
        <label for="content" class="form-label"><?php echo e(__('content') ?: 'Nội dung'); ?></label>
        <textarea name="content" id="content" class="form-control" rows="6" required><?php echo e($note['content']); ?></textarea>
    </div>
    <div class="mb-3" id="tasks-wrapper">
        <label class="form-label"><?php echo e(__('tasks') ?: 'Công việc'); ?></label>
        <div id="tasks-container">
            <?php if (!empty($tasks)): ?>
                <div class="border rounded p-2" style="max-height:200px; overflow-y:auto;">
                    <?php foreach ($tasks as $task): ?>
                        <?php $checked = in_array($task['id'], $linkedIds ?? []) ? 'checked' : ''; ?>
                        <div class="form-check">
                            <input type="checkbox" name="task_ids[]" id="task_<?php echo e($task['id']); ?>" value="<?php echo e($task['id']); ?>" class="form-check-input" <?php echo $checked; ?>>
                            <label for="task_<?php echo e($task['id']); ?>" class="form-check-label">
                                <?php echo e($task['name']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <small class="text-muted"><?php echo e(__('select_tasks_hint') ?: 'Chọn các công việc liên quan (nếu có).'); ?></small>
            <?php else: ?>
                <p class="text-muted"><?php echo e(__('select_project_for_tasks') ?: 'Chọn một dự án để hiển thị các công việc.'); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="mt-3">
        <button type="submit" class="btn btn-primary"><?php echo e(__('save') ?: 'Lưu'); ?></button>
        <a href="index.php?controller=note&action=view&id=<?php echo e($note['id']); ?>" class="btn btn-secondary"><?php echo e(__('cancel') ?: 'Hủy'); ?></a>
    </div>
</form>

<script>
// Keep track of currently linked tasks when editing
const LINKED_TASKS = <?php echo json_encode($linkedIds ?? []); ?>;
function onProjectChange(pid) {
    const container = document.getElementById('tasks-container');
    if (!pid) {
        container.innerHTML = '<p class="text-muted"><?php echo e(__('select_project_for_tasks') ?: 'Chọn một dự án để hiển thị các công việc.'); ?></p>';
        return;
    }
    container.innerHTML = '<p class="text-muted">Loading…</p>';
    fetch('index.php?controller=note&action=tasks&project_id=' + pid)
        .then(r => r.json())
        .then(tasks => {
            if (!tasks || tasks.length === 0) {
                container.innerHTML = '<p class="text-muted"><?php echo e(__('select_tasks_hint') ?: 'Chọn các công việc liên quan (nếu có).'); ?></p>';
                return;
            }
            let html = '<div class="border rounded p-2" style="max-height:200px; overflow-y:auto;">';
            tasks.forEach(t => {
                const checked = LINKED_TASKS.includes(parseInt(t.id)) ? 'checked' : '';
                html += '<div class="form-check">' +
                    '<input type="checkbox" name="task_ids[]" id="task_' + t.id + '" value="' + t.id + '" class="form-check-input" ' + checked + '>\n' +
                    '<label for="task_' + t.id + '" class="form-check-label">' + t.name.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</label>' +
                    '</div>';
            });
            html += '</div><small class="text-muted"><?php echo e(__('select_tasks_hint') ?: 'Chọn các công việc liên quan (nếu có).'); ?></small>';
            container.innerHTML = html;
        })
        .catch(() => {
            container.innerHTML = '<p class="text-muted">Error loading tasks</p>';
        });
}
</script>