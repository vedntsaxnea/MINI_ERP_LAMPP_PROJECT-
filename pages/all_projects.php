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
    <title>All Projects - Footprints</title>
    <?php include 'head.php'; ?>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container-fluid mt-4">
        <div class="header-section">
            <div class="header-content">
                <h1>All Projects</h1>
                <p class="subtitle">View all projects in one page</p>
            </div>
            <div class="header-actions">
                <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
                <a href="projects.php" class="btn btn-primary">Add New Project</a>
            </div>
        </div>

        <div class="table-container">
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <?php if (!empty($projects)): ?>
            <div class="employee-count">
                <span>Total Projects: <strong><?php echo count($projects); ?></strong></span>
            </div>

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
                        <td><?php echo htmlspecialchars(substr((string)$p['description'], 0, 60)); ?><?php echo strlen((string)$p['description']) > 60 ? '...' : ''; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo htmlspecialchars($p['status']); ?>">
                                <?php echo htmlspecialchars(ucfirst($p['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($p['start_date']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <h3>No Projects Found</h3>
                <p>There are no projects to display right now.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
