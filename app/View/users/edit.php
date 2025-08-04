<h1 style="margin-bottom:1rem;">Chỉnh sửa người dùng</h1>
<div class="card" style="max-width:600px;">
    <form method="post" action="">
        <div class="form-group">
            <label for="username"><?php echo e(__('username')); ?></label>
            <input type="text" id="username" name="username" value="<?php echo e($user['username']); ?>" disabled>
        </div>
        <div class="form-group">
            <label for="password"><?php echo e(__('password')); ?> (Để trống nếu không đổi)</label>
            <input type="password" id="password" name="password">
        </div>
        <div class="form-group">
            <label for="full_name">Họ tên</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo e($user['full_name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="text" id="email" name="email" value="<?php echo e($user['email']); ?>">
        </div>
        <div class="form-group">
            <label for="role_id"><?php echo e(__('roles')); ?></label>
            <select name="role_id" id="role_id">
                <?php foreach ($roles as $role): ?>
                    <option value="<?php echo e($role['id']); ?>" <?php echo $user['role_id'] == $role['id'] ? 'selected' : ''; ?>><?php echo e($role['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Permissions section (hidden for admin role) -->
        <fieldset id="permissions_fieldset" class="form-group" style="border:1px solid #e5e7eb; padding:0.75rem; margin-bottom:1rem;">
            <legend style="font-size:1rem;"><?php echo e(__('permissions')); ?></legend>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="perm_create_project" name="perm_create_project" <?php echo (!empty($currentPerms['create_project'])) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="perm_create_project"><?php echo e(__('perm_create_project')); ?></label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="perm_edit_project" name="perm_edit_project" <?php echo (!empty($currentPerms['edit_project'])) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="perm_edit_project"><?php echo e(__('perm_edit_project')); ?></label>
            </div>
            <!-- Project access checkboxes -->
            <div class="form-group" style="margin-top:0.75rem;">
                <label><?php echo e(__('project_access')); ?></label>
                <?php if (!empty($projects)): ?>
                    <?php foreach ($projects as $proj): ?>
                        <?php $checked = !empty($currentPerms['access_projects']) && in_array($proj['id'], $currentPerms['access_projects']); ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="access_project_<?php echo e($proj['id']); ?>" name="access_projects[]" value="<?php echo e($proj['id']); ?>" <?php echo $checked ? 'checked' : ''; ?>>
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
            // Hide the permissions fieldset when admin role is selected during edit
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
                // Initial toggle
                togglePerms();
            })();
        </script>
        <button type="submit" class="btn btn-primary"><?php echo e(__('save')); ?></button>
        <a href="index.php?controller=user" class="btn btn-secondary"><?php echo e(__('cancel')); ?></a>
    </form>
</div>