<h1 style="margin-bottom:1rem;"><?php echo e(__('create_project')); ?></h1>
<div class="card" style="max-width:600px;">
    <form method="post" action="">
        <div class="form-group">
            <label for="name"><?php echo e(__('project_name')); ?></label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="description"><?php echo e(__('project_description')); ?></label>
            <textarea id="description" name="description" rows="3"></textarea>
        </div>
        <div class="form-group">
            <label for="status"><?php echo e(__('status')); ?></label>
            <select name="status" id="status">
                <option value="new">New</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>
        </div>
        <div class="form-group">
            <label for="start_date"><?php echo e(__('start_date')); ?></label>
            <input type="date" id="start_date" name="start_date" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="form-group">
            <label for="end_date"><?php echo e(__('end_date')); ?></label>
            <input type="date" id="end_date" name="end_date">
        </div>
        <!-- Member selection: allow choosing which users can access this project -->
        <div class="form-group">
            <label><?php echo e(__('project_members')); ?></label>
            <div style="max-height:200px; overflow-y:auto; border:1px solid #e5e7eb; padding:0.5rem;">
                <div class="form-check">
                    <input type="checkbox" id="all_users" name="members[]" value="all" class="form-check-input">
                    <label for="all_users" class="form-check-label"><?php echo e(__('all_users')); ?></label>
                </div>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $u): ?>
                        <div class="form-check">
                            <input type="checkbox" id="member_<?php echo $u['id']; ?>" name="members[]" value="<?php echo $u['id']; ?>" class="form-check-input">
                            <label for="member_<?php echo $u['id']; ?>" class="form-check-label"><?php echo e($u['full_name'] ?: $u['username']); ?></label>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <small class="form-text text-muted"><?php echo e(__('select_members_hint')); ?></small>
        </div>
        <button type="submit" class="btn btn-primary"><?php echo e(__('save')); ?></button>
        <a href="index.php?controller=project" class="btn btn-secondary"><?php echo e(__('cancel')); ?></a>
    </form>
</div>