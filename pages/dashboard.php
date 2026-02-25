<?php
require_once '../config/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['error'] = 'Please login to access this page.';
    header('Location: login.php');
    exit;
}

$employeeCount = 0;
$projectCount = 0;
$taskCount = 0;

try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM employees");
    $employeeCount = (int)($stmt->fetch()['total'] ?? 0);

    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM projects");
    $projectCount = (int)($stmt->fetch()['total'] ?? 0);

    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM tasks");
    $taskCount = (int)($stmt->fetch()['total'] ?? 0);
} catch (PDOException $e) {
    $employeeCount = 0;
    $projectCount = 0;
    $taskCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard - Mini ERP</title>
    <?php include 'head.php'; ?>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-5" style = "background:rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(25px);
    padding: 35px 40px;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    margin-bottom: 25px;
    margin-top: 20px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    gap: 20px;">
        <div class="row text-center">
            <div class="col-12">
                <h1 class="mb-4" style = "color: rgb(255, 255, 255);">Welcome to MiniERP Dashboard</h1>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4 text-center" style = "color: rgb(255, 255, 255);">Dashboard Overview</h2>

                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <p class="mb-4 text-center" style = "color: rgb(255, 255, 255);">Welcome to your Mini ERP system. Select a menu option to get started.</p>
                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                        <a href="employees.php" class="btn btn-primary">Manage Employees</a>
                        <a href="all_projects.php" class="btn btn-primary"> All Projects</a>
                        <a href="all_tasks.php" class="btn btn-primary"> All Tasks</a>
                    </div>
                <?php else: ?>
                    <p class="mb-4 text-center" style = "color: rgb(255, 255, 255);">Welcome to your workspace. View and manage your assigned tasks.</p>
                    <div class="text-center">
                        <a href="my_tasks.php" class="btn btn-primary">View My Tasks</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="container mt-4">
        <div class="row g-3 mb-3 text-center">
            <div class="col-12 col-md-4">
                <div style="background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(35px); -webkit-backdrop-filter: blur(35px); border: 1px solid rgba(255,255,255,0.25); border-radius: 12px; padding: 18px;">
                    <h6 style="color: rgba(255,255,255,0.8); margin-bottom: 8px;">Employees</h6>
                    <h3 style="color: rgb(255, 255, 255); margin: 0; font-size: 42px;"><?php echo $employeeCount; ?></h3>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div style="background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(35px); -webkit-backdrop-filter: blur(35px); border: 1px solid rgba(255,255,255,0.25); border-radius: 12px; padding: 18px;">
                    <h6 style="color: rgba(255,255,255,0.8); margin-bottom: 8px;">Projects</h6>
                    <h3 style="color: rgb(255, 255, 255); margin: 0; font-size: 42px;"><?php echo $projectCount; ?></h3>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div style="background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(35px); -webkit-backdrop-filter: blur(35px); border: 1px solid rgba(255,255,255,0.25); border-radius: 12px; padding: 18px;">
                    <h6 style="color: rgba(255,255,255,0.8); margin-bottom: 8px;">Tasks</h6>
                    <h3 style="color: rgb(255, 255, 255); margin: 0; font-size: 42px;"><?php echo $taskCount; ?></h3>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>