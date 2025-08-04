<div class="card" style="max-width:400px;margin:0 auto;">
    <h2 style="text-align:center;margin-bottom:1rem;"><?php echo e(__('login')); ?></h2>
    <?php if (!empty($error)): ?>
        <div style="color: var(--danger); margin-bottom:1rem; text-align:center;">
            <?php echo e($error); ?>
        </div>
    <?php endif; ?>
    <form method="post" action="">
        <div class="form-group">
            <label for="username"><?php echo e(__('username')); ?></label>
            <input type="text" name="username" id="username" required>
        </div>
        <div class="form-group">
            <label for="password"><?php echo e(__('password')); ?></label>
            <input type="password" name="password" id="password" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;">
            <?php echo e(__('login')); ?>
        </button>
    </form>
</div>