<?php
session_start();
require '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['error'] = 'Please login to access this page.';
    header('Location: login.php');
    exit;
}

// Check if user is Employee
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'employee') {
    $_SESSION['error'] = 'Access Denied: This page is for employees only.';
    header('Location: dashboard.php');
    exit;
}

$success_message = '';
$error_message = '';

// Check for success message in session
if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Get employee ID from user_id
$employee_id = null;
try {
    $stmt = $pdo->prepare("SELECT id FROM employees WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $employee = $stmt->fetch();
    if ($employee) {
        $employee_id = $employee['id'];
    } else {
        $error_message = 'Employee profile not found. Please contact administrator.';
    }
} catch (PDOException $e) {
    $error_message = 'Error fetching employee data: ' . htmlspecialchars($e->getMessage());
}

// Handle Status Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['task_id']) && isset($_POST['status'])) {
    $task_id = !empty($_POST['task_id']) ? intval($_POST['task_id']) : 0;
    $new_status = isset($_POST['status']) ? trim($_POST['status']) : null;
    
    if ($task_id > 0 && $new_status && in_array($new_status, ['pending', 'in_progress', 'completed'])) {
        try {
            // Verify the task belongs to this employee
            $verify_stmt = $pdo->prepare("SELECT id FROM tasks WHERE id = ? AND assigned_to = ?");
            $verify_stmt->execute([$task_id, $employee_id]);
            
            if ($verify_stmt->fetch()) {
                $update_stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ? AND assigned_to = ?");
                $update_stmt->execute([$new_status, $task_id, $employee_id]);
                $_SESSION['success'] = 'Task status updated successfully!';
                header('Location: my_tasks.php');
                exit;
            } else {
                $error_message = 'Unauthorized: You can only update your own tasks.';
            }
        } catch (PDOException $e) {
            $error_message = 'Error updating task: ' . htmlspecialchars($e->getMessage());
        }
    } else {
        $error_message = 'Invalid task or status.';
    }
}

// Fetch Tasks Assigned to the Employee
$tasks = [];
if ($employee_id) {
    try {
        $sql = "SELECT tasks.*, projects.name as project_name 
                FROM tasks 
                JOIN projects ON tasks.project_id = projects.id 
                WHERE tasks.assigned_to = ? 
                ORDER BY tasks.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$employee_id]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error_message = 'Error fetching tasks: ' . htmlspecialchars($e->getMessage());
    }
}

// Count tasks by status
$pending_count = 0;
$in_progress_count = 0;
$completed_count = 0;

foreach ($tasks as $task) {
    if ($task['status'] == 'pending') $pending_count++;
    elseif ($task['status'] == 'in_progress') $in_progress_count++;
    elseif ($task['status'] == 'completed') $completed_count++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Tasks - Mini ERP</title>
    <?php include 'head.php'; ?>
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-card h3 {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-card .stat-number {
            color: #ffffff;
            font-size: 36px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .stat-card.pending { border-color: rgba(255, 193, 7, 0.5); }
        .stat-card.in-progress { border-color: rgba(33, 150, 243, 0.5); }
        .stat-card.completed { border-color: rgba(76, 175, 80, 0.5); }
        
        .task-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }
        
        .task-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .task-card-title {
            color: #ffffff;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .task-card-project {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .task-card-description {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .task-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .task-card-info {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .task-info-item {
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
        }
        
        .task-info-item strong {
            color: #ffffff;
        }
        
        .status-update-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .status-update-form select {
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            color: #ffffff;
            font-size: 14px;
            cursor: pointer;
        }
        
        .status-update-form select option {
            background: #1a1a2e;
            color: #ffffff;
        }
        
        .status-update-form button {
            padding: 8px 16px;
            background: rgba(76, 175, 80, 0.2);
            border: 1px solid rgba(76, 175, 80, 0.5);
            border-radius: 8px;
            color: #ffffff;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .status-update-form button:hover {
            background: rgba(76, 175, 80, 0.3);
            border-color: rgba(76, 175, 80, 0.7);
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h1>
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 11l3 3L22 4"></path>
                        <path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    My Tasks
                </h1>
                <p class="subtitle">View and manage your assigned tasks</p>
            </div>
            <div class="header-actions">
                <a href="dashboard.php" class="btn btn-secondary">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    Dashboard
                </a>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success" style="margin: 20px 0;">
            <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <?php echo htmlspecialchars($success_message); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
        <div class="alert alert-error" style="margin: 20px 0;">
            <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card pending">
                <h3>Pending</h3>
                <div class="stat-number"><?php echo $pending_count; ?></div>
            </div>
            <div class="stat-card in-progress">
                <h3>In Progress</h3>
                <div class="stat-number"><?php echo $in_progress_count; ?></div>
            </div>
            <div class="stat-card completed">
                <h3>Completed</h3>
                <div class="stat-number"><?php echo $completed_count; ?></div>
            </div>
        </div>

        <!-- Tasks Display -->
        <div class="table-container">
            <h3 style="color: #ffffff; margin-bottom: 25px; font-size: 20px;">All My Tasks</h3>
            
            <?php if (!empty($tasks)): ?>
                <?php foreach ($tasks as $task): ?>
                <div class="task-card">
                    <div class="task-card-header">
                        <div>
                            <div class="task-card-title"><?php echo htmlspecialchars($task['name']); ?></div>
                            <div class="task-card-project">
                                <svg style="width: 14px; height: 14px; display: inline-block; vertical-align: middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                </svg>
                                Project: <?php echo htmlspecialchars($task['project_name']); ?>
                            </div>
                        </div>
                        <span class="priority-badge priority-<?php echo htmlspecialchars($task['priority']); ?>">
                            <?php echo htmlspecialchars(ucfirst($task['priority'])); ?>
                        </span>
                    </div>
                    
                    <?php if (!empty($task['description'])): ?>
                    <div class="task-card-description">
                        <?php echo nl2br(htmlspecialchars($task['description'])); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="task-card-footer">
                        <div class="task-card-info">
                            <div class="task-info-item">
                                <strong>Due Date:</strong> <?php echo htmlspecialchars($task['due_date'] ?? 'Not set'); ?>
                            </div>
                            <div class="task-info-item">
                                <strong>Current Status:</strong> 
                                <span class="status-badge status-<?php echo htmlspecialchars($task['status']); ?>">
                                    <?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($task['status']))); ?>
                                </span>
                            </div>
                        </div>
                        
                        <form method="POST" class="status-update-form">
                            <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task['id']); ?>">
                            <select name="status" required>
                                <option value="pending" <?php echo $task['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="in_progress" <?php echo $task['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="completed" <?php echo $task['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                            <button type="submit" name="update_status">Update Status</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="empty-state">
                <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 11l3 3L22 4"></path>
                    <path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3>No Tasks Assigned</h3>
                <p>You don't have any tasks assigned yet. Check back later!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
