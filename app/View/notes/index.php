<h1 style="margin-bottom:1rem;"><?php echo e(__('notes') ?: 'Ghi chú'); ?></h1>

<?php
// Ensure helper functions such as linkify() are available in this view.
// __DIR__ points to app/View/notes so go two levels up to reach app/helpers.php.
require_once __DIR__ . '/../../helpers.php';
?>

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
    <!-- Use .table-wrapper to wrap the notes table for consistent padding and border -->
    <div class="table-wrapper">
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
                <?php
                    // Determine the display title for the note.  If the title
                    // field is blank, use the first 30 characters of the
                    // content (stripped of tags) as a fallback, appending an
                    // ellipsis.  This value will be used both in the table
                    // cell and as the modal title.  Use mb_substr to handle
                    // multibyte characters correctly.
                    $displayTitle = $n['title'] ?: mb_substr(strip_tags($n['content']), 0, 30) . '…';
                    // Prepare the note content for the modal.  Run through
                    // linkify() to convert URLs to safe anchor tags.  Then
                    // escape double quotes and other HTML entities for use
                    // inside a data attribute.  Note: ENT_QUOTES ensures both
                    // single and double quotes are encoded.
                    $noteHtml = linkify($n['content']);
                    $noteAttr = htmlspecialchars($noteHtml, ENT_QUOTES, 'UTF-8');
                    // Also prepare a short preview of the note for the table
                    // cell (strip tags and trim to 50 characters).
                    $preview = mb_substr(strip_tags($n['content']), 0, 50) . '…';
                ?>
                <td><?php echo e($displayTitle); ?></td>
                <td><?php echo e($preview); ?></td>
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
                        <a href="#" class="btn btn-outline-secondary btn-sm" title="<?php echo e(__('view')); ?>"
                           data-note-view
                           data-title="<?php echo e($displayTitle); ?>"
                           data-content="<?php echo $noteAttr; ?>">
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

<!-- Modal for displaying note details -->
<div id="noteDetailModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1050;">
    <div style="background:#fff; padding:1rem; border-radius:0.5rem; width:90%; max-width:600px; box-shadow:0 2px 8px rgba(0,0,0,0.2);">
        <h4 id="noteDetailTitle" style="margin-top:0;"></h4>
        <div id="noteDetailContent" style="max-height:400px; overflow-y:auto; margin-top:0.5rem;"></div>
        <div class="text-end mt-3">
            <button type="button" class="btn btn-secondary" onclick="closeNoteDetailModal()">Đóng</button>
        </div>
    </div>
</div>

<script>
// Open the note detail modal with provided title and content.  The
// content is expected to be HTML (already safely rendered on the
// server).  Use textContent for the title to avoid HTML injection.
function openNoteDetailModal(title, content) {
    const modal = document.getElementById('noteDetailModal');
    document.getElementById('noteDetailTitle').textContent = title;
    document.getElementById('noteDetailContent').innerHTML = content;
    modal.style.display = 'flex';
}
function closeNoteDetailModal() {
    const modal = document.getElementById('noteDetailModal');
    modal.style.display = 'none';
}
// Attach click handlers for note view links.  Because PHP outputs the
// table rows before this script tag, querySelectorAll can find them.
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-note-view]').forEach(function(el) {
        el.addEventListener('click', function(ev) {
            ev.preventDefault();
            const title = this.getAttribute('data-title');
            const content = this.getAttribute('data-content');
            openNoteDetailModal(title, content);
        });
    });
});
</script>