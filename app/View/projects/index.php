<h1 style="margin-bottom:1rem;"><?php echo e(__('projects')); ?></h1>

<div class="card">
    <table>
        <thead>
            <tr>
                <th><?php echo e(__('project_name')); ?></th>
                <th><?php echo e(__('status')); ?></th>
                <th><?php echo e(__('start_date')); ?></th>
                <th><?php echo e(__('end_date')); ?></th>
                <th><?php echo e(__('member_count')); ?></th>
                <th>Số task (hoàn thành/tổng)</th>
                <th>Tiến độ</th>
                <th>Task quá hạn</th>
                <th><?php echo e(__('actions')); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($projects as $project): ?>
            <tr>
                <td><?php echo e($project['name']); ?></td>
                <td><?php echo e($project['status']); ?></td>
                <td><?php echo e($project['start_date']); ?></td>
                <td><?php echo e($project['end_date']); ?></td>
                <td><?php echo e($project['member_count'] ?? 0); ?></td>
                <td><?php echo e($project['done_count']); ?>/<?php echo e($project['task_count']); ?></td>
                <td>
                    <?php
                    $total = (int)$project['task_count'];
                    $done = (int)$project['done_count'];
                    $progress = $total > 0 ? round(($done / $total) * 100) : 0;
                    ?>
                    <div style="position:relative; width:100%; height:8px; background-color:#e5e7eb; border-radius:4px;">
                        <div style="position:absolute; top:0; left:0; height:8px; width:<?php echo $progress; ?>%; background-color:#10b981; border-radius:4px;"></div>
                    </div>
                    <small><?php echo $progress; ?>%</small>
                </td>
                <td style="color:<?php echo $project['overdue_count'] > 0 ? 'var(--danger)' : 'var(--muted)'; ?>;">
                    <?php echo e($project['overdue_count']); ?>
                </td>
                <td style="white-space:nowrap; width:1%;">
                    <div style="display:flex; gap:0.25rem; flex-wrap:nowrap; white-space:nowrap;">
                        <a class="btn btn-secondary btn-sm" href="index.php?controller=task&project_id=<?php echo e($project['id']); ?>">
                            <?php echo e(__('tasks')); ?>
                        </a>
                        <?php if (user_can('edit_project') && user_can('access_project', $project['id'])): ?>
                            <a class="btn btn-primary btn-sm" href="index.php?controller=project&action=edit&id=<?php echo e($project['id']); ?>">
                                <?php echo e(__('edit_project')); ?>
                            </a>
                        <?php endif; ?>
                        <?php if (user_can('delete_project') && user_can('access_project', $project['id'])): ?>
                            <a class="btn btn-danger btn-sm" href="index.php?controller=project&action=delete&id=<?php echo e($project['id']); ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa?');">X</a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (user_can('create_project')): ?>
    <a href="index.php?controller=project&action=create" class="btn btn-primary">
        <?php echo e(__('create_project')); ?>
    </a>
    <?php endif; ?>
</div>