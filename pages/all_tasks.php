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

$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_task_status'])) {
    $task_id = isset($_POST['task_id']) ? (int)$_POST['task_id'] : 0;
    $new_status = isset($_POST['status']) ? trim($_POST['status']) : '';

    if ($task_id <= 0 || !in_array($new_status, ['pending', 'in_progress', 'completed'], true)) {
        $error_message = 'Invalid task status update request.';
    } else {
        try {
            $updateStmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
            $updateStmt->execute([$new_status, $task_id]);
            $success_message = 'Task status updated successfully!';
        } catch (PDOException $e) {
            $error_message = 'Error updating task status: ' . htmlspecialchars($e->getMessage());
        }
    }
}

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
            <div class="header-actions d-flex flex-wrap gap-2">
                <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
                <a href="tasks.php" class="btn btn-primary">Add New Task</a>
            </div>
        </div>

        <div class="table-container">
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <?php if (!empty($tasks)): ?>
            <div class="employee-count">
                <span>Total Tasks: <strong><?php echo count($tasks); ?></strong></span>
            </div>

            <div class="table-responsive">
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
                            <form method="POST" class="d-flex align-items-center gap-2">
                                <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task['id']); ?>">
                                <span class="status-badge status-<?php echo htmlspecialchars($task['status']); ?>">
                                    <select name="status" style="background: transparent; border: none; color: inherit; font: inherit; padding: 0; cursor: pointer;">
                                        <option value="pending" <?php echo $task['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="in_progress" <?php echo $task['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="completed" <?php echo $task['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                </span>
                                <button type="submit" name="update_task_status" class="btn btn-outline-light btn-sm">Save</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
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

