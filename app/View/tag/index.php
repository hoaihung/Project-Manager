<h1 style="margin-bottom:1rem;"><?php echo __('tags_page_title'); ?></h1>
<div class="card">
    <p><?php echo __('tags_page_description'); ?></p>
    <?php if (empty($tags)): ?>
        <p><?php echo __('no_tags'); ?></p>
    <?php else: ?>
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th><?php echo __('tag_singular'); ?></th>
                    <th><?php echo __('task_count'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tags as $t): ?>
                    <tr>
                        <td>
                            <?php
                            // For each tag, build links to each project where the tag appears
                            $tag = $t['tag'];
                            $taskModel = new \app\Model\Task();
                            $related = $taskModel->getTasksByTag($tag);
                            // Group by project_id
                            $projectsForTag = [];
                            foreach ($related as $task) {
                                $projectsForTag[$task['project_id']] = $task['project_name'];
                            }
                            ?>
                            <span><?php echo e($tag); ?></span>
                            <?php if (!empty($projectsForTag)): ?>
                                <br><small><?php echo __('in_project'); ?>:
                                    <?php
                                    $links = [];
                                    foreach ($projectsForTag as $pid => $pname) {
                                        $url = 'index.php?controller=task&project_id=' . urlencode($pid) . '&view=list&tag_filter=' . urlencode($tag);
                                        $links[] = '<a href="' . $url . '" style="text-decoration:underline; color:var(--primary);">' . e($pname) . '</a>';
                                    }
                                    echo implode(', ', $links);
                                    ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($t['count']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p style="margin-top:0.5rem; font-size:0.85rem; color:var(--muted);"><?php echo __('tag_instructions'); ?></p>
    <?php endif; ?>
</div>