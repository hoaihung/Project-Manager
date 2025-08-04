<?php
    // Determine return view from query or default to kanban
    $returnView = $_GET['view'] ?? 'kanban';
    $backUrl = 'index.php?controller=task&project_id=' . e($task['project_id']) . '&view=' . e($returnView);
?>
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
    <h1 style="margin:0;"><?php echo e(__('edit_task')); ?></h1>
    <a href="<?php echo $backUrl; ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left"></i> <?php echo e(__('back')); ?>
    </a>
</div>
<div class="task-edit-container" style="display:flex; gap:1rem; align-items:flex-start; flex-wrap:wrap;">
    <!-- Left column: edit form -->
    <div class="task-form" style="flex:3; min-width:300px;">
        <div class="card">
            <form method="post" action="" enctype="multipart/form-data">
                <!-- Top action bar -->
                <div style="margin-bottom:1rem; display:flex; flex-wrap:wrap; gap:0.5rem; justify-content:flex-start;">
                    <button type="submit" class="btn btn-primary"><?php echo e(__('save')); ?></button>
                    <a href="index.php?controller=task&project_id=<?php echo e($task['project_id']); ?>" class="btn btn-secondary"><?php echo e(__('cancel')); ?></a>
                    <?php $subCountTop = isset($task['subtasks']) ? count($task['subtasks']) : 0; ?>
                    <button type="button" class="btn btn-danger" onclick="showDeleteModal(<?php echo $subCountTop; ?>)">Xóa</button>
                    <?php if (empty($task['parent_id'])): ?>
                        <a href="index.php?controller=task&action=create&project_id=<?php echo e($task['project_id']); ?>&parent_id=<?php echo e($task['id']); ?>&view=<?php echo e($returnView); ?>" class="btn btn-secondary">
                            <i class="fa-solid fa-plus"></i> <?php echo e(__('subtask')); ?>
                        </a>
                    <?php endif; ?>
                </div>
                <!-- Group: name & status -->
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="name"><?php echo e(__('task_name')); ?></label>
                        <input type="text" id="name" name="name" value="<?php echo e($task['name']); ?>" required class="form-control">
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="status"><?php echo e(__('status')); ?></label>
                        <select name="status" id="status" class="form-select">
                            <option value="todo" <?php echo $task['status'] === 'todo' ? 'selected' : ''; ?>><?php echo __('todo'); ?></option>
                            <option value="in_progress" <?php echo $task['status'] === 'in_progress' ? 'selected' : ''; ?>><?php echo __('in_progress'); ?></option>
                            <option value="bug_review" <?php echo $task['status'] === 'bug_review' ? 'selected' : ''; ?>><?php echo __('bug_review'); ?></option>
                            <option value="done" <?php echo $task['status'] === 'done' ? 'selected' : ''; ?>><?php echo __('done'); ?></option>
                        </select>
                    </div>
                </div>
                <!-- Group: priority & tags -->
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="priority"><?php echo __('priority'); ?></label>
                        <select name="priority" id="priority" class="form-select">
                            <option value="urgent" <?php echo ($task['priority'] ?? 'normal') === 'urgent' ? 'selected' : ''; ?>><?php echo __('urgent'); ?></option>
                            <option value="high" <?php echo ($task['priority'] ?? 'normal') === 'high' ? 'selected' : ''; ?>><?php echo __('high'); ?></option>
                            <option value="normal" <?php echo ($task['priority'] ?? 'normal') === 'normal' ? 'selected' : ''; ?>><?php echo __('normal'); ?></option>
                            <option value="low" <?php echo ($task['priority'] ?? 'normal') === 'low' ? 'selected' : ''; ?>><?php echo __('low'); ?></option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                    <label for="tags"><?php echo __('tags'); ?></label>
                    <input type="text" id="tags" name="tags" value="<?php echo e($task['tags']); ?>" placeholder="<?php echo __('tags_placeholder'); ?>" class="form-control">
                    </div>
                </div>
                <!-- Group: assignee & parent -->
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="form-label"><?php echo e(__('assigned_to')); ?></label>
                        <div class="border rounded p-2" style="max-height:150px; overflow-y:auto;">
                            <?php foreach ($users as $user): ?>
                                <?php $checked = in_array($user['id'], $assignedUserIds ?? []) ? 'checked' : ''; ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="assignees[]" id="assign_<?php echo e($user['id']); ?>" value="<?php echo e($user['id']); ?>" <?php echo $checked; ?>>
                                    <label class="form-check-label" for="assign_<?php echo e($user['id']); ?>">
                                        <?php echo e($user['full_name']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="parent_id"><?php echo __('subtask_of'); ?></label>
                        <select name="parent_id" id="parent_id" class="form-select" <?php echo isset($hasSubtasks) && $hasSubtasks ? 'disabled' : ''; ?> >
                            <option value="">-- None --</option>
                            <?php if (isset($parentOptions)): ?>
                                <?php foreach ($parentOptions as $parent): ?>
                                    <option value="<?php echo e($parent['id']); ?>" <?php echo ($task['parent_id'] ?? '') == $parent['id'] ? 'selected' : ''; ?>><?php echo e($parent['name']); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <!-- Group: start & due dates -->
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="start_date"><?php echo e(__('start_date')); ?></label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo e($task['start_date']); ?>" class="form-control">
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="due_date"><?php echo e(__('due_date')); ?></label>
                        <input type="date" id="due_date" name="due_date" value="<?php echo e($task['due_date']); ?>" class="form-control">
                    </div>
                </div>
                <!-- Description -->
                <div class="form-group">
                    <label for="description"><?php echo e(__('task_description')); ?></label>
                    <textarea id="description" name="description" rows="3" class="form-control"><?php echo e($task['description']); ?></textarea>
                </div>
                <!-- Attachments -->
                <div class="form-group">
                    <label for="attachments"><?php echo __('attachments'); ?></label>
                    <input type="file" id="attachments" name="attachments[]" multiple class="form-control">
                </div>

                <!-- Subtasks reorder list -->
                <?php if (!empty($subtasks)): ?>
                <div class="form-group">
                    <label style="font-weight:600;"><?php echo __('reorder_subtasks'); ?></label>
                    <div id="subtaskContainer" style="border:1px solid var(--border); border-radius:0.25rem; padding:0.5rem; min-height:1rem;">
                        <?php foreach ($subtasks as $st): ?>
                        <div class="subtask-sort-item" data-id="<?php echo e($st['id']); ?>" draggable="true" style="background-color:var(--surface); border:1px solid var(--border); border-radius:0.25rem; padding:0.3rem; margin-bottom:0.3rem; display:flex; align-items:center; gap:0.5rem;">
                            <!-- Drag handle (left) -->
                            <span class="fa-solid fa-grip-lines" style="cursor:grab;"></span>
                            <!-- Subtask name clickable to edit -->
                            <a href="index.php?controller=task&action=edit&id=<?php echo e($st['id']); ?>&view=<?php echo e($returnView); ?>" style="flex-grow:1; text-decoration:none; color:inherit;">
                                <?php echo e($st['name']); ?>
                            </a>
                            <!-- Date range display -->
                            <span style="font-size:0.75rem; color:var(--muted); white-space:nowrap;">
                                <?php if (!empty($st['start_date'])): ?>
                                    <i class="fa-solid fa-calendar-day"></i> <?php echo e($st['start_date']); ?>
                                <?php endif; ?>
                                <?php if (!empty($st['due_date'])): ?>
                                    → <?php echo e($st['due_date']); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <small class="text-muted">Kéo các item để thay đổi thứ tự. Thứ tự mới được lưu tự động.</small>
                </div>
                <?php endif; ?>
                <div style="margin-top:1rem; display:flex; flex-wrap:wrap; gap:0.5rem;">
                    <button type="submit" class="btn btn-primary"><?php echo e(__('save')); ?></button>
                    <a href="index.php?controller=task&project_id=<?php echo e($task['project_id']); ?>" class="btn btn-secondary"><?php echo e(__('cancel')); ?></a>
                    <!-- Delete button only available here -->
                    <?php $subCount = isset($task['subtasks']) ? count($task['subtasks']) : 0; ?>
                    <!-- Delete button triggers custom modal confirmation -->
                    <button type="button" class="btn btn-danger" onclick="showDeleteModal(<?php echo $subCount; ?>)">Xóa</button>
                    <!-- Quick link to create a subtask of this task (only for top-level) -->
                    <?php if (empty($task['parent_id'])): ?>
                        <a href="index.php?controller=task&action=create&project_id=<?php echo e($task['project_id']); ?>&parent_id=<?php echo e($task['id']); ?>&view=<?php echo e($returnView); ?>" class="btn btn-secondary">
                            <i class="fa-solid fa-plus"></i> <?php echo e(__('subtask')); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <!-- Right column: activities and comments -->
    <div class="task-side" style="flex:2; min-width:260px;">
        <!-- Activity feed with collapsible content -->
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; cursor:pointer;" onclick="toggleActivity()">
                <h3 style="margin:0.5rem 0;">Hoạt động</h3>
                <span id="activityToggle" style="font-size:1rem; padding:0 0.5rem;">
                    <i class="fa-solid fa-chevron-down"></i>
                </span>
            </div>
            <div id="activityContent" style="display:none; max-height:300px; overflow-y:auto; border-top:1px solid #e5e7eb; padding-top:0.5rem;">
                <?php
                $logModel = new \app\Model\Log();
                $activities = $logModel->getByTask($task['id'], 50);
                ?>
                <?php if (empty($activities)): ?>
                    <p style="padding:0.5rem;">Chưa có hoạt động.</p>
                <?php else: ?>
                    <?php foreach ($activities as $activity): ?>
                        <div style="margin-bottom:0.5rem; padding-bottom:0.5rem; border-bottom:1px solid #e5e7eb;">
                            <strong><?php echo e($activity['username']); ?></strong>
                            <small style="color: var(--muted);"><?php echo e($activity['created_at']); ?></small>
                            <p><?php echo e($activity['action']); ?> - <?php echo e($activity['details']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <script>
        function toggleActivity() {
            const content = document.getElementById('activityContent');
            const icon = document.getElementById('activityToggle').querySelector('i');
            if (content.style.display === 'none' || content.style.display === '') {
                content.style.display = 'block';
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                content.style.display = 'none';
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }
        </script>
        <!-- Comments -->
        <div class="card" style="margin-top:1rem; max-height:300px; overflow-y:auto;">
            <h3 style="margin-bottom:0.5rem;">Bình luận</h3>
            <?php
            // Load existing comments
            $commentModel = new \app\Model\Comment();
            $comments = $commentModel->getByTask($task['id']);
            ?>
            <?php if (empty($comments)): ?>
                <p>Chưa có bình luận.</p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div style="margin-bottom:0.5rem; padding-bottom:0.5rem; border-bottom:1px solid #e5e7eb;">
                        <strong><?php echo e($comment['author']); ?></strong>
                        <small style="color: var(--muted);"><?php echo e($comment['created_at']); ?></small>
                        <p><?php echo e($comment['comment']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <!-- Add comment form -->
            <form method="post" action="index.php?controller=task&action=comment" style="margin-top:0.5rem;">
                <input type="hidden" name="task_id" value="<?php echo e($task['id']); ?>">
                <div class="form-group">
                    <label for="comment"><?php echo __('add_comment'); ?></label>
                    <textarea id="comment" name="comment" rows="2" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Gửi</button>
            </form>
        </div>

        <!-- Attachments -->
        <div class="card" style="margin-top:1rem;">
            <h3 style="margin-bottom:0.5rem;">Đính kèm</h3>
            <?php
            $fileModel = new \app\Model\File();
            $attachments = $fileModel->getByTask($task['id']);
            ?>
            <?php if (empty($attachments)): ?>
                <p>Chưa có tập tin đính kèm.</p>
            <?php else: ?>
                <ul style="list-style-type:none; padding-left:0;">
                    <?php foreach ($attachments as $file): ?>
                        <li style="margin-bottom:0.25rem; display:flex; justify-content:space-between; align-items:center;">
                            <span>
                                <a href="<?php echo e($file['file_path']); ?>" download><?php echo e($file['file_name']); ?></a>
                            </span>
                            <!-- Delete attachment link -->
                            <a href="index.php?controller=task&action=deleteFile&id=<?php echo e($file['id']); ?>&task_id=<?php echo e($task['id']); ?>" class="btn btn-danger btn-sm" style="padding:0.2rem 0.4rem; font-size:0.75rem;" onclick="return confirm('Bạn có chắc muốn xóa tập tin này?');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
</div> <!-- end of task-edit-container -->

<!-- Delete Confirmation Modal -->
<?php $subCount = isset($subtasks) ? count($subtasks) : 0; ?>
<div id="deleteModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1050;">
    <div style="background:#fff; padding:1.5rem; border-radius:0.5rem; max-width:500px; width:90%; box-shadow:0 0 10px rgba(0,0,0,0.2);">
        <h3 style="margin-top:0;"><?php echo e(__('delete_task')); ?></h3>
        <p id="deleteMessage" style="margin-bottom:0.75rem;"></p>
        <!-- Display delete subtask option only if this task has subtasks -->
        <?php if ($subCount > 0): ?>
        <div style="margin-bottom:0.5rem;">
            <input type="checkbox" id="deleteSubtasksCheckbox"> <label for="deleteSubtasksCheckbox"><?php echo e(__('delete_subtasks_option')); ?></label>
        </div>
        <?php endif; ?>
        <div style="margin-bottom:0.75rem;">
            <input type="checkbox" id="confirmDeleteCheckbox"> <label for="confirmDeleteCheckbox"><?php echo e(__('confirm_delete_option')); ?></label>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:0.5rem;">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()"><?php echo e(__('cancel')); ?></button>
            <a id="confirmDeleteBtn" href="#" class="btn btn-danger" style="pointer-events:none; opacity:0.5;">
                <?php echo e(__('delete')); ?>
            </a>
        </div>
    </div>
</div>

<script>
function showDeleteModal(subCount) {
    const modal = document.getElementById('deleteModal');
    const msg = document.getElementById('deleteMessage');
    msg.textContent = subCount && subCount > 0 ?
        '<?php echo e(__('delete_task_prompt_with_subtasks')); ?>'.replace(':count', subCount) :
        '<?php echo e(__('delete_task_prompt')); ?>';
    // Reset checkboxes and disable button
    const deleteSubs = document.getElementById('deleteSubtasksCheckbox');
    const confirmChk = document.getElementById('confirmDeleteCheckbox');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    if (deleteSubs) deleteSubs.checked = false;
    confirmChk.checked = false;
    confirmBtn.style.opacity = 0.5;
    confirmBtn.style.pointerEvents = 'none';
    // Set link to deletion url (default delete_subtasks=0)
    const baseUrl = 'index.php?controller=task&action=delete&id=<?php echo $task['id']; ?>';
    confirmBtn.href = baseUrl;
    modal.style.display = 'flex';
    // Listen for confirm checkbox changes to enable/disable button
    confirmChk.onchange = function() {
        if (confirmChk.checked) {
            confirmBtn.style.opacity = 1;
            confirmBtn.style.pointerEvents = 'auto';
        } else {
            confirmBtn.style.opacity = 0.5;
            confirmBtn.style.pointerEvents = 'none';
        }
    };
    // Listen for both checkboxes to update deletion url
    function updateHref() {
        const deleteFlag = deleteSubs && deleteSubs.checked ? 1 : 0;
        confirmBtn.href = baseUrl + '&delete_subtasks=' + deleteFlag;
    }
    if (deleteSubs) {
        deleteSubs.onchange = updateHref;
    }
    // call once to set initial href
    updateHref();
}
function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}
</script>

<!-- Script to enable drag & drop reordering of subtasks -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('subtaskContainer');
    if (!container) return;
    let draggedItem = null;
    container.querySelectorAll('.subtask-sort-item').forEach(item => {
        item.addEventListener('dragstart', ev => {
            draggedItem = item;
            item.classList.add('dragging');
        });
        item.addEventListener('dragend', ev => {
            item.classList.remove('dragging');
            draggedItem = null;
            // After drop, collect order and send to server
            const ids = Array.from(container.querySelectorAll('.subtask-sort-item')).map(el => el.getAttribute('data-id'));
            fetch('index.php?controller=task&action=reorderSubtasks', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ parent_id: <?php echo $task['id']; ?>, order: ids })
            });
        });
    });
    container.addEventListener('dragover', ev => {
        ev.preventDefault();
        const after = getDragAfterElement(container, ev.clientY);
        if (after == null) {
            container.appendChild(draggedItem);
        } else {
            container.insertBefore(draggedItem, after);
        }
    });
    function getDragAfterElement(container, y) {
        const items = [...container.querySelectorAll('.subtask-sort-item:not(.dragging)')];
        return items.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }
});
</script>