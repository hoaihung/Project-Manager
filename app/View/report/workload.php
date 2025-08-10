<h1><?php echo __('workload_report'); ?></h1>
<div class="card">
    <?php if (empty($reports)): ?>
        <p><?php echo __('no_workload'); ?></p>
    <?php else: ?>
        <!-- Workload table per user -->
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th><?php echo __('user_label'); ?></th>
                    <th><?php echo __('total_tasks'); ?></th>
                    <th><?php echo __('todo'); ?></th>
                    <th><?php echo __('in_progress'); ?></th>
                    <th><?php echo __('bug_review'); ?></th>
                    <th><?php echo __('done'); ?></th>
                    <th><?php echo __('due_soon_days'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $uid => $stat): ?>
                    <tr>
                        <td><?php echo e($stat['user']['full_name']); ?></td>
                        <td><?php echo e($stat['total']); ?></td>
                        <td><?php echo e($stat['todo']); ?></td>
                        <td><?php echo e($stat['in_progress']); ?></td>
                        <td><?php echo e($stat['bug_review']); ?></td>
                        <td><?php echo e($stat['done']); ?></td>
                        <td><?php echo e($stat['due_soon']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <!-- Visualisation of workload and upcoming deadlines -->
        <div class="mt-4">
            <h3><?php echo __('task_statistics'); ?></h3>
            <p class="text-muted" style="font-size:0.85rem;">
                <?php echo __('workload_chart_hint') ?: 'Biểu đồ dưới đây cho thấy khối lượng công việc của mỗi thành viên được phân tách theo trạng thái. Các thanh được xếp chồng giúp bạn dễ dàng so sánh tổng số việc và mức độ phân bố.'; ?>
            </p>
            <canvas id="workloadByUserChart" height="300" style="max-height:300px; width:100%;"></canvas>
        </div>
        <!-- Time‑based statistics section with filters -->
        <div class="mt-5">
            <h3><?php echo __('time_based_statistics') ?: 'Thống kê theo thời gian'; ?></h3>
            <p class="text-muted" style="font-size:0.85rem;">
                <?php echo __('time_based_statistics_hint') ?: 'Bạn có thể lọc theo tuần, tháng, năm, dự án và (nếu là quản trị) theo người dùng. Các số liệu hiển thị trong khoảng từ ngày bắt đầu tới ngày hiện tại.'; ?>
            </p>
            <!-- Filter form -->
            <form method="get" class="row g-2 align-items-end mb-3">
                <input type="hidden" name="controller" value="report">
                <input type="hidden" name="action" value="workload">
                <div class="col-auto">
                    <label for="range" class="form-label" style="font-size:0.85rem;">Khoảng thời gian</label>
                    <select name="range" id="range" class="form-select form-select-sm">
                        <option value="week" <?php echo ($selectedRange === 'week') ? 'selected' : ''; ?>>Tuần hiện tại</option>
                        <option value="month" <?php echo ($selectedRange === 'month') ? 'selected' : ''; ?>>Tháng hiện tại</option>
                        <option value="year" <?php echo ($selectedRange === 'year') ? 'selected' : ''; ?>>Năm hiện tại</option>
                        <option value="custom" <?php echo ($selectedRange === 'custom') ? 'selected' : ''; ?>>Tùy chỉnh</option>
                    </select>
                </div>
                <div class="col-auto">
                    <label for="project_id" class="form-label" style="font-size:0.85rem;">Dự án</label>
                    <select name="project_id" id="project_id" class="form-select form-select-sm">
                        <option value="0">Tất cả dự án</option>
                        <?php foreach ($projectOptions as $pOpt): ?>
                            <option value="<?php echo e($pOpt['id']); ?>" <?php echo ($selectedProjectId == $pOpt['id']) ? 'selected' : ''; ?>><?php echo e($pOpt['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($isAdmin): ?>
                    <div class="col-auto">
                        <label for="user_id" class="form-label" style="font-size:0.85rem;">Người dùng</label>
                        <select name="user_id" id="user_id" class="form-select form-select-sm">
                            <option value="0">Tất cả người dùng</option>
                            <?php foreach ($userOptions as $uOpt): ?>
                                <option value="<?php echo e($uOpt['id']); ?>" <?php echo ($selectedUserId == $uOpt['id']) ? 'selected' : ''; ?>><?php echo e($uOpt['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                <!-- Custom date range inputs -->
                <div class="col-auto">
                    <label for="start_date" class="form-label" style="font-size:0.85rem;">Từ ngày</label>
                    <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="<?php echo e($_GET['start_date'] ?? ''); ?>">
                </div>
                <div class="col-auto">
                    <label for="end_date" class="form-label" style="font-size:0.85rem;">Đến ngày</label>
                    <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="<?php echo e($_GET['end_date'] ?? ''); ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Lọc</button>
                </div>
            </form>

            <?php if (!empty($timeStat)): ?>
                <h4 class="mt-3" style="margin-bottom:0.5rem;">
                    Báo cáo <?php echo e($timeStat['label']); ?> từ <?php echo e($timeStat['start']); ?> đến <?php echo e($timeStat['end']); ?>
                </h4>
                <?php if (empty($timeStat['projects'])): ?>
                    <p><?php echo __('no_data_period') ?: 'Không có dữ liệu cho giai đoạn này.'; ?></p>
                <?php else: ?>
                    <?php foreach ($timeStat['projects'] as $projId => $projStats): ?>
                        <?php $proj = $projStats['project']; ?>
                        <?php if (!$proj) continue; ?>
                        <div class="mb-4 card">
                            <div class="card-header">
                                <strong>Dự án: <?php echo e($proj['name']); ?></strong>
                            </div>
                            <div class="card-body" style="font-size:0.9rem;">
                                <div class="row text-center">
                                    <!-- Column for started tasks -->
                                    <div class="col-md-4 mb-3">
                                        <div class="border rounded p-2 h-100">
                                            <h6 class="mb-1" title="Công việc có ngày bắt đầu nằm trong khoảng thời gian đã chọn">Bắt đầu trong giai đoạn</h6>
                                            <div style="font-size:1.6rem; font-weight:bold;">
                                                <?php echo e($projStats['started_count']); ?>
                                            </div>
                                            <?php if (!empty($projStats['started_tasks'])): ?>
                                                <table class="table table-sm table-striped mt-2 mb-0">
                                                    <thead>
                                                        <tr><th class="text-start">Công việc</th><th class="text-start">Bắt đầu</th></tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($projStats['started_tasks'] as $idx => $t): ?>
                                                            <?php if ($idx < 5): ?>
                                                            <tr>
                                                                <td class="text-start"><a href="index.php?controller=task&action=edit&id=<?php echo e($t['id']); ?>" target="_blank"><?php echo e($t['name']); ?></a></td>
                                                                <td class="text-start"><?php echo e($t['start_date']); ?></td>
                                                            </tr>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                                <?php if (count($projStats['started_tasks']) > 5): ?>
                                                    <details class="mt-1">
                                                        <summary style="cursor:pointer; font-size:0.8rem;">Xem thêm <?php echo e(count($projStats['started_tasks']) - 5); ?> công việc nữa</summary>
                                                        <table class="table table-sm table-striped mt-1">
                                                            <tbody>
                                                            <?php foreach ($projStats['started_tasks'] as $idx => $t): ?>
                                                                <?php if ($idx >= 5): ?>
                                                                <tr>
                                                                    <td class="text-start"><a href="index.php?controller=task&action=edit&id=<?php echo e($t['id']); ?>" target="_blank"><?php echo e($t['name']); ?></a></td>
                                                                    <td class="text-start"><?php echo e($t['start_date']); ?></td>
                                                                </tr>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </details>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <!-- Column for completion stats -->
                                    <div class="col-md-4 mb-3">
                                        <div class="border rounded p-2 h-100">
                                            <h6 class="mb-1" title="Số công việc đã hoàn thành, phân chia theo đúng hạn và trễ hạn">Thống kê hoàn thành</h6>
                                            <p class="mb-1"><strong>Đúng hạn:</strong> <?php echo e($projStats['done_ontime']); ?></p>
                                            <p class="mb-1"><strong>Trễ:</strong> <?php echo e($projStats['done_late']); ?></p>
                                            <p class="mb-1"><strong>Tổng:</strong> <?php echo e($projStats['due_total']); ?></p>
                                            <p class="mb-0"><strong>% Hoàn thành:</strong> <?php echo e($projStats['percent_done']); ?>%<br><strong>% Chưa hoàn thành:</strong> <?php echo e($projStats['percent_not_done']); ?>%</p>
                                        </div>
                                    </div>
                                    <!-- Column for overdue tasks -->
                                    <div class="col-md-4 mb-3">
                                        <div class="border rounded p-2 h-100">
                                            <h6 class="mb-1" title="Công việc chưa hoàn thành và đã quá hạn kết thúc đến ngày hôm nay">Quá hạn</h6>
                                            <div style="font-size:1.6rem; font-weight:bold;">
                                                <?php echo e($projStats['overdue_count']); ?>
                                            </div>
                                            <?php if (!empty($projStats['overdue_tasks'])): ?>
                                                <table class="table table-sm table-striped mt-2 mb-0">
                                                    <thead>
                                                        <tr><th class="text-start">Công việc</th><th class="text-start">Hạn chót</th></tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($projStats['overdue_tasks'] as $idx => $ot): ?>
                                                            <?php if ($idx < 5): ?>
                                                            <tr>
                                                                <td class="text-start"><a href="index.php?controller=task&action=edit&id=<?php echo e($ot['id']); ?>" target="_blank"><?php echo e($ot['name']); ?></a></td>
                                                                <td class="text-start"><?php echo e($ot['due_date']); ?></td>
                                                            </tr>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                                <?php if (count($projStats['overdue_tasks']) > 5): ?>
                                                    <details class="mt-1">
                                                        <summary style="cursor:pointer; font-size:0.8rem;">Xem thêm <?php echo e(count($projStats['overdue_tasks']) - 5); ?> công việc nữa</summary>
                                                        <table class="table table-sm table-striped mt-1">
                                                            <tbody>
                                                            <?php foreach ($projStats['overdue_tasks'] as $idx => $ot): ?>
                                                                <?php if ($idx >= 5): ?>
                                                                <tr>
                                                                    <td class="text-start"><a href="index.php?controller=task&action=edit&id=<?php echo e($ot['id']); ?>" target="_blank"><?php echo e($ot['name']); ?></a></td>
                                                                    <td class="text-start"><?php echo e($ot['due_date']); ?></td>
                                                                </tr>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </details>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div> <!-- end of row of stats -->
                                <!-- Additional task lists for completion and not done -->
                                <div class="mt-2">
                                    <!-- Done on time tasks list -->
                                    <h6 class="mb-1" title="Các công việc đã chuyển sang Hoàn thành và có ngày hoàn thành trước hoặc bằng ngày hết hạn">Đã hoàn thành đúng hạn (<?php echo count($projStats['done_ontime_tasks']); ?>)</h6>
                                    <?php if (!empty($projStats['done_ontime_tasks'])): ?>
                                        <table class="table table-sm table-striped mb-2">
                                            <thead>
                                                <tr><th class="text-start">Công việc</th><th class="text-start">Hoàn thành</th><th class="text-start">Hạn chót</th></tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($projStats['done_ontime_tasks'] as $idx => $t): ?>
                                                    <?php if ($idx < 5): ?>
                                                    <tr>
                                                        <td class="text-start"><a href="index.php?controller=task&action=edit&id=<?php echo e($t['id']); ?>" target="_blank"><?php echo e($t['name']); ?></a></td>
                                                        <td class="text-start"><?php echo e($t['completed_at']); ?></td>
                                                        <td class="text-start"><?php echo e($t['due_date']); ?></td>
                                                    </tr>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                        <?php if (count($projStats['done_ontime_tasks']) > 5): ?>
                                            <details class="mb-2">
                                                <summary style="cursor:pointer; font-size:0.8rem;">Xem thêm <?php echo e(count($projStats['done_ontime_tasks']) - 5); ?> công việc nữa</summary>
                                                <table class="table table-sm table-striped mt-1">
                                                    <tbody>
                                                    <?php foreach ($projStats['done_ontime_tasks'] as $idx => $t): ?>
                                                        <?php if ($idx >= 5): ?>
                                                        <tr>
                                                            <td class="text-start"><a href="index.php?controller=task&action=edit&id=<?php echo e($t['id']); ?>" target="_blank"><?php echo e($t['name']); ?></a></td>
                                                            <td class="text-start"><?php echo e($t['completed_at']); ?></td>
                                                            <td class="text-start"><?php echo e($t['due_date']); ?></td>
                                                        </tr>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </details>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <!-- Done late tasks list -->
                                    <h6 class="mb-1" title="Các công việc đã hoàn thành nhưng sau ngày hết hạn">Đã hoàn thành trễ (<?php echo count($projStats['done_late_tasks']); ?>)</h6>
                                    <?php if (!empty($projStats['done_late_tasks'])): ?>
                                        <table class="table table-sm table-striped mb-2">
                                            <thead>
                                                <tr><th class="text-start">Công việc</th><th class="text-start">Hoàn thành</th><th class="text-start">Hạn chót</th></tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($projStats['done_late_tasks'] as $idx => $t): ?>
                                                    <?php if ($idx < 5): ?>
                                                    <tr>
                                                        <td class="text-start"><a href="index.php?controller=task&action=edit&id=<?php echo e($t['id']); ?>" target="_blank"><?php echo e($t['name']); ?></a></td>
                                                        <td class="text-start"><?php echo e($t['completed_at']); ?></td>
                                                        <td class="text-start"><?php echo e($t['due_date']); ?></td>
                                                    </tr>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                        <?php if (count($projStats['done_late_tasks']) > 5): ?>
                                            <details class="mb-2">
                                                <summary style="cursor:pointer; font-size:0.8rem;">Xem thêm <?php echo e(count($projStats['done_late_tasks']) - 5); ?> công việc nữa</summary>
                                                <table class="table table-sm table-striped mt-1">
                                                    <tbody>
                                                    <?php foreach ($projStats['done_late_tasks'] as $idx => $t): ?>
                                                        <?php if ($idx >= 5): ?>
                                                        <tr>
                                                            <td class="text-start"><a href="index.php?controller=task&action=edit&id=<?php echo e($t['id']); ?>" target="_blank"><?php echo e($t['name']); ?></a></td>
                                                            <td class="text-start"><?php echo e($t['completed_at']); ?></td>
                                                            <td class="text-start"><?php echo e($t['due_date']); ?></td>
                                                        </tr>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </details>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <!-- In-progress tasks list (not done and not overdue) -->
                                    <?php
                                        $inProgressCount = isset($projStats['in_progress']) ? $projStats['in_progress'] : 0;
                                    ?>
                                    <h6 class="mb-1" title="<?php echo __('in_progress_hint') ?: 'Công việc chưa hoàn thành và vẫn còn trong thời hạn (chưa quá hạn).'; ?>">
                                        <?php echo __('tasks_in_progress') ?: 'Đang thực hiện'; ?> (<?php echo $inProgressCount; ?>)
                                    </h6>
                                    <?php if (!empty($projStats['in_progress_tasks'])): ?>
                                        <table class="table table-sm table-striped mb-2">
                                            <thead>
                                                <tr><th class="text-start">Công việc</th><th class="text-start">Hạn chót</th></tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($projStats['in_progress_tasks'] as $idx => $nt): ?>
                                                    <?php if ($idx < 5): ?>
                                                    <tr>
                                                        <td class="text-start"><a href="index.php?controller=task&action=edit&id=<?php echo e($nt['id']); ?>" target="_blank"><?php echo e($nt['name']); ?></a></td>
                                                        <td class="text-start"><?php echo e($nt['due_date']); ?></td>
                                                    </tr>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                        <?php if (count($projStats['in_progress_tasks']) > 5): ?>
                                            <details class="mb-2">
                                                <summary style="cursor:pointer; font-size:0.8rem;">Xem thêm <?php echo e(count($projStats['in_progress_tasks']) - 5); ?> công việc nữa</summary>
                                                <table class="table table-sm table-striped mt-1">
                                                    <tbody>
                                                    <?php foreach ($projStats['in_progress_tasks'] as $idx => $nt): ?>
                                                        <?php if ($idx >= 5): ?>
                                                        <tr>
                                                            <td class="text-start"><a href="index.php?controller=task&action=edit&id=<?php echo e($nt['id']); ?>" target="_blank"><?php echo e($nt['name']); ?></a></td>
                                                            <td class="text-start"><?php echo e($nt['due_date']); ?></td>
                                                        </tr>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </details>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <!-- Section: per-user breakdown (admin only) -->
                                <?php if ($isAdmin && !empty($projStats['users'])): ?>
                                    <details class="mt-2">
                                        <summary style="cursor:pointer; font-size:0.9rem;">Chi tiết theo người dùng</summary>
                                        <div class="mt-1">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Người dùng</th>
                                                        <th>Bắt đầu</th>
                                                        <th>Đúng hạn</th>
                                                        <th>Trễ hạn</th>
                                                        <th>Tổng</th>
                                                        <th>% Hoàn thành</th>
                                                        <th>% Chưa hoàn thành</th>
                                                        <th>Quá hạn</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($projStats['users'] as $uid => $uStats): ?>
                                                        <?php $u = $uStats['user']; ?>
                                                        <?php if (!$u) continue; ?>
                                                        <tr>
                                                            <td><?php echo e($u['full_name']); ?></td>
                                                            <td><?php echo e($uStats['started_count']); ?></td>
                                                            <td><?php echo e($uStats['done_ontime']); ?></td>
                                                            <td><?php echo e($uStats['done_late']); ?></td>
                                                            <td><?php echo e($uStats['due_total']); ?></td>
                                                            <td><?php echo e($uStats['percent_done']); ?>%</td>
                                                            <td><?php echo e($uStats['percent_not_done']); ?>%</td>
                                                            <td><?php echo e($uStats['overdue_count']); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </details>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <!-- Summary across all projects -->
                    <div class="mb-3">
                        <h5 style="margin-bottom:0.25rem;">Tổng hợp tất cả dự án</h5>
                        <p><strong>Số công việc bắt đầu trong giai đoạn:</strong> <?php echo e($timeStat['summary']['started_count']); ?></p>
                        <p><strong>Hoàn thành đúng hạn:</strong> <?php echo e($timeStat['summary']['done_ontime']); ?> &nbsp;&nbsp; <strong>Hoàn thành trễ:</strong> <?php echo e($timeStat['summary']['done_late']); ?></p>
                        <p><strong>Tổng công việc:</strong> <?php echo e($timeStat['summary']['due_total']); ?> &nbsp;&nbsp; <strong>% Hoàn thành:</strong> <?php echo e($timeStat['summary']['percent_done']); ?>% &nbsp;&nbsp; <strong>% Chưa hoàn thành:</strong> <?php echo e($timeStat['summary']['percent_not_done']); ?>%</p>
                        <p><strong>Công việc quá hạn:</strong> <?php echo e($timeStat['summary']['overdue_count']); ?></p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <!-- End time‑based statistics section -->
    <?php endif; ?>
</div>

<?php
// Prepare dataset for Chart.js. Encode the reports array.
$reportsJson = json_encode(array_values($reports));
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data passed from PHP
    const reports = <?php echo $reportsJson ?: '[]'; ?>;
    // no dueSoon data; analysis removed

    // Build data arrays for workload chart. We extract user names and counts
    // for each status. The status keys correspond to database values and we
    // translate them into human-readable labels using PHP translations.
    const users = reports.map(r => r.user.full_name);
    const statuses = ['todo','in_progress','bug_review','done'];
    const statusLabels = {
        'todo': '<?php echo __('todo'); ?>',
        'in_progress': '<?php echo __('in_progress'); ?>',
        'bug_review': '<?php echo __('bug_review'); ?>',
        'done': '<?php echo __('done'); ?>'
    };
    const colours = {
        'todo': '#e5e7eb',
        'in_progress': '#60a5fa',
        'bug_review': '#fbbf24',
        'done': '#34d399'
    };
    const datasets = statuses.map(statusKey => {
        return {
            label: statusLabels[statusKey] || statusKey,
            data: reports.map(r => r[statusKey] || 0),
            backgroundColor: colours[statusKey],
            stack: 'status'
        };
    });
    const ctx1 = document.getElementById('workloadByUserChart').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: users,
            datasets: datasets
        },
        options: {
            indexAxis: 'y',
            scales: {
                x: {
                    stacked: true,
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: '<?php echo __('number_of_tasks') ?: 'Số lượng công việc'; ?>'
                    }
                },
                y: {
                    stacked: true,
                    ticks: {
                        autoSkip: false
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.dataset.label || '';
                            const value = context.parsed.x;
                            return label + ': ' + value;
                        }
                    }
                }
            },
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Removed due soon chart rendering
});
</script>