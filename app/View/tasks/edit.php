<?php
// Ensure helper functions such as linkify() are available in this view.  The
// helpers file is located two directories up from this file.
require_once __DIR__ . '/../../helpers.php';
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
                <!-- Hidden field to indicate redirect to subtask creation -->
                <input type="hidden" name="redirect_to_subtask" id="redirect_to_subtask" value="0">
                <!-- Top action bar -->
                <div style="margin-bottom:1rem; display:flex; flex-wrap:wrap; gap:0.5rem; justify-content:flex-start;">
                    <button type="submit" class="btn btn-primary"><?php echo e(__('save')); ?></button>
                    <a href="index.php?controller=task&project_id=<?php echo e($task['project_id']); ?>" class="btn btn-secondary"><?php echo e(__('cancel')); ?></a>
                    <?php $subCountTop = isset($task['subtasks']) ? count($task['subtasks']) : 0; ?>
                    <button type="button" class="btn btn-danger" onclick="showDeleteModal(<?php echo $subCountTop; ?>)">Xóa</button>
                    <?php if (empty($task['parent_id'])): ?>
                        <?php
                        $subtaskUrl = 'index.php?controller=task&action=create&project_id=' . e($task['project_id']) . '&parent_id=' . e($task['id']) . '&view=' . e($returnView);
                        ?>
                        <!-- Add subtask button triggers saving current task then redirect -->
                        <button type="button" class="btn btn-secondary" onclick="prepareSubtaskCreation('<?php echo $subtaskUrl; ?>')">
                            <i class="fa-solid fa-plus"></i> <?php echo e(__('subtask')); ?>
                        </button>
                    <?php endif; ?>
                </div>
                <!-- Group: name & status -->
                <div class="row">
                    <div class="col-md-12 form-group">
                        <label for="name"><?php echo e(__('task_name')); ?></label>
                        <input type="text" id="name" name="name" value="<?php echo e($task['name']); ?>" required class="form-control">
                    </div>
                    
                </div>
                <!-- Group: priority & tags -->
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="status"><?php echo e(__('status')); ?></label>
                        <select name="status" id="status" class="form-select">
                            <option value="todo" <?php echo $task['status'] === 'todo' ? 'selected' : ''; ?>><?php echo __('todo'); ?></option>
                            <option value="in_progress" <?php echo $task['status'] === 'in_progress' ? 'selected' : ''; ?>><?php echo __('in_progress'); ?></option>
                            <option value="bug_review" <?php echo $task['status'] === 'bug_review' ? 'selected' : ''; ?>><?php echo __('bug_review'); ?></option>
                            <option value="done" <?php echo $task['status'] === 'done' ? 'selected' : ''; ?>><?php echo __('done'); ?></option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="priority"><?php echo __('priority'); ?></label>
                        <select name="priority" id="priority" class="form-select">
                            <option value="urgent" <?php echo ($task['priority'] ?? 'normal') === 'urgent' ? 'selected' : ''; ?>><?php echo __('urgent'); ?></option>
                            <option value="high" <?php echo ($task['priority'] ?? 'normal') === 'high' ? 'selected' : ''; ?>><?php echo __('high'); ?></option>
                            <option value="normal" <?php echo ($task['priority'] ?? 'normal') === 'normal' ? 'selected' : ''; ?>><?php echo __('normal'); ?></option>
                            <option value="low" <?php echo ($task['priority'] ?? 'normal') === 'low' ? 'selected' : ''; ?>><?php echo __('low'); ?></option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
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
                <!-- Group: start & due dates with inline display and edit via modal -->
                <div class="row">
                    <div class="col-md-12 form-group">
                        
                        <!-- Display formatted date range here -->
                        <div style="display: inline-flex; ">
                            <div id="dateDisplay" class="form-control" style="background:transparent; border:none; padding-left:0; min-height:2rem;"></div>
                            <button type="button" class="btn btn-link p-0 ms-1" onclick="openDateModal()" title="<?php echo e(__('edit')); ?>">
                                    <i class="fa-solid fa-pen"></i>
                            </button>
                        </div>

                        <!-- Hidden inputs to store actual dates for form submission -->
                        <input type="date" id="start_date" name="start_date" value="<?php echo e($task['start_date']); ?>" class="form-control d-none">
                        <input type="date" id="due_date" name="due_date" value="<?php echo e($task['due_date']); ?>" class="form-control d-none">
                    </div>
                </div>
                <!-- Description with inline display and edit modal -->
                <div class="form-group">
                    <label class="form-label">
                        <?php echo e(__('task_description')); ?>
                        <button type="button" class="btn btn-link p-0 ms-1" onclick="openDescriptionModal()" title="<?php echo e(__('edit')); ?>">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                    </label>
                    <!-- Display formatted description (linkified).  No border needed -->
                    <div id="descriptionDisplay" style="min-height:3rem; white-space:pre-wrap;"></div>
                    <!-- Hidden textarea to store description for submission -->
                    <textarea id="description" name="description" rows="3" class="form-control d-none"><?php echo e($task['description']); ?></textarea>
                </div>
                
                <!-- Checklist section: manage checklist items for this task -->
                <div class="form-group">
                    <label class="form-label"><?php echo __('checklist'); ?></label>
                    <div id="checklist-container">
                        <?php if (!empty($checklistItems)): ?>
                            <?php foreach ($checklistItems as $idx => $item): ?>
                                <div class="d-flex align-items-center mb-2 checklist-row">
                                    <input type="checkbox" name="checklist_done[<?php echo $idx; ?>]" class="form-check-input me-2" <?php echo $item['is_done'] ? 'checked' : ''; ?>>
                                    <input type="text" name="checklist_content[<?php echo $idx; ?>]" class="form-control" value="<?php echo e($item['content']); ?>" placeholder="<?php echo __('checklist'); ?>">
                                    <button type="button" class="btn btn-link text-danger ms-2 p-0" onclick="removeChecklistRow(this)" title="<?php echo __('delete'); ?>">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mt-1" onclick="addChecklistRow()">
                        <i class="fa-solid fa-plus"></i> <?php echo __('add_checklist_item'); ?>
                    </button>
                </div>

                <!-- Links section: allow attaching external documents (sheet/docs) to this task -->
                <div class="form-group">
                    <label class="form-label"><?php echo __('links'); ?></label>
                <div id="links-container" style="display:none;">
                    <?php if (!empty($links)): ?>
                        <?php foreach ($links as $idx => $link): ?>
                            <?php
                                // Determine icon HTML for this link based on URL
                                $iconHtml = '<i class="fa-solid fa-link"></i>';
                                if (!empty($link['url'])) {
                                    if (strpos($link['url'], 'docs.google') !== false) {
                                        $iconHtml = '<i class="fa-brands fa-google" style="color:#4285F4;"></i>';
                                    } elseif (strpos($link['url'], 'sheets.google') !== false) {
                                        $iconHtml = '<i class="fa-brands fa-google" style="color:#0F9D58;"></i>';
                                    }
                                }
                            ?>
                            <!-- Hidden row for each existing link (will still submit form fields) -->
                            <div class="row mb-2 align-items-center link-row" data-link-index="<?php echo $idx; ?>">
                                <div class="col-auto pe-0" style="min-width:1.5rem; text-align:center;">
                                    <?php echo $iconHtml; ?>
                                </div>
                                <div class="col-4">
                                    <input type="text" name="link_names[<?php echo $idx; ?>]" class="form-control" value="<?php echo e($link['name']); ?>">
                                </div>
                                <div class="col-5">
                                    <input type="url" name="link_urls[<?php echo $idx; ?>]" class="form-control" value="<?php echo e($link['url']); ?>">
                                </div>
                                <div class="col-2 d-flex align-items-center justify-content-end">
                                    <!-- Removed visible delete button here; deletion handled via display list -->
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <!-- Visible list of links -->
                <ul id="links-display" style="list-style-type:none; padding-left:0;">
                    <?php if (!empty($links)): ?>
                        <?php foreach ($links as $idx => $link): ?>
                            <?php
                                $url = $link['url'];
                                $label = $link['name'] ?: (strlen($url) > 30 ? substr($url, 0, 30) . '…' : $url);
                                $iconHtml = '<i class="fa-solid fa-link"></i>';
                                if ($url) {
                                    if (strpos($url, 'docs.google') !== false) {
                                        $iconHtml = '<i class="fa-brands fa-google" style="color:#4285F4;"></i>';
                                    } elseif (strpos($url, 'sheets.google') !== false) {
                                        $iconHtml = '<i class="fa-brands fa-google" style="color:#0F9D58;"></i>';
                                    }
                                }
                            ?>
                            <li class="mb-2 d-flex align-items-center gap-2" data-link-index="<?php echo $idx; ?>">
                                <span><?php echo $iconHtml; ?></span>
                                <a href="<?php echo e($url); ?>" target="_blank" style="text-decoration:none;"><?php echo e($label); ?></a>
                                <button type="button" class="btn btn-link text-danger p-0 ms-auto" onclick="removeLinkDisplay(<?php echo $idx; ?>)"><i class="fa-solid fa-xmark"></i></button>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                <button type="button" class="btn btn-outline-primary btn-sm mt-1" onclick="openLinkModal()">
                    <i class="fa-solid fa-plus"></i> <?php echo __('add_link'); ?>
                </button>
                </div>


                <!-- Attachments -->
                <div class="form-group">
                    <label for="attachments"><?php echo __('attachments'); ?></label>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <!-- File input: constrain width to keep button compact -->
                        <input type="file" id="attachments" name="attachments[]" multiple class="form-control" style="max-width:200px; display:inline-block;" onchange="handleAttachmentSelection(this)">
                        <!-- Preview list of selected attachments -->
                        <ul id="attachment-preview" class="d-inline-flex flex-wrap list-unstyled mb-0" style="max-height:100px; overflow-y:auto;">
                            <!-- Items added via JS -->
                        </ul>
                    </div>
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

        <!-- Dynamic form helpers for links and checklist -->
        <script>
        // Translation variables for dynamic link and checklist UI.  Using json_encode
        // ensures quotes are escaped for safe insertion into JS strings.
        const L_LINK_NAME = <?php echo json_encode(__('link_name')); ?>;
        const L_LINK_URL  = <?php echo json_encode(__('link_url')); ?>;
        const L_DELETE    = <?php echo json_encode(__('delete')); ?>;
        const L_CHECKLIST = <?php echo json_encode(__('checklist')); ?>;
        // Maintain a counter for link indices that ties hidden rows and display entries
        let linkCounter = (function() {
            const rows = document.querySelectorAll('#links-container .link-row');
            let maxIndex = 0;
            rows.forEach(function(r) {
                const idx = parseInt(r.getAttribute('data-link-index'));
                if (!isNaN(idx) && idx >= maxIndex) {
                    maxIndex = idx + 1;
                }
            });
            return maxIndex;
        })();
        // Add a new hidden link row to the container and return its unique index
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
        // Remove a hidden row by its link index
        function removeLinkRowByIndex(index) {
            const rows = document.querySelectorAll('#links-container .link-row');
            rows.forEach(function(r) {
                if (parseInt(r.getAttribute('data-link-index')) === index) {
                    r.remove();
                }
            });
        }
        // Add a new link row with specific values and update display list
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
        // Add a visible entry to the links display list
        function addLinkDisplayEntry(name, url, index) {
            const displayList = document.getElementById('links-display');
            // Determine icon based on url
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
        // Remove a visible link and corresponding hidden row
        function removeLinkDisplay(index) {
            // Remove from display list
            const displayList = document.getElementById('links-display');
            const items = displayList.querySelectorAll('li');
            items.forEach(function(item) {
                if (parseInt(item.getAttribute('data-link-index')) === index) {
                    item.remove();
                }
            });
            // Remove hidden row
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
        // Maintain an array of selected attachments; this allows deleting files before submission
        let attachmentFiles = [];
        // Handles file input changes: append new files to the list and refresh preview
        function handleAttachmentSelection(input) {
            const files = Array.from(input.files);
            files.forEach(function(file) {
                attachmentFiles.push(file);
            });
            // Clear the input so the same file can be selected again
            input.value = '';
            updateAttachmentInput();
            updateAttachmentPreview();
        }
        // Rebuild the FileList for the hidden input from our attachmentFiles array
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
        // Render the preview list showing each file name with a remove button
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
        // Remove an attachment at index, rebuild input and preview
        function removeAttachment(index) {
            attachmentFiles.splice(index, 1);
            updateAttachmentInput();
            updateAttachmentPreview();
        }
        // Initialize preview on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateAttachmentPreview();
        });

        // When user clicks to add a subtask, ensure the current task name is filled.
        function handleAddSubtask(event, url) {
            // Deprecated: replaced by prepareSubtaskCreation(); retained for backward compatibility
            event.preventDefault();
            prepareSubtaskCreation(url);
            return false;
        }

        /**
         * Prepare to create a subtask.  Ensures the current task has a name,
         * sets a hidden flag to redirect after save, then submits the form.
         * The server will save the task and redirect to the provided subtask URL.
         */
        function prepareSubtaskCreation(url) {
            const nameField = document.getElementById('name');
            if (!nameField || nameField.value.trim() === '') {
                alert('Vui lòng nhập tên công việc trước khi tạo nhiệm vụ con.');
                return;
            }
            // Set flag to redirect after saving
            const redirectField = document.getElementById('redirect_to_subtask');
            if (redirectField) {
                redirectField.value = '1';
            }
            // Submit the form
            const form = nameField.closest('form');
            if (form) {
                form.submit();
            }
        }
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

        <!-- Modals for notes and editing description/dates -->
        <?php if (!empty($availableNotes)): ?>
        <div id="attachNoteModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1050;">
            <div style="background:#fff; padding:1rem; border-radius:0.5rem; width:90%; max-width:400px; box-shadow:0 2px 8px rgba(0,0,0,0.2);">
                <h4 style="margin-top:0;"><?php echo e(__('attach_note')); ?></h4>
                <div class="mb-2">
                    <label for="modalNoteSelect" class="form-label small text-muted mb-1"><?php echo e(__('select_note')); ?></label>
                    <select id="modalNoteSelect" class="form-select">
                        <?php foreach ($availableNotes as $an): ?>
                            <?php
                                $label = $an['title'] ?: mb_substr(strip_tags($an['content']), 0, 30) . '…';
                                if (!empty($an['project_name'])) {
                                    $label .= ' - ' . $an['project_name'];
                                }
                            ?>
                            <option value="<?php echo e($an['id']); ?>"><?php echo e($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" class="btn btn-secondary" onclick="closeAttachNoteModal()"><?php echo e(__('cancel')); ?></button>
                    <button type="button" class="btn btn-primary" onclick="saveAttachNoteModal()"><?php echo e(__('attach_note')); ?></button>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <div id="createNoteModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1050;">
            <div style="background:#fff; padding:1rem; border-radius:0.5rem; width:90%; max-width:500px; box-shadow:0 2px 8px rgba(0,0,0,0.2);">
                <h4 style="margin-top:0;"><?php echo e(__('add_note')); ?></h4>
                <div class="mb-2">
                    <label for="modalNoteTitle" class="form-label small text-muted mb-1"><?php echo e(__('title')); ?></label>
                    <input type="text" id="modalNoteTitle" class="form-control">
                </div>
                <div class="mb-2">
                    <label for="modalNoteContent" class="form-label small text-muted mb-1"><?php echo e(__('content')); ?></label>
                    <!-- Formatting toolbar for new note modal -->
                    <div class="btn-group mb-1" role="group">
                        <button type="button" class="btn btn-light btn-sm" id="btn-modal-create-bold"><strong>B</strong></button>
                        <button type="button" class="btn btn-light btn-sm" id="btn-modal-create-italic"><em>I</em></button>
                        <button type="button" class="btn btn-light btn-sm" id="btn-modal-create-list">&bull; List</button>
                    </div>
                    <textarea id="modalNoteContent" class="form-control" rows="4"></textarea>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateNoteModal()"><?php echo e(__('cancel')); ?></button>
                    <button type="button" class="btn btn-primary" onclick="saveCreateNoteModal()"><?php echo e(__('save')); ?></button>
                </div>
            </div>
        </div>
        <div id="descriptionModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1050;">
            <div style="background:#fff; padding:1rem; border-radius:0.5rem; width:90%; max-width:500px; box-shadow:0 2px 8px rgba(0,0,0,0.2);">
                <h4 style="margin-top:0;"><?php echo e(__('task_description')); ?></h4>
                <div class="mb-2">
                    <textarea id="modalDescriptionContent" class="form-control" rows="5"></textarea>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" class="btn btn-secondary" onclick="closeDescriptionModal()"><?php echo e(__('cancel')); ?></button>
                    <button type="button" class="btn btn-primary" onclick="saveDescriptionModal()"><?php echo e(__('save')); ?></button>
                </div>
            </div>
        </div>
        <div id="dateModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1050;">
            <div style="background:#fff; padding:1rem; border-radius:0.5rem; width:90%; max-width:400px; box-shadow:0 2px 8px rgba(0,0,0,0.2);">
                <h4 style="margin-top:0;"><?php echo e(__('start_date')); ?> &amp; <?php echo e(__('due_date')); ?></h4>
                <div class="mb-2">
                    <label for="modalStartDate" class="form-label small text-muted mb-1"><?php echo e(__('start_date')); ?></label>
                    <input type="date" id="modalStartDate" class="form-control">
                </div>
                <div class="mb-2">
                    <label for="modalDueDate" class="form-label small text-muted mb-1"><?php echo e(__('due_date')); ?></label>
                    <input type="date" id="modalDueDate" class="form-control">
                </div>
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" class="btn btn-secondary" onclick="closeDateModal()"><?php echo e(__('cancel')); ?></button>
                    <button type="button" class="btn btn-primary" onclick="saveDateModal()"><?php echo e(__('save')); ?></button>
                </div>
            </div>
        </div>

        <script>
        // Constants
        const TASK_ID = <?php echo (int)$task['id']; ?>;
        const RETURN_VIEW = <?php echo json_encode($returnView); ?>;
        // Provide current user information for notes editing.  These constants
        // allow the frontend to determine whether the edit button should be
        // displayed for a given note (author or admin).  They are injected
        // from the server session.
        const CURRENT_USER_ID = <?php echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0; ?>;
        const IS_ADMIN = <?php echo ((isset($_SESSION['user']['role_id']) && (int)$_SESSION['user']['role_id'] === 1) ? 'true' : 'false'); ?>;

        // Helper functions for notes modals
        function openAttachNoteModal() {
            const modal = document.getElementById('attachNoteModal');
            if (modal) modal.style.display = 'flex';
        }
        function closeAttachNoteModal() {
            const modal = document.getElementById('attachNoteModal');
            if (modal) modal.style.display = 'none';
        }
        function saveAttachNoteModal() {
            const select = document.getElementById('modalNoteSelect');
            const noteId = select ? select.value : '';
            if (!noteId) {
                closeAttachNoteModal();
                return;
            }
            const formData = new FormData();
            formData.append('task_id', TASK_ID);
            formData.append('note_id', noteId);
            fetch('index.php?controller=task&action=addNoteToTaskAjax', {
                method: 'POST',
                body: formData,
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateNotesList(data.notes);
                } else {
                    alert(data.error || 'Failed to attach note');
                }
                closeAttachNoteModal();
            })
            .catch(() => {
                alert('An error occurred');
                closeAttachNoteModal();
            });
        }
        function openCreateNoteModal() {
            document.getElementById('modalNoteTitle').value = '';
            document.getElementById('modalNoteContent').value = '';
            const modal = document.getElementById('createNoteModal');
            if (modal) modal.style.display = 'flex';
        }
        function closeCreateNoteModal() {
            const modal = document.getElementById('createNoteModal');
            if (modal) modal.style.display = 'none';
        }
        function saveCreateNoteModal() {
            const title = document.getElementById('modalNoteTitle').value.trim();
            const content = document.getElementById('modalNoteContent').value.trim();
            if (title === '' && content === '') {
                closeCreateNoteModal();
                return;
            }
            const formData = new FormData();
            formData.append('title', title);
            formData.append('content', content);
            formData.append('task_id', TASK_ID);
            formData.append('project_id', <?php echo (int)$task['project_id']; ?>);
            fetch('index.php?controller=note&action=createAjax', {
                method: 'POST',
                body: formData,
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Append new note to available select for future attachments
                    updateAvailableNotesSelect(data.note);
                    // Immediately attach to this task
                    const fd = new FormData();
                    fd.append('task_id', TASK_ID);
                    fd.append('note_id', data.note.id);
                    return fetch('index.php?controller=task&action=addNoteToTaskAjax', {
                        method: 'POST',
                        body: fd,
                    });
                } else {
                    throw new Error(data.error || 'Failed to create note');
                }
            })
            .then(res => res.json())
            .then(d => {
                if (d.success) {
                    updateNotesList(d.notes);
                    closeCreateNoteModal();
                } else {
                    alert(d.error || 'Failed to attach note');
                    closeCreateNoteModal();
                }
            })
            .catch(err => {
                alert(err.message || 'An error occurred');
                closeCreateNoteModal();
            });
        }
        function updateAvailableNotesSelect(note) {
            const select = document.getElementById('modalNoteSelect');
            if (!select) return;
            const opt = document.createElement('option');
            opt.value = note.id;
            opt.textContent = note.title;
            select.appendChild(opt);
        }
        function updateNotesList(notes) {
            const list = document.getElementById('notes-list');
            const emptyMsg = document.getElementById('notes-empty-message');
            if (!list) return;
            list.innerHTML = '';
            if (!notes || notes.length === 0) {
                // Show the empty message when no notes remain
                if (emptyMsg) emptyMsg.style.display = '';
                return;
            }
            // Hide the empty message when there are notes to display
            if (emptyMsg) emptyMsg.style.display = 'none';
            notes.forEach(n => {
                const li = document.createElement('li');
                li.className = 'mb-2';
                li.setAttribute('data-note-id', n.id);
                const wrapper = document.createElement('div');
                wrapper.className = 'p-2 border rounded d-flex justify-content-between align-items-center';
                // Build link that opens the note detail modal rather than navigating away
                const a = document.createElement('a');
                a.href = '#';
                a.className = 'open-note-modal';
                // Store title, HTML content, raw content and author for later use
                a.dataset.title = n.title;
                a.dataset.content = n.content_html;
                a.dataset.raw = n.content_raw;
                a.dataset.userId = n.user_id;
                a.style.textDecoration = 'none';
                a.style.color = 'var(--link-color)';
                a.style.fontWeight = '600';
                a.style.flexGrow = '1';
                a.textContent = n.title;
                wrapper.appendChild(a);
                const btnGroup = document.createElement('div');
                btnGroup.className = 'btn-group ms-2';
                // Conditionally add edit button if the current user is the author or admin
                if (n.user_id === CURRENT_USER_ID || IS_ADMIN) {
                    const editBtn = document.createElement('button');
                    editBtn.type = 'button';
                    editBtn.className = 'btn btn-outline-primary btn-sm';
                    editBtn.title = '<?php echo e(__('edit')); ?>';
                    editBtn.innerHTML = '<i class="fa-solid fa-pencil"></i>';
                    editBtn.onclick = function() {
                        openNoteEditModal(n.id, n.title, n.content_raw);
                    };
                    btnGroup.appendChild(editBtn);
                }
                // Delete button
                const delBtn = document.createElement('button');
                delBtn.type = 'button';
                delBtn.className = 'btn btn-outline-danger btn-sm';
                delBtn.title = '<?php echo e(__('delete')); ?>';
                delBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                delBtn.onclick = function() {
                    removeNoteAjax(n.id);
                };
                btnGroup.appendChild(delBtn);
                wrapper.appendChild(btnGroup);
                li.appendChild(wrapper);
                list.appendChild(li);
            });
            // After rebuilding the list, attach click event handlers to new note links
            list.querySelectorAll('.open-note-modal').forEach(function(el) {
                el.addEventListener('click', function(ev) {
                    ev.preventDefault();
                    const title  = this.dataset.title;
                    const content= this.dataset.content;
                    const raw    = this.dataset.raw;
                    const id     = this.closest('li').getAttribute('data-note-id');
                    window.currentDetailNoteId = id;
                    window.currentDetailNoteTitle = title;
                    window.currentDetailNoteRaw = raw;
                    openNoteDetailModal(title, content);
                });
            });
        }
        function removeNoteAjax(noteId) {
            const formData = new FormData();
            formData.append('task_id', TASK_ID);
            formData.append('note_id', noteId);
            fetch('index.php?controller=task&action=removeNoteFromTaskAjax', {
                method: 'POST',
                body: formData,
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateNotesList(data.notes);
                } else {
                    alert(data.error || 'Failed to remove note');
                }
            })
            .catch(() => alert('An error occurred'));
        }

        // Description and date editing helpers
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        function linkify(text) {
            if (!text) return '';
            const urlRegex = /(https?:\/\/[^\s]+)/g;
            return escapeHtml(text).replace(urlRegex, function(url) {
                // Extract domain by removing protocol and taking substring before first '/'
                const withoutProto = url.replace(/^https?:\/\//, '');
                const domain = withoutProto.split('/')[0];
                return '<a href="' + url + '" target="_blank" rel="noopener noreferrer">(liên kết) [' + domain + ']</a>';
            });
        }
        function updateDescriptionDisplay() {
            const descField = document.getElementById('description');
            const disp = document.getElementById('descriptionDisplay');
            if (!descField || !disp) return;
            const val = descField.value || '';
            const html = linkify(val);
            if (html === '') {
                disp.innerHTML = '<em><?php echo e(__('no_description')); ?></em>';
            } else {
                disp.innerHTML = html;
            }
        }
        function updateDateDisplay() {
            const startInput = document.getElementById('start_date');
            const dueInput   = document.getElementById('due_date');
            const disp = document.getElementById('dateDisplay');
            if (!disp || !startInput || !dueInput) return;
            const s = startInput.value;
            const d = dueInput.value;
            function fmt(dateStr) {
                if (!dateStr) return '';
                const parts = dateStr.split('-');
                if (parts.length === 3) {
                    return parts[2] + '/' + parts[1] + '/' + parts[0];
                }
                return dateStr;
            }
            let range = '';
            const sDisp = fmt(s);
            const dDisp = fmt(d);
            if (s && d) {
                range = sDisp + ' → ' + dDisp;
            } else if (s) {
                range = sDisp;
            } else if (d) {
                range = '→ ' + dDisp;
            } else {
                range = '<?php echo e(__('no_dates')); ?>';
            }
            const label = '<?php echo e(__('start_date')); ?> & <?php echo e(__('due_date')); ?>:';
            disp.textContent = label + ' ' + range;
        }
        function openDescriptionModal() {
            const modal = document.getElementById('descriptionModal');
            const descField = document.getElementById('description');
            const modalTextarea = document.getElementById('modalDescriptionContent');
            if (modal && descField && modalTextarea) {
                modalTextarea.value = descField.value;
                modal.style.display = 'flex';
            }
        }
        function closeDescriptionModal() {
            const modal = document.getElementById('descriptionModal');
            if (modal) modal.style.display = 'none';
        }
        function saveDescriptionModal() {
            const descField = document.getElementById('description');
            const modalTextarea = document.getElementById('modalDescriptionContent');
            if (descField && modalTextarea) {
                descField.value = modalTextarea.value;
                updateDescriptionDisplay();
            }
            closeDescriptionModal();
        }
        function openDateModal() {
            const modal = document.getElementById('dateModal');
            const startField = document.getElementById('start_date');
            const dueField = document.getElementById('due_date');
            const modalStart = document.getElementById('modalStartDate');
            const modalDue  = document.getElementById('modalDueDate');
            if (modal && startField && dueField && modalStart && modalDue) {
                modalStart.value = startField.value;
                modalDue.value = dueField.value;
                modal.style.display = 'flex';
            }
        }
        function closeDateModal() {
            const modal = document.getElementById('dateModal');
            if (modal) modal.style.display = 'none';
        }
        function saveDateModal() {
            const startField = document.getElementById('start_date');
            const dueField   = document.getElementById('due_date');
            const modalStart = document.getElementById('modalStartDate');
            const modalDue   = document.getElementById('modalDueDate');
            if (startField && modalStart) startField.value = modalStart.value;
            if (dueField && modalDue) dueField.value = modalDue.value;
            updateDateDisplay();
            closeDateModal();
        }
        document.addEventListener('DOMContentLoaded', function() {
            updateDescriptionDisplay();
            updateDateDisplay();
        });
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
                        <p><?php echo linkify($comment['comment']); ?></p>
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

        <!-- Notes linked to this task -->
        <div class="card" style="margin-top:1rem;">
            <h3 style="margin-bottom:0.5rem;"><?php echo e(__('notes')); ?></h3>
            <!-- Notes list with container and empty message -->
            <?php if (empty($notesForTask)): ?>
                <p id="notes-empty-message"><?php echo e(__('no_notes_for_task')); ?></p>
                <ul id="notes-list" style="list-style-type:none; padding-left:0; margin-bottom:0.5rem;"></ul>
            <?php else: ?>
                <p id="notes-empty-message" style="display:none;"><?php echo e(__('no_notes_for_task')); ?></p>
                <ul id="notes-list" style="list-style-type:none; padding-left:0; margin-bottom:0.5rem;">
                    <?php foreach ($notesForTask as $note): ?>
                        <?php
                            $noteTitle = $note['title'] ?: mb_substr(strip_tags($note['content']), 0, 30) . '…';
                        ?>
                        <li class="mb-2" data-note-id="<?php echo e($note['id']); ?>">
                            <div class="p-2 border rounded d-flex justify-content-between align-items-center">
                                <?php
                                    // Prepare note content for the modal.  Convert markdown to safe HTML using
                                    // the markdown_to_html helper so that users see formatted notes when
                                    // opening the detail modal.  Also preserve the raw markdown for editing.
                                    $noteContentHtml = markdown_to_html($note['content']);
                                    $noteContentAttr = htmlspecialchars($noteContentHtml, ENT_QUOTES, 'UTF-8');
                                    $noteRawAttr = htmlspecialchars($note['content'], ENT_QUOTES, 'UTF-8');
                                ?>
                                <a href="#" class="open-note-modal" data-title="<?php echo e($noteTitle); ?>" data-content="<?php echo $noteContentAttr; ?>" data-raw="<?php echo $noteRawAttr; ?>"
                                   style="text-decoration:none; color:var(--primary); font-weight:600; flex-grow:1;">
                                    <?php echo e($noteTitle); ?>
                                </a>
                                <div class="btn-group ms-2" role="group">
                                    <button type="button" class="btn btn-outline-primary btn-sm" title="<?php echo e(__('edit')); ?>"
                                            <?php
                                                // Use single quotes around the title and content parameters to avoid
                                                // breaking the onclick attribute.  Escape any single quotes in the
                                                // strings to ensure valid JavaScript syntax.
                                                $jsTitle   = str_replace("'", "\\'", $noteTitle);
                                                $jsContent = str_replace("'", "\\'", $note['content']);
                                            ?>
                                            onclick="openNoteEditModal(<?php echo e($note['id']); ?>, '<?php echo $jsTitle; ?>', '<?php echo $jsContent; ?>')">
                                        <i class="fa-solid fa-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" title="<?php echo e(__('delete')); ?>" onclick="removeNoteAjax(<?php echo e($note['id']); ?>)">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <!-- Buttons to create or attach notes via modal -->
            <div class="d-flex flex-wrap gap-2 mt-1">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="openCreateNoteModal()">
                    <i class="fa-solid fa-plus"></i> <?php echo e(__('add_note')); ?>
                </button>
                <?php if (!empty($availableNotes)): ?>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="openAttachNoteModal()">
                    <i class="fa-solid fa-link"></i> <?php echo e(__('attach_note')); ?>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div> <!-- end of task-edit-container -->

<!-- Modal for viewing note details.  When a user clicks a note title in
     the notes list, this modal displays the full note content. -->
<div id="noteDetailModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1050;">
    <div style="background:#fff; padding:1rem; border-radius:0.5rem; width:90%; max-width:600px; box-shadow:0 2px 8px rgba(0,0,0,0.2);">
        <h4 id="noteDetailTitle" style="margin-top:0;"></h4>
        <div id="noteDetailContent" style="max-height:400px; overflow-y:auto; margin-top:0.5rem;"></div>
        <div class="text-end mt-3">
            <!-- Edit button triggers editing of the currently viewed note -->
            <button type="button" class="btn btn-primary me-2" onclick="editDetailNote()"><?php echo __('edit') ?: 'Sửa'; ?></button>
            <button type="button" class="btn btn-secondary" onclick="closeNoteDetailModal()">Đóng</button>
        </div>
    </div>
</div>

<script>
// Modal functions for viewing notes.  These functions are defined
// outside of DOMContentLoaded so they can be invoked inline from
// event handlers.  The openNoteDetailModal function accepts the
// note title and HTML content (already escaped) and injects them
// into the modal.  The modal is shown by setting its display to flex.
function openNoteDetailModal(title, content) {
    const modal = document.getElementById('noteDetailModal');
    document.getElementById('noteDetailTitle').textContent = title;
    document.getElementById('noteDetailContent').innerHTML = content;
    modal.style.display = 'flex';
}

// Formatting toolbar handlers for create note modal
document.addEventListener('DOMContentLoaded', function() {
    const modalBoldBtn  = document.getElementById('btn-modal-create-bold');
    const modalItalicBtn= document.getElementById('btn-modal-create-italic');
    const modalListBtn  = document.getElementById('btn-modal-create-list');
    const modalTextarea = document.getElementById('modalNoteContent');
    function modalApplyFormat(format) {
        if (!modalTextarea) return;
        const start = modalTextarea.selectionStart;
        const end   = modalTextarea.selectionEnd;
        const selected = modalTextarea.value.slice(start, end);
        let replacement = selected;
        if (format === 'bold') {
            replacement = '**' + selected + '**';
        } else if (format === 'italic') {
            replacement = '_' + selected + '_';
        } else if (format === 'list') {
            const lines = selected.split(/\n/);
            replacement = lines.map(function(l) { return l ? '- ' + l : ''; }).join('\n');
        }
        modalTextarea.setRangeText(replacement, start, end, 'end');
        modalTextarea.focus();
    }
    if (modalBoldBtn) modalBoldBtn.addEventListener('click', function() { modalApplyFormat('bold'); });
    if (modalItalicBtn) modalItalicBtn.addEventListener('click', function() { modalApplyFormat('italic'); });
    if (modalListBtn) modalListBtn.addEventListener('click', function() { modalApplyFormat('list'); });
});
function closeNoteDetailModal() {
    const modal = document.getElementById('noteDetailModal');
    modal.style.display = 'none';
}
// Attach event listeners to note titles after the DOM loads.  Use
// delegation to capture clicks on dynamically added notes.  The
// elements have the class .open-note-modal and data-title/data-content
// attributes for the modal.
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.open-note-modal').forEach(function(el) {
        el.addEventListener('click', function(ev) {
            ev.preventDefault();
            // Fetch note attributes for detail and potential editing
            const title = this.getAttribute('data-title');
            const content = this.getAttribute('data-content');
            const raw    = this.getAttribute('data-raw');
            const id     = this.closest('li').getAttribute('data-note-id');
            // Store globally for editing later
            window.currentDetailNoteId = id;
            window.currentDetailNoteTitle = title;
            window.currentDetailNoteRaw = raw;
            openNoteDetailModal(title, content);
        });
    });
});

