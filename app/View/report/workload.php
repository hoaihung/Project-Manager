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
        <div class="mt-5">
            <h3><?php echo __('due_soon_tasks_chart') ?: 'Công việc sắp đến hạn'; ?></h3>
            <p class="text-muted" style="font-size:0.85rem;">
                <?php echo __('due_soon_chart_hint') ?: 'Số lượng công việc đến hạn trong 7 ngày tới (bao gồm hôm nay).'; ?>
            </p>
            <canvas id="dueSoonChartCanvas" height="250" style="max-height:250px; width:100%;"></canvas>
        </div>

        <!-- Completed tasks analysis removed per requirements -->
    <?php endif; ?>
</div>

<?php
// Prepare datasets for Chart.js. Encode the reports and dueSoonByDate arrays.
$reportsJson = json_encode(array_values($reports));
$dueSoonJson = json_encode($dueSoonByDate ?? []);
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data passed from PHP
    const reports = <?php echo $reportsJson ?: '[]'; ?>;
    const dueSoon = <?php echo $dueSoonJson ?: '{}'; ?>;
    // no doneDue data; analysis removed

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

    // Prepare due soon chart data (next 7 days). We expect dueSoon to be an
    // object with keys as date strings and values as counts.
    const dueLabels = Object.keys(dueSoon);
    const dueValues = dueLabels.map(d => dueSoon[d]);
    const ctx2 = document.getElementById('dueSoonChartCanvas').getContext('2d');
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: dueLabels,
            datasets: [{
                label: '<?php echo __('due_tasks'); ?>',
                data: dueValues,
                backgroundColor: '#f87171'
            }]
        },
        options: {
            scales: {
                x: {
                    title: {
                        display: true,
                        text: '<?php echo __('date'); ?>'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: '<?php echo __('number_of_tasks') ?: 'Số lượng công việc'; ?>'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' <?php echo __('tasks'); ?>';
                        }
                    }
                }
            },
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>