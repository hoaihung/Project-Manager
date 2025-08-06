<?php
/*
 * Partial view for rendering a single task row in the list view. This
 * fragment expects that the following variables are defined in the
 * including scope:
 *  - $task: associative array representing the task record
 *  - $project: current project array (used for links)
 *  - $nowDate, $overdueList, $dueSoonList, $rowStyle: computed state for
 *    overdue/due soon highlighting
 */
?>
<?php
    // Compute overdue/due soon flags again if not provided
    $nowDate = $nowDate ?? date('Y-m-d');
    $overdueList = (!empty($task['due_date']) && $task['due_date'] < $nowDate && $task['status'] !== 'done');
    $dueSoonList = (!empty($task['due_date']) && $task['due_date'] >= $nowDate && $task['due_date'] <= date('Y-m-d', strtotime('+3 days')) && $task['status'] !== 'done');
    $rowStyle = '';
    // Build row style string based solely on this task's due status (no inheritance).
    // If the task is done, always keep a white background.  Otherwise highlight
    // overdue or due soon tasks using pastel colours.
    if ($task['status'] !== 'done') {
        if ($overdueList) {
            $rowStyle .= 'background-color: var(--status-overdue);';
        } elseif ($dueSoonList) {
            $rowStyle .= 'background-color: var(--status-due-soon);';
        }
    }
    // Add a thick border for completed tasks that still have incomplete subtasks
    $hasUndoneSubtasks = (!empty($task['subtask_total']) && $task['subtask_done'] < $task['subtask_total']);
    if ($task['status'] === 'done' && $hasUndoneSubtasks) {
        // Use an inset box-shadow instead of border to avoid collapsing
        $rowStyle .= 'box-shadow: 0 0 0 2px var(--danger) inset;';
    }
?>
<tr style="<?php echo $rowStyle; ?>">
    <td style="padding-left:<?php echo !empty($task['parent_id']) ? '1rem' : '0'; ?>;">
        <div>
            <a href="index.php?controller=task&action=edit&id=<?php echo e($task['id']); ?>&view=list" style="margin-right:0.25rem;">
                <?php echo e($task['name']); ?>
            </a>
            <?php
                // Lazy load models to count notes and links for this task
                $noteModelTmp = new \app\Model\Note();
                $linkModelTmp = new \app\Model\TaskLink();
                $noteCount = count($noteModelTmp->getByTask($task['id']));
                $linkCount = count($linkModelTmp->getByTask($task['id']));
            ?>
            <?php if (!empty($task['description'])): ?>
                <span title="<?php echo e(__('description')); ?>" style="margin-right:0.15rem;"><i class="fa-solid fa-align-left"></i></span>
            <?php endif; ?>
            <?php if (!empty($task['attachment_count']) && $task['attachment_count'] > 0): ?>
                <span title="<?php echo e(__('attachments')); ?>" style="margin-right:0.15rem;"><i class="fa-solid fa-paperclip"></i> (<?php echo e($task['attachment_count']); ?>)</span>
            <?php endif; ?>
            <?php if (!empty($task['subtask_total']) && $task['subtask_total'] > 0): ?>
                <span title="<?php echo e(__('subtasks')); ?>" style="margin-right:0.15rem;"><i class="fa-solid fa-layer-group"></i> <?php echo e($task['subtask_done']); ?>/<?php echo e($task['subtask_total']); ?></span>
            <?php endif; ?>
            <?php if ($noteCount > 0): ?>
                <span title="<?php echo e(__('notes')); ?>" style="margin-right:0.15rem;"><i class="fa-solid fa-note-sticky"></i> (<?php echo $noteCount; ?>)</span>
            <?php endif; ?>
            <?php if ($linkCount > 0): ?>
                <span title="<?php echo e(__('links')); ?>" style="margin-right:0.15rem;">
                    <?php
                        // Determine icon: use Google Docs/Sheets icons for docs and sheets; default link icon otherwise
                        $firstLink = $linkModelTmp->getByTask($task['id']);
                        $iconHtml = '<i class="fa-solid fa-link"></i>';
                        if (!empty($firstLink)) {
                            $url = $firstLink[0]['url'];
                            if (strpos($url, 'docs.google') !== false) {
                                $iconHtml = '<i class="fa-brands fa-google" style="color:#4285F4;"></i>';
                            } elseif (strpos($url, 'sheets.google') !== false) {
                                $iconHtml = '<i class="fa-brands fa-google" style="color:#0F9D58;"></i>';
                            }
                        }
                        echo $iconHtml;
                    ?>
                    (<?php echo $linkCount; ?>)
                </span>
            <?php endif; ?>
        </div>
        <?php if (!empty($task['assignees'])): ?>
            <div style="font-size:0.65rem; color:var(--muted); margin-top:0.1rem;">
                👤 <?php echo e($task['assignees']); ?>
            </div>
        <?php endif; ?>
    </td>
    <td>
        <?php
            $status = $task['status'];
            $statusLabel = '';
            $statusBg  = 'var(--status-todo)';
            $statusColor = '#4b5563';
            if ($status === 'in_progress') {
                $statusLabel = __('in_progress');
                $statusBg = 'var(--status-in-progress)';
                $statusColor = '#1e40af';
            } elseif ($status === 'bug_review') {
                $statusLabel = __('bug_review');
                $statusBg = 'var(--status-bug-review)';
                $statusColor = '#b45309';
            } elseif ($status === 'done') {
                $statusLabel = __('done');
                $statusBg = 'var(--status-done)';
                $statusColor = '#065f46';
            } else {
                $statusLabel = __('todo');
                $statusBg = 'var(--status-todo)';
                $statusColor = '#4b5563';
            }
        ?>
        <span style="background-color:<?php echo $statusBg; ?>; color:<?php echo $statusColor; ?>; padding:0.15rem 0.4rem; border-radius:0.25rem; font-size:0.7rem;">
            <?php echo e($statusLabel); ?>
        </span>
    </td>
    <td>
        <?php
            $p = $task['priority'] ?? 'normal';
            $pClass = 'priority-normal';
            $pIcon  = '<i class="fa-solid fa-flag"></i>';
            if ($p === 'urgent') {
                $pClass = 'priority-urgent';
            } elseif ($p === 'high') {
                $pClass = 'priority-high';
            } elseif ($p === 'low') {
                $pClass = 'priority-low';
            }
        ?>
        <span class="<?php echo e($pClass); ?>" style="padding:0.2rem 0.35rem; border-radius:0.3rem; font-size:0.75rem; display:inline-flex; align-items:center; justify-content:center;">
            <?php echo $pIcon; ?>
        </span>
    </td>
    <td><?php echo e($task['start_date'] ?? ''); ?></td>
    <td><?php echo e($task['due_date'] ?? ''); ?></td>
    <td>
        <a href="index.php?controller=task&action=edit&id=<?php echo e($task['id']); ?>&view=list" class="btn btn-outline-secondary btn-sm" title="<?php echo e(__('edit_task')); ?>">
            <i class="fa-solid fa-pencil"></i>
        </a>
        <?php if (empty($task['parent_id'])): ?>
            <a href="index.php?controller=task&action=create&project_id=<?php echo e($project['id']); ?>&parent_id=<?php echo e($task['id']); ?>&view=list" class="btn btn-outline-secondary btn-sm" title="<?php echo e(__('subtask')); ?>">
                <i class="fa-solid fa-plus"></i>
            </a>
        <?php endif; ?>
    </td>
</tr>