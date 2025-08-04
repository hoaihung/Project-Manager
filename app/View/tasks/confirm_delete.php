<h1 style="margin-bottom:1rem;">
    <?php echo e(__('delete_task')); ?>: <?php echo e($task['name']); ?>
</h1>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?php echo e($_SESSION['error']); ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="card" style="max-width:600px;">
    <form method="post" action="">
        <input type="hidden" name="id" value="<?php echo e($task['id']); ?>">
        <p><?php echo e(__('delete_task_prompt')); ?></p>
        <?php if (!$isSubtask): ?>
            <!-- Offer option to delete subtasks only when deleting a parent task -->
            <div class="form-check" style="margin-bottom:0.5rem;">
                <input class="form-check-input" type="checkbox" id="delete_subtasks" name="delete_subtasks">
                <label class="form-check-label" for="delete_subtasks">
                    <?php echo e(__('delete_subtasks_option')); ?>
                </label>
            </div>
        <?php endif; ?>
        <div class="form-check" style="margin-bottom:0.5rem;">
            <input class="form-check-input" type="checkbox" id="confirm_delete" name="confirm_delete">
            <label class="form-check-label" for="confirm_delete">
                <?php echo e(__('confirm_delete_option')); ?>
            </label>
        </div>
        <button type="submit" id="btn-delete" class="btn btn-danger" disabled>
            <?php echo e(__('delete')); ?>
        </button>
        <a href="index.php?controller=task&project_id=<?php echo e($task['project_id']); ?>" class="btn btn-secondary">
            <?php echo e(__('cancel')); ?>
        </a>
    </form>
</div>

<script>
    // Enable delete button only when confirm checkbox is checked
    (function() {
        const confirmCheck = document.getElementById('confirm_delete');
        const deleteBtn = document.getElementById('btn-delete');
        function toggleButton() {
            deleteBtn.disabled = !confirmCheck.checked;
        }
        confirmCheck.addEventListener('change', toggleButton);
    })();
</script>