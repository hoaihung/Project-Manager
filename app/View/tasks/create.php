<?php
    // Determine return view from query or default to kanban
    $retView = $_GET['view'] ?? 'kanban';
    $backUrl = 'index.php?controller=task&project_id=' . e($project_id) . '&view=' . e($retView);
?>
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
    <h1 style="margin:0;"><?php echo e(__('create_task')); ?></h1>
    <a href="<?php echo $backUrl; ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left"></i> <?php echo e(__('back')); ?>
    </a>
</div>
<div class="card">
    <form method="post" action="" enctype="multipart/form-data">
        <!-- Action bar -->
        <div class="mb-3 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary"><?php echo e(__('save')); ?></button>
            <a href="index.php?controller=task&project_id=<?php echo e($project_id); ?>&view=<?php echo e($_GET['view'] ?? 'kanban'); ?>" class="btn btn-secondary"><?php echo e(__('cancel')); ?></a>
        </div>
        <!-- Name & status -->
        <div class="row mb-3">
            <div class="col-md-12 mb-2">
                <label for="name" class="form-label"><?php echo e(__('task_name')); ?></label>
                <input type="text" id="name" name="name" required class="form-control">
            </div>
            
        </div>
        <!-- Priority & tags -->
        <div class="row mb-3">
            <div class="col-md-4 mb-2">
                <label for="status" class="form-label"><?php echo e(__('status')); ?></label>
                <select name="status" id="status" class="form-select">
                    <?php $statusParam = $statusParam ?? 'todo'; ?>
                    <option value="todo" <?php echo $statusParam === 'todo' ? 'selected' : ''; ?>><?php echo __('todo'); ?></option>
                    <option value="in_progress" <?php echo $statusParam === 'in_progress' ? 'selected' : ''; ?>><?php echo __('in_progress'); ?></option>
                    <option value="bug_review" <?php echo $statusParam === 'bug_review' ? 'selected' : ''; ?>><?php echo __('bug_review'); ?></option>
                    <option value="done" <?php echo $statusParam === 'done' ? 'selected' : ''; ?>><?php echo __('done'); ?></option>
                </select>
            </div>
            <div class="col-md-4 mb-2">
                <label for="priority" class="form-label"><?php echo __('priority'); ?></label>
                <select name="priority" id="priority" class="form-select">
                    <option value="urgent"><?php echo __('urgent'); ?></option>
                    <option value="high"><?php echo __('high'); ?></option>
                    <option value="normal" selected><?php echo __('normal'); ?></option>
                    <option value="low"><?php echo __('low'); ?></option>
                </select>
            </div>
            <div class="col-md-4 mb-2">
                <label for="tags" class="form-label"><?php echo __('tags'); ?></label>
                <input type="text" id="tags" name="tags" placeholder="<?php echo __('tags_placeholder'); ?>" class="form-control">
            </div>
        </div>
        <!-- Assignee & parent -->
        <div class="row mb-3">
            <div class="col-md-6 mb-2">
                <label class="form-label"><?php echo e(__('assigned_to')); ?></label>
                <div class="border rounded p-2" style="max-height:150px; overflow-y:auto;">
                    <?php foreach ($users as $user): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="assignees[]" id="assign_<?php echo e($user['id']); ?>" value="<?php echo e($user['id']); ?>">
                            <label class="form-check-label" for="assign_<?php echo e($user['id']); ?>">
                                <?php echo e($user['full_name']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-6 mb-2">
                <?php if ($parent_id): ?>
                    <input type="hidden" name="parent_id" value="<?php echo e($parent_id); ?>">
                <?php else: ?>
                    <label for="parent_id" class="form-label"><?php echo __('subtask_of'); ?></label>
                    <select name="parent_id" id="parent_id" class="form-select">
                        <option value="">-- None --</option>
                        <?php if (isset($parentOptions)): ?>
                            <?php foreach ($parentOptions as $parent): ?>
                                <option value="<?php echo e($parent['id']); ?>"><?php echo e($parent['name']); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                <?php endif; ?>
            </div>
        </div>
        <!-- Dates -->
        <div class="row mb-3">
            <div class="col-md-6 mb-2">
                <label for="start_date" class="form-label"><?php echo e(__('start_date')); ?></label>
                <input type="date" id="start_date" name="start_date" value="<?php echo e(date('Y-m-d')); ?>" class="form-control">
            </div>
            <div class="col-md-6 mb-2">
                <label for="due_date" class="form-label"><?php echo e(__('due_date')); ?></label>
                <input type="date" id="due_date" name="due_date" class="form-control">
            </div>
        </div>
        <!-- Description -->
        <div class="mb-3">
            <label for="description" class="form-label"><?php echo e(__('task_description')); ?></label>
            <textarea id="description" name="description" rows="3" class="form-control"></textarea>
        </div>
        <!-- Checklist section: manage checklist items for this task -->
        <div class="mb-3">
            <label class="form-label"><?php echo __('checklist'); ?></label>
            <div id="checklist-container">
                <!-- Initially empty; rows are added dynamically -->
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm mt-1" onclick="addChecklistRow()">
                <i class="fa-solid fa-plus"></i> <?php echo __('add_checklist_item'); ?>
            </button>
        </div>
        

        <!-- Links section: allow attaching external documents (sheet/docs) to this task -->
        <div class="mb-3">
            <label class="form-label"><?php echo __('links'); ?></label>
            <div id="links-container" style="display:none;">
                <!-- Hidden container for link inputs -->
            </div>
            <!-- Visible list for displaying links -->
            <ul id="links-display" style="list-style-type:none; padding-left:0;"></ul>
            <button type="button" class="btn btn-outline-primary btn-sm mt-1" onclick="openLinkModal()">
                <i class="fa-solid fa-plus"></i> <?php echo __('add_link'); ?>
            </button>
        </div>


        <!-- Existing notes selection: allow attaching previously created notes to this new task -->
        <?php if (!empty($availableNotes)): ?>
        <div class="mb-3">
            <label class="form-label"><?php echo __('existing_notes'); ?></label>
            <div class="border rounded p-2" style="max-height:150px; overflow-y:auto;">
                <?php foreach ($availableNotes as $n): ?>
                    <?php
                        $lbl = $n['title'] ?: mb_substr(strip_tags($n['content']), 0, 30) . '…';
                        if (!empty($n['project_name'])) {
                            $lbl .= ' - ' . $n['project_name'];
                        }
                    ?>
                    <div class="form-check">
                        <input type="checkbox" name="existing_notes[]" value="<?php echo e($n['id']); ?>" id="note_<?php echo e($n['id']); ?>" class="form-check-input">
                        <label for="note_<?php echo e($n['id']); ?>" class="form-check-label">
                            <?php echo e($lbl); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- Attachments -->
        <div class="mb-3">
            <label for="attachments" class="form-label"><?php echo __('attachments'); ?></label>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <input type="file" id="attachments" name="attachments[]" multiple class="form-control" style="max-width:200px; display:inline-block;" onchange="handleAttachmentSelection(this)">
                <ul id="attachment-preview" class="d-inline-flex flex-wrap list-unstyled mb-0" style="max-height:100px; overflow-y:auto;"></ul>
            </div>
        </div>
        <?php endif; ?>
        <div class="d-flex flex-wrap gap-2 mb-3">
            <button type="submit" class="btn btn-primary"><?php echo e(__('save')); ?></button>
            <?php if (!empty($parent_id)): ?>
                <!-- When creating a subtask, show both Cancel and Back to parent; both point to parent edit page -->
                <a href="index.php?controller=task&action=edit&id=<?php echo e($parent_id); ?>&view=<?php echo e($_GET['view'] ?? 'kanban'); ?>" class="btn btn-secondary">
                    <?php echo e(__('cancel')); ?>
                </a>
                <a href="index.php?controller=task&action=edit&id=<?php echo e($parent_id); ?>&view=<?php echo e($_GET['view'] ?? 'kanban'); ?>" class="btn btn-link">
                    <?php echo e(__('back_to_parent_task')); ?>
                </a>
            <?php else: ?>
                <a href="index.php?controller=task&project_id=<?php echo e($project_id); ?>&view=<?php echo e($_GET['view'] ?? 'kanban'); ?>" class="btn btn-secondary">
                    <?php echo e(__('cancel')); ?>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
// Translation variables for dynamic link and checklist UI. Using json_encode
// ensures any quotes in translations are escaped for safe JavaScript usage.
const L_LINK_NAME = <?php echo json_encode(__('link_name')); ?>;
const L_LINK_URL  = <?php echo json_encode(__('link_url')); ?>;
const L_DELETE    = <?php echo json_encode(__('delete')); ?>;
const L_CHECKLIST = <?php echo json_encode(__('checklist')); ?>;
// Maintain a counter for link indices that tie hidden rows and display entries
let linkCounter = 0;
// Add a new hidden link row and return its index
function addLinkRow() {
    const container = document.getElementById('links-container');
    const idx = container.querySelectorAll('.link-row').length;
    const row = document.createElement('div');
    row.className = 'row mb-2 align-items-center link-row';
    const linkIndex = linkCounter++;
    row.setAttribute('data-link-index', linkIndex);
    row.innerHTML =
        '<div class="col-auto pe-0" style="min-width:1.5rem; text-align:center;">' +
            '<i class="fa-solid fa-link"></i>' +
        '</div>' +
        '<div class="col-4">' +
            '<input type="text" name="link_names[' + idx + ']" class="form-control" placeholder="' + L_LINK_NAME + '">' +
        '</div>' +
        '<div class="col-5">' +
            '<input type="url" name="link_urls[' + idx + ']" class="form-control" placeholder="' + L_LINK_URL + '">' +
        '</div>' +
        '<div class="col-2 d-flex align-items-center justify-content-end"></div>';
    container.appendChild(row);
    return linkIndex;
}
// Remove hidden row by index
function removeLinkRowByIndex(index) {
    const rows = document.querySelectorAll('#links-container .link-row');
    rows.forEach(function(r) {
        if (parseInt(r.getAttribute('data-link-index')) === index) {
            r.remove();
        }
    });
}
// Add link row with values and display entry
function addLinkRowWithValues(name, url) {
    const linkIndex = addLinkRow();
    const container = document.getElementById('links-container');
    const rows = container.querySelectorAll('.link-row');
    const last = rows[rows.length - 1];
    if (last) {
        const nameInput = last.querySelector('input[name^="link_names"]');
        const urlInput  = last.querySelector('input[name^="link_urls"]');
        if (nameInput) nameInput.value = name;
        if (urlInput) urlInput.value = url;
    }
    addLinkDisplayEntry(name, url, linkIndex);
}
// Add visible entry to display list
function addLinkDisplayEntry(name, url, index) {
    const displayList = document.getElementById('links-display');
    let iconHtml = '<i class="fa-solid fa-link"></i>';
    if (url) {
        if (url.includes('docs.google')) {
            iconHtml = '<i class="fa-brands fa-google" style="color:#4285F4;"></i>';
        } else if (url.includes('sheets.google')) {
            iconHtml = '<i class="fa-brands fa-google" style="color:#0F9D58;"></i>';
        }
    }
    const label = name || (url.length > 30 ? url.substring(0, 30) + '…' : url);
    const li = document.createElement('li');
    li.className = 'mb-2 d-flex align-items-center gap-2';
    li.setAttribute('data-link-index', index);
    li.innerHTML =
        '<span>' + iconHtml + '</span>' +
        '<a href="' + url + '" target="_blank" style="text-decoration:none;">' + label + '</a>' +
        '<button type="button" class="btn btn-link text-danger p-0 ms-auto" onclick="removeLinkDisplay(' + index + ')"><i class="fa-solid fa-xmark"></i></button>';
    displayList.appendChild(li);
}
// Remove a visible entry and hidden row
function removeLinkDisplay(index) {
    const displayList = document.getElementById('links-display');
    const items = displayList.querySelectorAll('li');
    items.forEach(function(item) {
        if (parseInt(item.getAttribute('data-link-index')) === index) {
            item.remove();
        }
    });
    removeLinkRowByIndex(index);
}
// Add a new checklist item row
function addChecklistRow() {
    const container = document.getElementById('checklist-container');
    const idx = container.querySelectorAll('.checklist-row').length;
    const wrapper = document.createElement('div');
    wrapper.className = 'd-flex align-items-center mb-2 checklist-row';
    wrapper.innerHTML =
        '<input type="checkbox" name="checklist_done[' + idx + ']" class="form-check-input me-2">' +
        '<input type="text" name="checklist_content[' + idx + ']" class="form-control" placeholder="' + L_CHECKLIST + '">' +
        '<button type="button" class="btn btn-link text-danger ms-2 p-0" onclick="removeChecklistRow(this)" title="' + L_DELETE + '">' +
            '<i class="fa-solid fa-trash"></i>' +
        '</button>';
    container.appendChild(wrapper);
}
// Remove a checklist item row
function removeChecklistRow(btn) {
    const row = btn.closest('.checklist-row');
    if (row) {
        row.remove();
    }
}

// --------- Attachment preview handling ---------
// Maintain list of selected attachments for create page
let attachmentFiles = [];
function handleAttachmentSelection(input) {
    const files = Array.from(input.files);
    files.forEach(function(file) {
        attachmentFiles.push(file);
    });
    // Reset input so it can receive additional selections
    input.value = '';
    updateAttachmentInput();
    updateAttachmentPreview();
}
// Rebuild FileList from attachmentFiles
function updateAttachmentInput() {
    const dt = new DataTransfer();
    attachmentFiles.forEach(function(file) {
        dt.items.add(file);
    });
    const fileInput = document.getElementById('attachments');
    if (fileInput) {
        fileInput.files = dt.files;
    }
}
// Render attachments preview
function updateAttachmentPreview() {
    const preview = document.getElementById('attachment-preview');
    if (!preview) return;
    preview.innerHTML = '';
    attachmentFiles.forEach(function(file, idx) {
        const li = document.createElement('li');
        li.className = 'me-2 mb-1 d-flex align-items-center';
        li.innerHTML =
            '<span class="badge bg-secondary me-1">' + file.name + '</span>' +
            '<button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeAttachment(' + idx + ')">' +
                '<i class="fa-solid fa-xmark"></i>' +
            '</button>';
        preview.appendChild(li);
    });
}
// Remove selected file by index
function removeAttachment(index) {
    attachmentFiles.splice(index, 1);
    updateAttachmentInput();
    updateAttachmentPreview();
}
document.addEventListener('DOMContentLoaded', function() {
    updateAttachmentPreview();
});
</script>

<!-- Simple modal for adding/editing links -->
<div id="linkModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1050;">
    <div style="background:#fff; padding:1rem; border-radius:0.5rem; width:90%; max-width:400px; box-shadow:0 2px 8px rgba(0,0,0,0.2);">
        <h4 style="margin-top:0;"><?php echo e(__('add_link')); ?></h4>
        <div class="mb-2">
            <label for="modalLinkName" class="form-label small text-muted mb-1"><?php echo e(__('link_name')); ?></label>
            <input type="text" id="modalLinkName" class="form-control">
        </div>
        <div class="mb-2">
            <label for="modalLinkUrl" class="form-label small text-muted mb-1"><?php echo e(__('link_url')); ?></label>
            <input type="url" id="modalLinkUrl" class="form-control">
        </div>
        <div class="d-flex justify-content-end gap-2 mt-3">
            <button type="button" class="btn btn-secondary" onclick="closeLinkModal()"><?php echo e(__('cancel')); ?></button>
            <button type="button" class="btn btn-primary" onclick="saveLinkFromModal()"><?php echo e(__('save')); ?></button>
        </div>
    </div>
</div>
<script>
function openLinkModal() {
    document.getElementById('modalLinkName').value = '';
    document.getElementById('modalLinkUrl').value = '';
    document.getElementById('linkModal').style.display = 'flex';
}
function closeLinkModal() {
    document.getElementById('linkModal').style.display = 'none';
}
function saveLinkFromModal() {
    const name = document.getElementById('modalLinkName').value.trim();
    const url  = document.getElementById('modalLinkUrl').value.trim();
    if (name !== '' || url !== '') {
        addLinkRowWithValues(name, url);
    }
    closeLinkModal();
}
</script>