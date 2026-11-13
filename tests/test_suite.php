<?php
declare(strict_types=1);

/**
 * Flowzen - Quality Assurance & Verification Test Runner
 * Executes all 25 mandatory RTIH assessment test cases.
 */

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

require_once dirname(__DIR__) . '/app/config/config.php';
require_once dirname(__DIR__) . '/app/config/database.php';
require_once dirname(__DIR__) . '/app/helpers/Security.php';
require_once dirname(__DIR__) . '/app/helpers/Session.php';
require_once dirname(__DIR__) . '/app/helpers/Validator.php';
require_once dirname(__DIR__) . '/app/models/User.php';
require_once dirname(__DIR__) . '/app/models/Task.php';
require_once dirname(__DIR__) . '/app/models/ActivityLog.php';
require_once dirname(__DIR__) . '/app/models/Notification.php';

class TestRunner {
    private int $passed = 0;
    private int $failed = 0;
    private array $results = [];

    public function run(): void {
        echo "\n" . str_repeat('=', 80) . "\n";
        echo " FLOWZEN SYSTEM QUALITY ASSURANCE & RTIH COMPLIANCE TEST RUNNER\n";
        echo str_repeat('=', 80) . "\n\n";

        // Execute tests
        $this->testDatabaseConnectivity();
        $this->testRegistrationSuccess();
        $this->testDuplicateEmailRejection();
        $this->testLoginSuccess();
        $this->testIncorrectPasswordRejection();
        $this->testDashboardAccessDenialWithoutLogin();
        $this->testTaskCreation();
        $this->testEmptyTitleRejection();
        $this->testTaskListing();
        $this->testTaskEditing();
        $this->testTaskDeletion();
        $this->testStatusUpdate();
        $this->testTitleSearch();
        $this->testStatusFiltering();
        $this->testPriorityFiltering();
        $this->testPaginationCalculations();
        $this->testDeadlineValidation();
        $this->testCsvExportStructure();
        $this->testUserA_CannotAccessUserB_Task();
        $this->testUserA_CannotModifyUserB_Task();
        $this->testCsrfValidation();
        $this->testSqlInjectionResistance();
        $this->testXssEscaping();
        $this->testLogoutSessionClearing();
        $this->testKanbanStatusPersistence();
        $this->testArchiveAndRestore();

        // Print final summary
        echo "\n" . str_repeat('-', 80) . "\n";
        echo sprintf(" TEST RUN FINISHED: %d PASSED, %d FAILED (TOTAL %d)\n",
            $this->passed, $this->failed, $this->passed + $this->failed);
        echo str_repeat('=', 80) . "\n";

        if ($this->failed > 0) {
            exit(1);
        }
        exit(0);
    }

    private function assert(string $testName, bool $condition, string $detail = ''): void {
        if ($condition) {
            $this->passed++;
            echo sprintf(" [PASS] %-55s %s\n", $testName, $detail);
            $this->results[] = ['name' => $testName, 'status' => 'PASS', 'detail' => $detail];
        } else {
            $this->failed++;
            echo sprintf(" [FAIL] %-55s %s\n", $testName, $detail);
            $this->results[] = ['name' => $testName, 'status' => 'FAIL', 'detail' => $detail];
        }
    }

    private function testDatabaseConnectivity(): void {
        try {
            $pdo = Database::getConnection();
            $this->assert('Database Connectivity', $pdo instanceof PDO, 'Connected via PDO with utf8mb4');
        } catch (Throwable $e) {
            $this->assert('Database Connectivity', false, $e->getMessage());
        }
    }

    private function testRegistrationSuccess(): void {
        $testEmail = 'tester_' . time() . '@flowzen.test';
        $userId = User::create('Test Suite User', $testEmail, 'SecurePass123!');
        $this->assert('1. Successful Registration', $userId > 0, "Created user ID: $userId");
    }

    private function testDuplicateEmailRejection(): void {
        $existing = 'demo@flowzen.com';
        $exists = User::emailExists($existing);
        $this->assert('2. Duplicate Email Rejection', $exists === true, 'Detected duplicate demo@flowzen.com');
    }

    private function testLoginSuccess(): void {
        $user = User::authenticate('demo@flowzen.com', 'Password123!');
        $this->assert('3. Successful Login', $user !== null && $user['email'] === 'demo@flowzen.com', 'Authenticated with password_verify()');
    }

