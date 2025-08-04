<h1 style="margin-bottom:1rem;">Audit Log</h1>
<div class="card">
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
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?php echo e($log['id']); ?></td>
                    <td><?php echo e($log['username']); ?></td>
                    <td><?php echo e($log['action']); ?></td>
                    <td><?php echo e($log['details']); ?></td>
                    <td><?php echo e($log['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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