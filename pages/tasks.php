<?php
session_start();
require '../config/db.php';

// Check if user is Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    $_SESSION['error'] = 'Access Denied: Admin privileges required.';
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

// Fetch Projects for the dropdown
try {
    $projects = $pdo->query("SELECT id, name FROM projects ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    $error_message = 'Error fetching projects: ' . htmlspecialchars($e->getMessage());
    $projects = [];
}

// Fetch Employees for the dropdown
try {
    $employees = $pdo->query("SELECT id, first_name, last_name FROM employees ORDER BY first_name ASC")->fetchAll();
} catch (PDOException $e) {
    $error_message = 'Error fetching employees: ' . htmlspecialchars($e->getMessage());
    $employees = [];
}

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $project_id = isset($_POST['project_id']) && !empty($_POST['project_id']) ? intval($_POST['project_id']) : 0;
    $assigned_to = isset($_POST['assigned_to']) && !empty($_POST['assigned_to']) ? intval($_POST['assigned_to']) : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $priority = isset($_POST['priority']) ? trim($_POST['priority']) : 'medium';
    $due_date = isset($_POST['due_date']) && !empty($_POST['due_date']) ? trim($_POST['due_date']) : NULL;

    // Validation
    if (empty($name)) {
        $error_message = 'Task name is required.';
    } elseif ($project_id <= 0) {
        $error_message = 'Please select a valid project.';
    } elseif ($assigned_to <= 0) {
        $error_message = 'Please assign the task to a valid employee.';
    } else {
        try {
            $sql = "INSERT INTO tasks (project_id, assigned_to, name, description, priority, due_date) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$project_id, $assigned_to, $name, $description, $priority, $due_date]);
            $_SESSION['success'] = 'Task assigned successfully!';
            header('Location: tasks.php');
            exit;
        } catch (PDOException $e) {
            $error_message = 'Error creating task: ' . htmlspecialchars($e->getMessage());
        }
    }
}

// Fetch Existing Tasks to display
try {
    $sql = "SELECT tasks.*, projects.name as project_name, employees.first_name, employees.last_name 
            FROM tasks 
            JOIN projects ON tasks.project_id = projects.id 
            LEFT JOIN employees ON tasks.assigned_to = employees.id
            ORDER BY tasks.id DESC";
    $tasks = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    $error_message = 'Error fetching tasks: ' . htmlspecialchars($e->getMessage());
    $tasks = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Task Management - Footprints</title>
    <?php include 'head.php'; ?>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container-fluid mt-4">
        <div class="header-section">
            <div class="header-content">
                <h1>
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 11l3 3L22 4"></path>
                        <path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Task Management
                </h1>
                <p class="subtitle">Manage your tasks and assignments efficiently</p>
            </div>
            <div class="header-actions">
                <a href="dashboard.php" class="btn btn-secondary">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    Dashboard
                </a>
                <button onclick="document.getElementById('task-form-section').scrollIntoView({behavior: 'smooth'})" class="btn btn-primary">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Add New Task
                </button>
            </div>
        </div>

        <!-- Form Section -->
        <div id="task-form-section" class="table-container">
            <h3 style="color: #ffffff; margin-bottom: 25px; font-size: 20px;">Assign New Task</h3>
            
            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="employee-form">
                <div class="form-row">
                    <div class="form-group-inline">
                        <label for="name">
                            <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                            </svg>
                            Task Name
                        </label>
                        <input type="text" id="name" name="name" placeholder="Enter task name" required>
                    </div>

                    <div class="form-group-inline">
                        <label for="project_id">
                            <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            </svg>
                            Select Project
                        </label>
                        <select id="project_id" name="project_id" required>
                            <option value="">-- Select Project --</option>
                            <?php foreach ($projects as $p): ?>
                                <option value="<?php echo htmlspecialchars($p['id']); ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group-inline">
                        <label for="assigned_to">
                            <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            Assign To
                        </label>
                        <select id="assigned_to" name="assigned_to" required>
                            <option value="">-- Select Employee --</option>
                            <?php foreach ($employees as $e): ?>
                                <option value="<?php echo htmlspecialchars($e['id']); ?>"><?php echo htmlspecialchars($e['first_name'] . ' ' . $e['last_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group-inline">
                        <label for="priority">
                            <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="12 2 15.09 10.26 24 12.52 18 18.69 19.82 27.78 12 23.77 4.18 27.78 6 18.69 0 12.52 8.91 10.26 12 2"></polygon>
                            </svg>
                            Priority
                        </label>
                        <select id="priority" name="priority">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>

                    <div class="form-group-inline">
                        <label for="due_date">
                            <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            Due Date
                        </label>
                        <input type="date" id="due_date" name="due_date">
                    </div>
                </div>

                <div class="form-group-inline">
                    <label for="description">
                        <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        Description
                    </label>
                    <textarea id="description" name="description" placeholder="Enter task description" rows="4"></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-large">Assign Task</button>
                    <a href="dashboard.php" class="btn btn-secondary btn-large" style="text-decoration: none; justify-content: center;">Back to Dashboard</a>
                </div>
            </form>
        </div>

        <!-- Tasks Table Section -->
        <div class="table-container">
            <h3 style="color: #ffffff; margin-bottom: 25px; font-size: 20px;">All Tasks</h3>
            
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
                    <?php foreach ($tasks as $t): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['name']); ?></td>
                        <td><?php echo htmlspecialchars($t['project_name']); ?></td>
                        <td><?php echo htmlspecialchars(($t['first_name'] ?? 'Unassigned') . ' ' . ($t['last_name'] ?? '')); ?></td>
                        <td>
                            <span class="priority-badge priority-<?php echo htmlspecialchars($t['priority']); ?>">
                                <?php echo htmlspecialchars(ucfirst($t['priority'])); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($t['due_date'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo htmlspecialchars($t['status']); ?>">
                                <?php echo htmlspecialchars(ucfirst($t['status'])); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                    <polyline points="13 2 13 9 20 9"></polyline>
                </svg>
                <h3>No Tasks Yet</h3>
                <p>Create your first task to get started.</p>
                <button onclick="document.getElementById('task-form-section').scrollIntoView({behavior: 'smooth'})" class="btn btn-primary">Add Task</button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