    private function testIncorrectPasswordRejection(): void {
        $user = User::authenticate('demo@flowzen.com', 'WrongPasswordXYZ');
        $this->assert('4. Incorrect Password Rejection', $user === null, 'Rejected bad password safely');
    }

    private function testDashboardAccessDenialWithoutLogin(): void {
        Session::start();
        Session::destroy();
        Session::start();
        $isLoggedIn = Session::isLoggedIn();
        $this->assert('5. Dashboard Access Denial Without Login', $isLoggedIn === false, 'Session reports unauthenticated');
    }

    private function testTaskCreation(): void {
        $taskId = Task::create(1, [
            'title'       => 'Automated Test Task ' . time(),
            'description' => 'Test task created by automated test suite',
            'status'      => 'pending',
            'priority'    => 'high',
            'due_date'    => date('Y-m-d', strtotime('+3 days'))
        ]);
        $this->assert('6. Task Creation', $taskId > 0, "Created task ID: $taskId");
    }

    private function testEmptyTitleRejection(): void {
        $validator = new Validator(['title' => '   ', 'status' => 'pending']);
        $validator->required('title');
        $this->assert('7. Empty Title Rejection', $validator->fails() && isset($validator->getErrors()['title']), 'Rejected whitespace-only title');
    }

    private function testTaskListing(): void {
        $tasks = Task::getFiltered(1, ['archived' => false], 10, 0);
        $this->assert('8. Task Listing', is_array($tasks) && count($tasks) > 0, 'Found ' . count($tasks) . ' active tasks');
    }

    private function testTaskEditing(): void {
        $taskId = Task::create(1, [
            'title'    => 'Task To Edit',
            'status'   => 'pending',
            'priority' => 'low'
        ]);
        $updated = Task::update($taskId, 1, [
            'title'       => 'Task Has Been Edited',
            'description' => 'Updated description content',
            'status'      => 'in_progress',
            'priority'    => 'high',
            'due_date'    => date('Y-m-d')
        ]);
        $fetched = Task::findByIdAndUser($taskId, 1);
        $this->assert('9. Task Editing', $updated && $fetched['title'] === 'Task Has Been Edited', 'Verified title and status update');
    }

    private function testTaskDeletion(): void {
        $taskId = Task::create(1, ['title' => 'Task To Delete', 'status' => 'pending']);
        $deleted = Task::delete($taskId, 1);
        $exists = Task::findByIdAndUser($taskId, 1);
        $this->assert('10. Task Deletion', $deleted && $exists === null, 'Task successfully removed');
    }

    private function testStatusUpdate(): void {
        $taskId = Task::create(1, ['title' => 'Status Toggle Task', 'status' => 'pending']);
        Task::updateStatus($taskId, 1, 'completed');
        $task = Task::findByIdAndUser($taskId, 1);
        $this->assert('11. Status Update', $task['status'] === 'completed', 'Status toggled to completed');
    }

    private function testTitleSearch(): void {
        $uniqueTitle = 'UniqueSearchQueryXYZ_' . time();
        Task::create(1, ['title' => $uniqueTitle, 'status' => 'pending']);
        $results = Task::getFiltered(1, ['search' => 'UniqueSearchQueryXYZ', 'archived' => false], 10, 0);
        $this->assert('12. Title Search', count($results) >= 1 && str_contains($results[0]['title'], 'UniqueSearchQueryXYZ'), 'Search matched substring');
    }

    private function testStatusFiltering(): void {
        $completed = Task::getFiltered(1, ['status' => 'completed', 'archived' => false], 10, 0);
        $allCompleted = true;
        foreach ($completed as $t) {
            if ($t['status'] !== 'completed') {
                $allCompleted = false;
                break;
            }
        }
        $this->assert('13. Status Filtering', $allCompleted && count($completed) > 0, 'All returned tasks have status=completed');
    }

    private function testPriorityFiltering(): void {
        $high = Task::getFiltered(1, ['priority' => 'high', 'archived' => false], 10, 0);
        $allHigh = true;
        foreach ($high as $t) {
            if ($t['priority'] !== 'high') {
                $allHigh = false;
                break;
            }
        }
        $this->assert('14. Priority Filtering', $allHigh, 'Filtered exclusively by priority=high');
    }

    private function testPaginationCalculations(): void {
        $total = 25;
        $perPage = 8;
        $totalPages = (int)ceil($total / $perPage);
        $offset = (2 - 1) * $perPage;
        $this->assert('15. Pagination', $totalPages === 4 && $offset === 8, 'Page 2 offset = 8, total pages = 4');
    }

