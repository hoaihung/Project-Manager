<h1 style="margin-bottom:1rem;">Audit Log</h1>

<!-- Filter and controls -->
<div class="mb-3" style="display:flex; flex-wrap:wrap; gap:0.5rem; align-items:center;">
    <form method="get" style="display:flex; gap:0.5rem; align-items:center;">
        <input type="hidden" name="controller" value="log">
        <input type="hidden" name="action" value="index">
        <label for="date_range" style="font-size:0.85rem; color:var(--muted);">Filter:</label>
        <?php $selectedRange = $_GET['date_range'] ?? 'all'; ?>
        <select name="date_range" id="date_range" class="form-select form-select-sm" style="padding:0.3rem; border:1px solid var(--border); border-radius:0.25rem; font-size:0.85rem;">
            <option value="all" <?php echo $selectedRange === 'all' ? 'selected' : ''; ?>>All</option>
            <option value="today" <?php echo $selectedRange === 'today' ? 'selected' : ''; ?>>Today</option>
            <option value="last7" <?php echo $selectedRange === 'last7' ? 'selected' : ''; ?>>Last 7 days</option>
            <option value="this_month" <?php echo $selectedRange === 'this_month' ? 'selected' : ''; ?>>This month</option>
        </select>
        <button type="submit" class="btn btn-secondary btn-sm">Apply</button>
    </form>
    <!-- Clear logs button: confirm via JS before navigating -->
    <a href="index.php?controller=log&action=clear" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to clear all logs?');">Clear logs</a>
</div>

<div class="card">
    <?php
        // Group logs by date (YYYY-MM-DD).  This allows a cleaner
        // presentation where each day's entries are separated by a
        // header row.  Use the .group-header class for styling and
        // consistency with task group headers.
        $groupedLogs = [];
        foreach ($logs as $l) {
            $dateKey = substr($l['created_at'], 0, 10);
            $groupedLogs[$dateKey][] = $l;
        }
    ?>
    <!-- Wrap the audit log table to provide padding and border around it -->
    <div class="table-wrapper">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Action</th>
                <th>Details</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($groupedLogs)): ?>
                <tr><td colspan="5"><?php echo __('no_tasks'); ?></td></tr>
            <?php else: ?>
                <?php foreach ($groupedLogs as $date => $entries): ?>
                    <tr class="group-header"><td colspan="5"><?php echo e($date); ?></td></tr>
                    <?php foreach ($entries as $log): ?>
                        <tr>
                            <td><?php echo e($log['id']); ?></td>
                            <td><?php echo e($log['username']); ?></td>
                            <td><?php echo e($log['action']); ?></td>
                            <td><?php echo e($log['details']); ?></td>
                            <td><?php echo e($log['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
    <?php if (isset($totalPages) && $totalPages > 1): ?>
        <nav aria-label="Logs page navigation">
            <ul class="pagination">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <?php
                        $urlParams = $_GET;
                        $urlParams['page'] = $p;
                        $queryString = http_build_query($urlParams);
                    ?>
                    <li class="page-item <?php echo $p == $currentPage ? 'active' : ''; ?>">
                        <a class="page-link" href="index.php?<?php echo $queryString; ?>"><?php echo $p; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>