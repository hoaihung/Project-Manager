<h1 style="margin-bottom:1rem;"><?php echo __('notifications'); ?></h1>

<?php
// $notifications is passed from ProfileController::notifications()
// Extract each category for clarity
$overdue        = $notifications['overdue'] ?? [];
$dueSoon        = $notifications['due_soon'] ?? [];
$dueToday       = $notifications['due_today'] ?? [];
$highPriority   = $notifications['high_priority'] ?? [];
// Overlap groups contain arrays with 'tasks' and 'range'
$overlapGroups  = $notifications['overlapGroups'] ?? [];

/**
 * Render a list of tasks with icons and status labels.  When the input list is empty
 * this function prints a paragraph indicating there are no tasks.
 *
 * Each list item displays:
 *  - Task name (link to edit)
 *  - Optional project name in muted text
 *  - A row of icons with due date, priority, attachments count, comment count and subtask progress
 *  - A coloured status label on the right
 *
 * @param array $tasks
 */
function renderTaskList(array $tasks)
{
    if (empty($tasks)) {
        echo '<p>' . __('no_tasks') . '.</p>';
        return;
    }
    echo '<ul class="list-unstyled">';
    foreach ($tasks as $t) {
        echo '<li class="mb-2 d-flex justify-content-between align-items-start">';
        // Left content: name, project, icons
        echo '<div>';
        echo '<a href="index.php?controller=task&action=edit&id=' . e($t['id']) . '" class="fw-semibold text-decoration-none">' . e($t['name']) . '</a>';
        if (!empty($t['project_name'])) {
            echo ' <small class="text-muted">– ' . e($t['project_name']) . '</small>';
        }
        echo '<div class="small text-muted mt-1">';
        $statusKey = $t['status'] ?? '';
        echo '<span class="status-label status-' . e($statusKey) . '" style="white-space:nowrap; margin-left:0.5rem;">' . __( $statusKey ) . '</span> ';
        // Due date
        if (!empty($t['due_date'])) {
            echo '<span><i class="fa-solid fa-calendar-day me-1"></i>' . e($t['due_date']) . '</span>';
        }
        // Priority
        $pri = $t['priority'] ?? '';
        if ($pri) {
            echo '<span class="ms-2"><i class="fa-solid fa-flag me-1"></i>' . __( $pri ) . '</span>';
        }
        // Attachments
        if (!empty($t['file_count'])) {
            echo '<span class="ms-2"><i class="fa-solid fa-paperclip me-1"></i>' . $t['file_count'] . '</span>';
        }
        // Comments
        if (!empty($t['comment_count'])) {
            echo '<span class="ms-2"><i class="fa-solid fa-comment me-1"></i>' . $t['comment_count'] . '</span>';
        }
        // Subtasks
        if (!empty($t['subtask_total'])) {
            $done = $t['subtask_done'] ?? 0;
            echo '<span class="ms-2"><i class="fa-solid fa-list-check me-1"></i>' . $done . '/' . $t['subtask_total'] . '</span>';
        }
        echo '</div>';
        echo '</div>';
        echo '</li>';
    }
    echo '</ul>';
}
?>

<!-- Wrap each category in a section card.  These custom wrappers match the
     application’s card style while ensuring the notifications page
     preserves its own CSS independent of Bootstrap’s card component. -->
<div class="section-card">
    <h3 class="mb-2"><?php echo __('overdue'); ?></h3>
    <?php renderTaskList($overdue); ?>
</div>

<div class="section-card">
    <h3 class="mb-2"><?php echo __('due_today'); ?></h3>
    <?php renderTaskList($dueToday); ?>
</div>

<div class="section-card">
    <h3 class="mb-2"><?php echo __('due_soon'); ?> (3 <?php echo __('days'); ?>)</h3>
    <?php renderTaskList($dueSoon); ?>
</div>

<div class="section-card">
    <h3 class="mb-2"><?php echo __('high_priority'); ?></h3>
    <?php renderTaskList($highPriority); ?>
</div>

<div class="section-card">
    <h3 class="mb-2"><?php echo __('overlap_tasks'); ?></h3>
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
                <?php renderTaskList($group['tasks']); ?>
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