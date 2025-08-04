<h1 style="margin-bottom:1rem;">Thêm người dùng</h1>
<div class="card" style="max-width:600px;">
    <form method="post" action="">
        <div class="form-group">
            <label for="username"><?php echo e(__('username')); ?></label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password"><?php echo e(__('password')); ?></label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="full_name">Họ tên</label>
            <input type="text" id="full_name" name="full_name" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="text" id="email" name="email">
        </div>
        <div class="form-group">
            <label for="role_id"><?php echo e(__('roles')); ?></label>
            <select name="role_id" id="role_id">
                <?php foreach ($roles as $role): ?>
                    <option value="<?php echo e($role['id']); ?>"><?php echo e($role['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Permissions (only apply to non-admin roles).  Admins automatically have all privileges. -->
        <fieldset id="permissions_fieldset" class="form-group" style="border:1px solid #e5e7eb; padding:0.75rem; margin-bottom:1rem;">
            <legend style="font-size:1rem;"><?php echo e(__('permissions')); ?></legend>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="perm_create_project" name="perm_create_project">
                <label class="form-check-label" for="perm_create_project"><?php echo e(__('perm_create_project')); ?></label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="perm_edit_project" name="perm_edit_project">
                <label class="form-check-label" for="perm_edit_project"><?php echo e(__('perm_edit_project')); ?></label>
            </div>
            <!-- Project access: choose projects this user can view/edit. Use checkboxes for each project -->
            <div class="form-group" style="margin-top:0.75rem;">
                <label><?php echo e(__('project_access')); ?></label>
                <?php if (!empty($projects)): ?>
                    <?php foreach ($projects as $proj): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="access_project_<?php echo e($proj['id']); ?>" name="access_projects[]" value="<?php echo e($proj['id']); ?>">
                            <label class="form-check-label" for="access_project_<?php echo e($proj['id']); ?>">
                                <?php echo e($proj['name']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted small"><?php echo e(__('no_projects')); ?></p>
                <?php endif; ?>
            </div>
        </fieldset>
        <script>
            // Hide the permissions fieldset when the admin role is selected
            (function() {
                const roleSelect = document.getElementById('role_id');
                const permFieldset = document.getElementById('permissions_fieldset');
                function togglePerms() {
                    if (roleSelect.value === '1') {
                        permFieldset.style.display = 'none';
                    } else {
                        permFieldset.style.display = '';
                    }
                }
                roleSelect.addEventListener('change', togglePerms);
                // Initial toggle on page load
                togglePerms();
            })();
        </script>
        <button type="submit" class="btn btn-primary"><?php echo e(__('save')); ?></button>
        <a href="index.php?controller=user" class="btn btn-secondary"><?php echo e(__('cancel')); ?></a>
    </form>
</div>