// Function called from Edit button inside the note detail modal.  Closes
// the detail modal and opens the editing modal with raw content.
function editDetailNote() {
    closeNoteDetailModal();
    if (!window.currentDetailNoteId) return;
    openNoteEditModal(window.currentDetailNoteId, window.currentDetailNoteTitle, window.currentDetailNoteRaw);
}
</script>

<!-- Modal for editing note -->
<div id="noteEditModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1050;">
    <div style="background:#fff; padding:1rem; border-radius:0.5rem; width:90%; max-width:600px; box-shadow:0 2px 8px rgba(0,0,0,0.2);">
        <h4 style="margin-top:0;"><?php echo __('edit_note') ?: 'Sửa ghi chú'; ?></h4>
        <form id="noteEditForm">
            <div class="mb-2">
                <label for="noteEditTitle" class="form-label small"><?php echo __('title') ?: 'Tiêu đề'; ?></label>
                <input type="text" id="noteEditTitle" name="title" class="form-control form-control-sm">
            </div>
            <div class="mb-2">
                <label for="noteEditContent" class="form-label small"><?php echo __('content') ?: 'Nội dung'; ?></label>
                <div class="btn-group mb-1" role="group">
                    <button type="button" class="btn btn-light btn-sm" data-format="bold"><strong>B</strong></button>
                    <button type="button" class="btn btn-light btn-sm" data-format="italic"><em>I</em></button>
                    <button type="button" class="btn btn-light btn-sm" data-format="list">&bull; List</button>
                </div>
                <textarea id="noteEditContent" name="content" class="form-control form-control-sm" rows="6"></textarea>
            </div>
            <div class="text-end">
                <button type="button" class="btn btn-secondary btn-sm" id="noteEditCancel"><?php echo __('cancel') ?: 'Hủy bỏ'; ?></button>
                <button type="submit" class="btn btn-primary btn-sm"><?php echo __('save') ?: 'Lưu'; ?></button>
            </div>
        </form>
    </div>
