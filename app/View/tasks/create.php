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
            <div class="col-md-6 mb-2">
                <label for="name" class="form-label"><?php echo e(__('task_name')); ?></label>
                <input type="text" id="name" name="name" required class="form-control">
            </div>
            <div class="col-md-6 mb-2">
                <label for="status" class="form-label"><?php echo e(__('status')); ?></label>
                <select name="status" id="status" class="form-select">
                    <?php $statusParam = $statusParam ?? 'todo'; ?>
                    <option value="todo" <?php echo $statusParam === 'todo' ? 'selected' : ''; ?>><?php echo __('todo'); ?></option>
                    <option value="in_progress" <?php echo $statusParam === 'in_progress' ? 'selected' : ''; ?>><?php echo __('in_progress'); ?></option>
                    <option value="bug_review" <?php echo $statusParam === 'bug_review' ? 'selected' : ''; ?>><?php echo __('bug_review'); ?></option>
                    <option value="done" <?php echo $statusParam === 'done' ? 'selected' : ''; ?>><?php echo __('done'); ?></option>
                </select>
            </div>
        </div>
        <!-- Priority & tags -->
        <div class="row mb-3">
            <div class="col-md-6 mb-2">
                <label for="priority" class="form-label"><?php echo __('priority'); ?></label>
                <select name="priority" id="priority" class="form-select">
                    <option value="urgent"><?php echo __('urgent'); ?></option>
                    <option value="high"><?php echo __('high'); ?></option>
                    <option value="normal" selected><?php echo __('normal'); ?></option>
                    <option value="low"><?php echo __('low'); ?></option>
                </select>
            </div>
            <div class="col-md-6 mb-2">
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
        <!-- Attachments -->
        <div class="mb-3">
            <label for="attachments" class="form-label"><?php echo __('attachments'); ?></label>
            <input type="file" id="attachments" name="attachments[]" multiple class="form-control">
        </div>
        <div class="d-flex flex-wrap gap-2 mb-3">
            <button type="submit" class="btn btn-primary"><?php echo e(__('save')); ?></button>
            <a href="index.php?controller=task&project_id=<?php echo e($project_id); ?>&view=<?php echo e($_GET['view'] ?? 'kanban'); ?>" class="btn btn-secondary"><?php echo e(__('cancel')); ?></a>
        </div>
    </form>
</div>