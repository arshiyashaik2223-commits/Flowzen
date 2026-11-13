<div class="container-fluid p-0">
    <!-- Header with Month Navigation -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="h3 fw-bold font-heading text-plum mb-1">Calendar Schedule</h2>
            <p class="text-muted mb-0 small">
                Visualize deadlines across time and plan upcoming milestones.
            </p>
        </div>
        <div class="mt-3 mt-md-0 d-flex align-items-center gap-2">
            <a href="<?= APP_URL ?>/calendar?month=<?= $prevMonth ?>" class="btn btn-outline-plum btn-sm d-flex align-items-center gap-1">
                <i data-lucide="chevron-left" style="width: 16px; height: 16px;"></i>
                <span>Previous</span>
            </a>
            <span class="fw-bold text-plum px-2 fs-6 font-heading"><?= e($monthName) ?></span>
            <a href="<?= APP_URL ?>/calendar?month=<?= $nextMonth ?>" class="btn btn-outline-plum btn-sm d-flex align-items-center gap-1">
                <span>Next</span>
                <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>
            </a>
        </div>
    </div>

    <!-- Calendar Container -->
    <div class="fz-card p-0 overflow-hidden mb-4">
        <!-- Day of Week Headers -->
        <div class="calendar-grid-header">
            <div>Sun</div>
            <div>Mon</div>
            <div>Tue</div>
            <div>Wed</div>
            <div>Thu</div>
            <div>Fri</div>
            <div>Sat</div>
        </div>

        <!-- Days Grid -->
        <div class="calendar-grid-body">
            <?php
                // Empty cells before the first day of the month
                for ($blank = 0; $blank < $firstDayOfWeek; $blank++):
            ?>
                <div class="calendar-day-cell inactive-day"></div>
            <?php endfor; ?>

            <?php
                $todayDate = date('Y-m-d');
                for ($day = 1; $day <= $daysInMonth; $day++):
                    $dateStr = sprintf('%s-%02d', $currentMonth, $day);
                    $isToday = ($dateStr === $todayDate);
                    $dayTasks = $tasksByDate[$dateStr] ?? [];
            ?>
                <div class="calendar-day-cell <?= $isToday ? 'is-today' : '' ?>">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="day-number"><?= $day ?></span>
                        <?php if (count($dayTasks) > 0): ?>
                            <span class="badge bg-plum rounded-pill" style="font-size: 0.65rem;">
                                <?= count($dayTasks) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="overflow-y-auto" style="max-height: 80px;">
                        <?php foreach ($dayTasks as $t): ?>
                            <?php
                                $badgeClass = 'bg-porcelain text-plum';
                                if ($t['priority'] === 'high') $badgeClass = 'bg-coral text-white';
                                elseif ($t['status'] === 'completed') $badgeClass = 'bg-teal text-white';
                            ?>
                            <a href="<?= APP_URL ?>/tasks/edit?id=<?= $t['id'] ?>"
                               class="calendar-task-pill d-block <?= $badgeClass ?>"
                               title="<?= e($t['title']) ?>">
                                <?= e($t['title']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endfor; ?>

            <?php
                // Fill trailing empty cells to complete the grid row
                $totalCells = $firstDayOfWeek + $daysInMonth;
                $trailing = (7 - ($totalCells % 7)) % 7;
                for ($blank = 0; $blank < $trailing; $blank++):
            ?>
                <div class="calendar-day-cell inactive-day"></div>
            <?php endfor; ?>
        </div>
    </div>
</div>
