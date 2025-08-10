<h1 style="margin-bottom:1rem;"><?php echo e(__('create_note') ?: 'Tạo ghi chú'); ?></h1>

<form method="post" action="">
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="project_id" class="form-label"><?php echo e(__('project') ?: 'Dự án'); ?></label>
            <select name="project_id" id="project_id" class="form-select" onchange="onProjectChange(this.value)">
                <option value="">-- <?php echo e(__('global') ?: 'Toàn hệ thống'); ?> --</option>
                <?php foreach ($projects as $proj): ?>
                    <option value="<?php echo e($proj['id']); ?>" <?php echo ($projectId && $projectId == $proj['id']) ? 'selected' : ''; ?>><?php echo e($proj['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label for="title" class="form-label"><?php echo e(__('title') ?: 'Tiêu đề'); ?></label>
            <input type="text" name="title" id="title" class="form-control" placeholder="<?php echo e(__('optional') ?: 'Không bắt buộc'); ?>">
        </div>
    </div>
    <div class="mb-3">
        <label for="content" class="form-label"><?php echo e(__('content') ?: 'Nội dung'); ?></label>
        <!-- Formatting toolbar for note content -->
        <div class="btn-group mb-1" role="group">
            <button type="button" class="btn btn-light btn-sm" id="btn-create-bold"><strong>B</strong></button>
            <button type="button" class="btn btn-light btn-sm" id="btn-create-italic"><em>I</em></button>
            <button type="button" class="btn btn-light btn-sm" id="btn-create-list">&bull; List</button>
        </div>
        <textarea name="content" id="content" class="form-control" rows="6" placeholder="Markdown ..." required></textarea>
    </div>
    <div class="mb-3" id="tasks-wrapper">
        <label class="form-label"><?php echo e(__('tasks') ?: 'Công việc'); ?></label>
        <div id="tasks-container">
            <?php if (!empty($tasks)): ?>
                <div class="border rounded p-2" style="max-height:200px; overflow-y:auto;">
                    <?php foreach ($tasks as $task): ?>
                        <?php $checked = !empty($preSelectedTaskIds) && in_array($task['id'], $preSelectedTaskIds) ? 'checked' : ''; ?>
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
        <a href="index.php?controller=note<?php echo $projectId ? '&project_id=' . e($projectId) : ''; ?>" class="btn btn-secondary"><?php echo e(__('cancel') ?: 'Hủy'); ?></a>
    </div>
</form>

<script>
// Preserve pre-selected tasks (e.g. when creating note from a task)
const PRE_SELECTED_TASKS = <?php echo json_encode($preSelectedTaskIds ?? []); ?>;
// Handle project selection without reloading: fetch tasks via AJAX
function onProjectChange(pid) {
    const container = document.getElementById('tasks-container');
    if (!pid) {
        container.innerHTML = '<p class="text-muted"><?php echo e(__('select_project_for_tasks') ?: 'Chọn một dự án để hiển thị các công việc.'); ?></p>';
        return;
    }
    // Show loading indicator
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
                const checked = PRE_SELECTED_TASKS.includes(parseInt(t.id)) ? 'checked' : '';
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

// Formatting toolbar handlers for create note page
document.addEventListener('DOMContentLoaded', function() {
    const boldBtn  = document.getElementById('btn-create-bold');
    const italicBtn= document.getElementById('btn-create-italic');
    const listBtn  = document.getElementById('btn-create-list');
    const textarea = document.getElementById('content');
    function applyFormat(format) {
        const start = textarea.selectionStart;
        const end   = textarea.selectionEnd;
        const selected = textarea.value.slice(start, end);
        let replacement = selected;
        if (format === 'bold') {
            replacement = '**' + selected + '**';
        } else if (format === 'italic') {
            replacement = '_' + selected + '_';
        } else if (format === 'list') {
            const lines = selected.split(/\n/);
            replacement = lines.map(function(l) { return l ? '- ' + l : ''; }).join('\n');
        }
        textarea.setRangeText(replacement, start, end, 'end');
        textarea.focus();
    }
    if (boldBtn) boldBtn.addEventListener('click', function() { applyFormat('bold'); });
    if (italicBtn) italicBtn.addEventListener('click', function() { applyFormat('italic'); });
    if (listBtn) listBtn.addEventListener('click', function() { applyFormat('list'); });
});
</script>