</div>

<script>
// Variables and functions for note editing modal in task edit view
let currentEditingNoteId = null;
function openNoteEditModal(id, title, content) {
    currentEditingNoteId = id;
    document.getElementById('noteEditTitle').value = title || '';
    document.getElementById('noteEditContent').value = content || '';
    document.getElementById('noteEditModal').style.display = 'flex';
}
document.getElementById('noteEditCancel').addEventListener('click', function() {
    document.getElementById('noteEditModal').style.display = 'none';
});
// Formatting buttons
document.querySelectorAll('#noteEditModal [data-format]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const format = this.getAttribute('data-format');
        const textarea = document.getElementById('noteEditContent');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selected = textarea.value.slice(start, end);
        let replacement = selected;
        if (format === 'bold') {
            replacement = '**' + selected + '**';
        } else if (format === 'italic') {
            replacement = '_' + selected + '_';
        } else if (format === 'list') {
            // Prepend a bullet if not already at the beginning of a line
            const lines = selected.split(/\n/);
            replacement = lines.map(function(l) { return l ? '- ' + l : ''; }).join('\n');
        }
        textarea.setRangeText(replacement, start, end, 'end');
        textarea.focus();
    });
});
// Submit handler to save note via AJAX
document.getElementById('noteEditForm').addEventListener('submit', function(ev) {
    ev.preventDefault();
    const title = document.getElementById('noteEditTitle').value;
    const content = document.getElementById('noteEditContent').value;
    if (!currentEditingNoteId) return;
    fetch('index.php?controller=note&action=edit&id=' + currentEditingNoteId, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'title=' + encodeURIComponent(title) + '&content=' + encodeURIComponent(content)
    }).then(function() {
        // Reload to reflect changes
        location.reload();
    });
});
</script>

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