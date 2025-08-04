<h1 style="margin-bottom:1rem;"><?php echo e(__('users')); ?></h1>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th><?php echo e(__('username')); ?></th>
                <th>Họ tên</th>
                <th>Email</th>
                <th><?php echo e(__('roles')); ?></th>
                <th><?php echo e(__('actions')); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo e($user['id']); ?></td>
                <td><?php echo e($user['username']); ?></td>
                <td><?php echo e($user['full_name']); ?></td>
                <td><?php echo e($user['email']); ?></td>
                <td><?php echo e($user['role_name']); ?></td>
                <td>
                    <a href="index.php?controller=user&action=edit&id=<?php echo e($user['id']); ?>" class="btn btn-secondary">Edit</a>
                    <a href="index.php?controller=user&action=delete&id=<?php echo e($user['id']); ?>" class="btn btn-danger" onclick="return confirm('Xóa người dùng này?');">X</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <a href="index.php?controller=user&action=create" class="btn btn-primary">Thêm người dùng</a>
</div>