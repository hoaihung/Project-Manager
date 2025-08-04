<h1 style="margin-bottom:1rem;"><?php echo __('notifications'); ?></h1>

<?php
// $notifications is passed from ProfileController::notifications()
$overdue   = $notifications['overdue'] ?? [];
$dueSoon   = $notifications['due_soon'] ?? [];
// Overlap groups contain arrays with 'tasks' and 'range'
$overlapGroups = $notifications['overlapGroups'] ?? [];
function renderTable($tasks) {
    if (empty($tasks)) {
        echo '<p>' . __('no_tasks') . '.</p>';
        return;
    }
    echo '<table class="table table-bordered table-sm">';
    echo '<thead><tr>';
    echo '<th>' . __('task_name_col') . '</th>';
    echo '<th>' . __('project_name_col') . '</th>';
    echo '<th>' . __('start_date_col') . '</th>';
    echo '<th>' . __('due_date') . '</th>';
    echo '<th>' . __('status') . '</th>';
    echo '</tr></thead><tbody>';
    foreach ($tasks as $t) {
        echo '<tr>';
        echo '<td><a href="index.php?controller=task&action=edit&id=' . e($t['id']) . '">' . e($t['name']) . '</a></td>';
        echo '<td>' . e($t['project_name'] ?? '') . '</td>';
        echo '<td>' . e($t['start_date'] ?? '') . '</td>';
        echo '<td>' . e($t['due_date'] ?? '') . '</td>';
        echo '<td>' . __( $t['status'] ) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}
?>

<div class="mb-4">
    <h3><?php echo __('overdue'); ?></h3>
    <?php renderTable($overdue); ?>
</div>

<div class="mb-4">
    <h3><?php echo __('due_soon'); ?> (3 <?php echo __('days'); ?>)</h3>
    <?php renderTable($dueSoon); ?>
</div>

<div class="mb-4">
    <h3><?php echo __('overlap_tasks'); ?></h3>
    <?php if (empty($overlapGroups)): ?>
        <p><?php echo __('no_tasks'); ?>.</p>
    <?php else: ?>
        <?php foreach ($overlapGroups as $idx => $group): ?>
            <?php
                // Intersection range provided as [start, end]
                $range = $group['range'];
                // Compute number of days overlapped (inclusive)
                try {
                    $startDateObj = new \DateTime($range[0]);
                    $endDateObj   = new \DateTime($range[1]);
                    $interval = $startDateObj->diff($endDateObj);
                    // +1 to include both start and end dates
                    $overlapDays = $interval->days + 1;
                } catch (\Exception $e) {
                    $overlapDays = '';
                }
            ?>
            <div class="mb-3 p-3 border rounded bg-light shadow-sm">
                <h5 class="mb-2" style="font-weight:600;">
                    <?php echo __('overlap_tasks'); ?> <?php echo __('from'); ?>
                    <span class="badge bg-secondary"><?php echo e($range[0]); ?></span>
                    <?php echo __('to'); ?>
                    <span class="badge bg-secondary"><?php echo e($range[1]); ?></span>
                </h5>
                <ul class="list-unstyled mb-2" style="margin-left:0;">
                    <?php foreach ($group['tasks'] as $gt): ?>
                        <li class="mb-1">
                            <i class="fa fa-tasks text-muted me-1"></i>
                            <a href="index.php?controller=task&action=edit&id=<?php echo e($gt['id']); ?>" class="fw-semibold text-decoration-none">
                                <?php echo e($gt['name']); ?>
                            </a>
                            <span class="small text-muted">
                                (<?php echo e($gt['start_date'] ?? ''); ?> – <?php echo e($gt['due_date'] ?? ''); ?>)
                            </span>
                            <?php if (!empty($gt['project_name'])): ?>
                                <em class="small text-muted">– <?php echo e($gt['project_name']); ?></em>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php if (!empty($overlapDays)): ?>
                    <div class="text-muted" style="font-size:0.85rem;">
                        <?php echo __('overlap_tasks'); ?>
                        <?php echo __('from'); ?>
                        <?php echo e($range[0]); ?>
                        <?php echo __('to'); ?>
                        <?php echo e($range[1]); ?>
                        – <strong><?php echo e($overlapDays); ?></strong> <?php echo __('days'); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Chart rendering removed per new requirements: overlap groups now display textual information only -->