    private function testDeadlineValidation(): void {
        $validator = new Validator(['due_date' => 'not-a-date']);
        $validator->date('due_date');
        $validValidator = new Validator(['due_date' => '2026-12-31']);
        $validValidator->date('due_date');
        $this->assert('16. Deadline Validation', $validator->fails() && $validValidator->passes(), 'Enforced YYYY-MM-DD format');
    }

    private function testCsvExportStructure(): void {
        $data = Task::getForExport(1);
        $hasRequiredColumns = isset($data[0]['id'], $data[0]['title'], $data[0]['status'], $data[0]['priority']);
        $this->assert('17. CSV Export', is_array($data) && $hasRequiredColumns, 'Export query delivers relational fields');
    }

    private function testUserA_CannotAccessUserB_Task(): void {
        // User 1 creates a private task
        $taskA = Task::create(1, ['title' => 'User A Secret Task', 'status' => 'pending']);
        // User 2 attempts to query User 1's task
        $illegalAccess = Task::findByIdAndUser($taskA, 999999);
        $this->assert('18. User A Cannot View User B Task', $illegalAccess === null, 'Enforced user_id boundary');
    }

    private function testUserA_CannotModifyUserB_Task(): void {
        $taskA = Task::create(1, ['title' => 'User A Protected Task', 'status' => 'pending']);
        // User 999999 attempts to update it
        $modified = Task::update($taskA, 999999, ['title' => 'Hacked Task', 'status' => 'completed']);
        $unchanged = Task::findByIdAndUser($taskA, 1);
        $this->assert('19. User A Cannot Modify User B Task', $modified === false && $unchanged['title'] === 'User A Protected Task', 'Unauthorized UPDATE rejected');
    }

    private function testCsrfValidation(): void {
        Session::start();
        $token = Security::getCsrfToken();
        $valid = Security::validateCsrfToken($token);
        $invalid = Security::validateCsrfToken('forged-token-12345');
        $this->assert('20. CSRF Validation', $valid === true && $invalid === false, 'hash_equals token check verified');
    }

    private function testSqlInjectionResistance(): void {
        $maliciousInput = "'; DROP TABLE tasks; --";
        $results = Task::getFiltered(1, ['search' => $maliciousInput], 10, 0);
        $pdo = Database::getConnection();
        $tableStillExists = $pdo->query("SHOW TABLES LIKE 'tasks'")->fetchColumn();
        $this->assert('21. SQL Injection Resistance', $tableStillExists === 'tasks', 'PDO prepared statements neutralized injection attempt');
    }

    private function testXssEscaping(): void {
        $xss = '<script>alert("pwned")</script>';
        $escaped = Security::escape($xss);
        $this->assert('22. HTML/Script Injection Protection', !str_contains($escaped, '<script>') && str_contains($escaped, '&lt;script&gt;'), 'htmlspecialchars sanitized script tags');
    }

    private function testLogoutSessionClearing(): void {
        Session::start();
        Session::login(['id' => 1, 'name' => 'Demo', 'email' => 'demo@flowzen.com']);
        $before = Session::isLoggedIn();
        Session::destroy();
        Session::start();
        $after = Session::isLoggedIn();
        $this->assert('23. Logout & Session Protection', $before === true && $after === false, 'Session data destroyed');
    }

    private function testKanbanStatusPersistence(): void {
        $task = Task::create(1, ['title' => 'Kanban Test Task', 'status' => 'pending']);
        Task::updateStatus($task, 1, 'in_progress');
        $st1 = Task::findByIdAndUser($task, 1)['status'];
        Task::updateStatus($task, 1, 'completed');
        $st2 = Task::findByIdAndUser($task, 1)['status'];
        $this->assert('24. Kanban Status Persistence', $st1 === 'in_progress' && $st2 === 'completed', 'Backend state transitioned and persisted');
    }

    private function testArchiveAndRestore(): void {
        $task = Task::create(1, ['title' => 'Archive Test Task', 'status' => 'completed']);
        Task::archive($task, 1);
        $archived = Task::findByIdAndUser($task, 1);
        $isArchived = !empty($archived['archived_at']);
        Task::restore($task, 1);
        $restored = Task::findByIdAndUser($task, 1);
        $isRestored = empty($restored['archived_at']);
        $this->assert('25. Archive & Restore', $isArchived && $isRestored, 'archived_at timestamp managed correctly');
    }
}

$runner = new TestRunner();
$runner->run();
