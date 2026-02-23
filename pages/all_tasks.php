<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['error'] = 'Please login to access this page.';
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    $_SESSION['error'] = 'Access Denied: Admin privileges required.';
    header('Location: dashboard.php');
    exit;
}

$error_message = '';

try {
    $sql = "SELECT tasks.*, projects.name AS project_name, employees.first_name, employees.last_name
            FROM tasks
            JOIN projects ON tasks.project_id = projects.id
            LEFT JOIN employees ON tasks.assigned_to = employees.id
            ORDER BY tasks.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $tasks = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = 'Error fetching tasks: ' . htmlspecialchars($e->getMessage());
    $tasks = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>All Tasks - Footprints</title>
    <?php include 'head.php'; ?>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container-fluid mt-4">
        <div class="header-section">
            <div class="header-content">
                <h1>All Tasks</h1>
                <p class="subtitle">View all tasks in one page</p>
            </div>
            <div class="header-actions">
                <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
                <a href="tasks.php" class="btn btn-primary">Add New Task</a>
            </div>
        </div>

        <div class="table-container">
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <?php if (!empty($tasks)): ?>
            <div class="employee-count">
                <span>Total Tasks: <strong><?php echo count($tasks); ?></strong></span>
            </div>

            <table class="employee-table">
                <thead>
                    <tr>
                        <th>Task Name</th>
                        <th>Project</th>
                        <th>Assigned To</th>
                        <th>Priority</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task['name']); ?></td>
                        <td><?php echo htmlspecialchars($task['project_name']); ?></td>
                        <td><?php echo htmlspecialchars(trim(($task['first_name'] ?? 'Unassigned') . ' ' . ($task['last_name'] ?? ''))); ?></td>
                        <td>
                            <span class="priority-badge priority-<?php echo htmlspecialchars($task['priority']); ?>">
                                <?php echo htmlspecialchars(ucfirst($task['priority'])); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($task['due_date'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo htmlspecialchars($task['status']); ?>">
                                <?php echo htmlspecialchars(ucfirst($task['status'])); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <h3>No Tasks Found</h3>
                <p>There are no tasks to display right now.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
