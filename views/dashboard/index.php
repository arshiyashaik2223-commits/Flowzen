<div class="container-fluid p-0">
    <!-- Welcome Header Banner -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="h3 fw-bold font-heading text-plum mb-1">
                Hello, <?= e($user['name'] ?? 'Friend') ?> 👋
            </h2>
            <p class="text-muted mb-0 small">
                Here is your live productivity summary for today, <?= date('l, F j, Y') ?>.
            </p>
        </div>
        <div class="mt-3 mt-md-0 d-flex gap-2">
            <a href="<?= APP_URL ?>/tasks/create" class="btn btn-citron d-flex align-items-center gap-2">
                <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                <span>Add New Task</span>
            </a>
            <a href="<?= APP_URL ?>/tasks/export" class="btn btn-outline-plum d-flex align-items-center gap-2">
                <i data-lucide="download" style="width: 16px; height: 16px;"></i>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- 6 KPI Stat Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Tasks -->
        <div class="col-6 col-lg-2">
            <div class="kpi-card h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-muted small fw-semibold text-uppercase">Total</span>
                    <div class="kpi-icon-wrapper bg-porcelain text-plum">
                        <i data-lucide="layers" style="width: 20px; height: 20px;"></i>
                    </div>
                </div>
                <div class="kpi-value text-plum"><?= $stats['total'] ?></div>
                <div class="small text-muted" style="font-size:0.75rem;">Active workspace tasks</div>
            </div>
        </div>

        <!-- Pending -->
        <div class="col-6 col-lg-2">
            <div class="kpi-card h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-muted small fw-semibold text-uppercase">Pending</span>
                    <div class="kpi-icon-wrapper bg-orchid text-white">
                        <i data-lucide="clock" style="width: 20px; height: 20px;"></i>
                    </div>
                </div>
                <div class="kpi-value" style="color: #4C3C65;"><?= $stats['pending'] ?></div>
                <div class="small text-muted" style="font-size:0.75rem;">Awaiting kickoff</div>
            </div>
        </div>

        <!-- In Progress -->
        <div class="col-6 col-lg-2">
            <div class="kpi-card h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-muted small fw-semibold text-uppercase">In Progress</span>
                    <div class="kpi-icon-wrapper bg-teal text-white">
                        <i data-lucide="activity" style="width: 20px; height: 20px;"></i>
                    </div>
                </div>
                <div class="kpi-value" style="color: #0E564E;"><?= $stats['in_progress'] ?></div>
                <div class="small text-muted" style="font-size:0.75rem;">Under active focus</div>
            </div>
        </div>

        <!-- Completed -->
        <div class="col-6 col-lg-2">
            <div class="kpi-card h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-muted small fw-semibold text-uppercase">Done</span>
                    <div class="kpi-icon-wrapper bg-citron text-dark">
                        <i data-lucide="check-check" style="width: 20px; height: 20px;"></i>
                    </div>
                </div>
                <div class="kpi-value" style="color: #384805;"><?= $stats['completed'] ?></div>
                <div class="small text-muted" style="font-size:0.75rem;">Finished tasks</div>
            </div>
        </div>

        <!-- Overdue -->
        <div class="col-6 col-lg-2">
            <div class="kpi-card h-100" style="border-left: 3px solid var(--color-coral);">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-muted small fw-semibold text-uppercase">Overdue</span>
                    <div class="kpi-icon-wrapper bg-coral text-white">
                        <i data-lucide="alert-triangle" style="width: 20px; height: 20px;"></i>
                    </div>
                </div>
                <div class="kpi-value text-coral"><?= $stats['overdue'] ?></div>
                <div class="small text-muted" style="font-size:0.75rem;">Past target deadline</div>
            </div>
        </div>

        <!-- Velocity Completion Rate -->
        <div class="col-6 col-lg-2">
            <div class="kpi-card h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-muted small fw-semibold text-uppercase">Completion</span>
                    <div class="kpi-icon-wrapper bg-porcelain text-teal">
                        <i data-lucide="percent" style="width: 20px; height: 20px;"></i>
                    </div>
                </div>
                <div class="kpi-value text-teal"><?= $stats['completion_rate'] ?>%</div>
                <div class="small text-muted" style="font-size:0.75rem;">Efficiency ratio</div>
            </div>
        </div>
    </div>

    <!-- Charts Row: Distribution & Weekly Velocity -->
    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="fz-card h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h3 class="h6 fw-bold font-heading mb-0 text-plum">Task Status Distribution</h3>
                    <span class="badge bg-porcelain text-muted" style="font-size:0.72rem;">Live Breakdown</span>
                </div>
                <div style="height: 240px; position: relative;">
                    <canvas id="statusDoughnutChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="fz-card h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h3 class="h6 fw-bold font-heading mb-0 text-plum">Weekly Productivity Velocity</h3>
                    <span class="badge bg-porcelain text-muted" style="font-size:0.72rem;">Past 7 Days</span>
                </div>
                <div style="height: 240px; position: relative;">
                    <canvas id="weeklyVelocityChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Two Columns: Upcoming Deadlines & Recent Activities -->
    <div class="row g-3">
        <!-- Upcoming Deadlines -->
        <div class="col-lg-7">
            <div class="fz-card h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h3 class="h6 fw-bold font-heading mb-0 text-plum">Upcoming Deadlines</h3>
                    <a href="<?= APP_URL ?>/tasks" class="small text-plum fw-semibold text-decoration-none">View All &rarr;</a>
                </div>

                <?php if (empty($deadlines)): ?>
                    <div class="text-center py-4 text-muted">
                        <i data-lucide="calendar-check" class="mb-2" style="width: 32px; height: 32px; color: var(--color-teal);"></i>
                        <p class="small mb-0">No urgent pending deadlines! You are all caught up.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr class="text-muted small border-bottom">
                                    <th>Task Title</th>
                                    <th>Priority</th>
                                    <th>Due Date</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($deadlines as $d): ?>
                                    <?php
                                        $isOverdue = $d['due_date'] < date('Y-m-d');
                                        $isToday = $d['due_date'] === date('Y-m-d');
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-plum"><?= e($d['title']) ?></div>
                                            <div class="small text-muted text-truncate" style="max-width: 260px;">
                                                <?= e($d['description'] ?? 'No description provided') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-priority-<?= $d['priority'] ?> rounded-pill px-2 py-1 small">
                                                <?= ucfirst($d['priority']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($isOverdue): ?>
                                                <span class="text-coral fw-bold small d-flex align-items-center gap-1">
                                                    <i data-lucide="alert-circle" style="width:14px;height:14px;"></i>
                                                    <?= e($d['due_date']) ?> (Overdue)
                                                </span>
                                            <?php elseif ($isToday): ?>
                                                <span class="text-plum fw-bold small d-flex align-items-center gap-1">
                                                    <i data-lucide="clock" style="width:14px;height:14px;"></i>
                                                    Today
                                                </span>
                                            <?php else: ?>
                                                <span class="text-secondary small"><?= e($d['due_date']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= APP_URL ?>/tasks/edit?id=<?= $d['id'] ?>" class="btn btn-outline-plum btn-sm p-1" title="Edit Task">
                                                <i data-lucide="edit-2" style="width: 14px; height: 14px;"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activity Feed -->
        <div class="col-lg-5">
            <div class="fz-card h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h3 class="h6 fw-bold font-heading mb-0 text-plum">Recent Activity</h3>
                    <a href="<?= APP_URL ?>/activity" class="small text-plum fw-semibold text-decoration-none">Full Timeline &rarr;</a>
                </div>

                <?php if (empty($recentLogs)): ?>
                    <div class="text-center py-4 text-muted">
                        <i data-lucide="history" class="mb-2" style="width: 32px; height: 32px; color: var(--color-orchid);"></i>
                        <p class="small mb-0">No actions recorded yet. Create your first task!</p>
                    </div>
                <?php else: ?>
                    <div class="activity-feed">
                        <?php foreach ($recentLogs as $log): ?>
                            <div class="d-flex gap-3 mb-3 pb-2 border-bottom border-subtle">
                                <div class="rounded-circle bg-porcelain p-2 d-flex align-items-center justify-content-center"
                                     style="width: 32px; height: 32px; flex-shrink: 0;">
                                    <i data-lucide="activity" style="width: 16px; height: 16px; color: var(--color-plum);"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="small fw-semibold text-plum"><?= e($log['description']) ?></div>
                                    <div class="small text-muted" style="font-size: 0.72rem;">
                                        <?= date('M j, Y g:i A', strtotime($log['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart Initialization Script -->
<script src="<?= APP_URL ?>/assets/js/charts.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        initDashboardCharts(
            <?= json_encode([
                'pending'     => $stats['pending'],
                'in_progress' => $stats['in_progress'],
                'completed'   => $stats['completed']
            ]) ?>,
            <?= json_encode($weeklyTrend) ?>
        );
    });
</script>
