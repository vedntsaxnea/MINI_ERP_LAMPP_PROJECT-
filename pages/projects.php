<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    $_SESSION['error'] = 'Access Denied: Admin privileges required.';
    header('Location: dashboard.php');
    exit;
}

$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_project_status'])) {
    $project_id = isset($_POST['project_id']) ? (int)$_POST['project_id'] : 0;
    $new_status = isset($_POST['status']) ? trim($_POST['status']) : '';

    if ($project_id <= 0 || !in_array($new_status, ['pending', 'active', 'completed'], true)) {
        $error_message = 'Invalid project status update request.';
    } else {
        try {
            $updateStmt = $pdo->prepare("UPDATE projects SET status = ? WHERE id = ?");
            $updateStmt->execute([$new_status, $project_id]);
            $success_message = 'Project status updated successfully!';
        } catch (PDOException $e) {
            $error_message = 'Error updating project status: ' . htmlspecialchars($e->getMessage());
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['update_project_status'])) {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'pending';

    if (empty($name)) {
        $error_message = 'Project name is required.';
    } else {
        try {
            $checkStmt = $pdo->prepare("SELECT id FROM projects WHERE LOWER(name) = LOWER(?)");
            $checkStmt->execute([$name]);
            if ($checkStmt->fetch()) {
                $error_message = 'A project with this name already exists. Please use a different name.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO projects (name, description, start_date, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $description, $start_date, $status]);
                $success_message = 'Project created successfully!';
            }
        } catch (PDOException $e) {
            $error_message = 'Error creating project: ' . htmlspecialchars($e->getMessage());
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT * FROM projects ORDER BY id DESC");
    $stmt->execute();
    $projects = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = 'Error fetching projects: ' . htmlspecialchars($e->getMessage());
    $projects = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Project Management - Footprints</title>
    <?php include 'head.php'; ?>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container-fluid mt-4">
        <div class="header-section">
            <div class="header-content">
                <h1>
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                    Project Management
                </h1>
                <p class="subtitle">Manage your projects efficiently</p>
            </div>
            <div class="header-actions d-flex flex-wrap gap-2">
                <a href="dashboard.php" class="btn btn-secondary">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    Dashboard
                </a>
                <button onclick="document.getElementById('project-form-section').scrollIntoView({behavior: 'smooth'})" class="btn btn-primary">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Add New Project
                </button>
            </div>
        </div>

        
        <div id="project-form-section" class="table-container">
            <h3 style="color: #ffffff; margin-bottom: 25px; font-size: 20px;">Add New Project</h3>
            
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
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            </svg>
                            Project Name
                        </label>
                        <input type="text" id="name" name="name" placeholder="Enter project name" required>
                    </div>

                    <div class="form-group-inline">
                        <label for="start_date">
                            <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            Start Date
                        </label>
                        <input type="date" id="start_date" name="start_date">
                    </div>

                    <div class="form-group-inline">
                        <label for="status">
                            <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                            </svg>
                            Status
                        </label>
                        <select id="status" name="status">
                            <option value="pending">Pending</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>

                <div class="form-group-inline">
                    <label for="description">
                        <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        Description
                    </label>
                    <textarea id="description" name="description" placeholder="Enter project description" rows="4"></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-large">Create Project</button>
                    <a href="dashboard.php" class="btn btn-secondary btn-large" style="text-decoration: none; justify-content: center;">Back to Dashboard</a>
                </div>
            </form>
        </div>

        
        <div class="table-container">
            <h3 style="color: #ffffff; margin-bottom: 25px; font-size: 20px;">All Projects</h3>
            
            <?php if (!empty($projects)): ?>
            <div class="employee-count">
                <span>Total Projects: <strong><?php echo count($projects); ?></strong></span>
            </div>

            <div class="table-responsive">
            <table class="employee-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Start Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $p): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p['id']); ?></td>
                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                        <td><?php echo htmlspecialchars(substr($p['description'], 0, 50)); ?><?php echo strlen($p['description']) > 50 ? '...' : ''; ?></td>
                        <td>
                            <form method="POST" class="d-flex align-items-center gap-2">
                                <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($p['id']); ?>">
                                <span class="status-badge status-<?php echo htmlspecialchars($p['status']); ?>">
                                    <select name="status" style="background: transparent; border: none; color: inherit; font: inherit; padding: 0; cursor: pointer;">
                                        <option value="pending" <?php echo $p['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="active" <?php echo $p['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="completed" <?php echo $p['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                </span>
                                <button type="submit" name="update_project_status" class="btn btn-outline-light btn-sm">Save</button>
                            </form>
                        </td>
                        <td><?php echo htmlspecialchars($p['start_date']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                    <polyline points="13 2 13 9 20 9"></polyline>
                </svg>
                <h3>No Projects Yet</h3>
                <p>Create your first project to get started.</p>
                <button onclick="document.getElementById('project-form-section').scrollIntoView({behavior: 'smooth'})" class="btn btn-primary">Add Project</button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>

