<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['error'] = 'Please login to access this page.';
    header('Location: login.php');
    exit;
}

$employeeCount = 0;
$projectCount = 0;
$taskCount = 0;
$pendingCount = 0;
$inProgressCount = 0;
$completedCount = 0;

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

if (isset($_SESSION['role']) && $_SESSION['role'] === 'employee' && isset($_SESSION['user_id'])) {
    try {
        $employeeStmt = $pdo->prepare("SELECT id FROM employees WHERE user_id = ?");
        $employeeStmt->execute([$_SESSION['user_id']]);
        $employee = $employeeStmt->fetch();

        if ($employee) {
            $statusStmt = $pdo->prepare("SELECT status, COUNT(*) AS total FROM tasks WHERE assigned_to = ? GROUP BY status");
            $statusStmt->execute([$employee['id']]);
            $rows = $statusStmt->fetchAll();

            foreach ($rows as $row) {
                if ($row['status'] === 'pending') {
                    $pendingCount = (int)$row['total'];
                } elseif ($row['status'] === 'in_progress') {
                    $inProgressCount = (int)$row['total'];
                } elseif ($row['status'] === 'completed') {
                    $completedCount = (int)$row['total'];
                }
            }
        }
    } catch (PDOException $e) {
        $pendingCount = 0;
        $inProgressCount = 0;
        $completedCount = 0;
    }
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
    <div class="container mt-4 mt-md-5 px-3" style = "background:rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(25px);
    padding: 24px 20px;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    margin-bottom: 25px;
    margin-top: 16px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    gap: 20px;">
        <div class="row text-center">
            <div class="col-12">
                <h1 class="mb-3 mb-md-4" style = "color: rgb(255, 255, 255); font-size: clamp(1.6rem, 4.5vw, 2.4rem);">Welcome to MiniERP Dashboard</h1>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <h2 class="mb-3 mb-md-4 text-center" style = "color: rgb(255, 255, 255); font-size: clamp(1.2rem, 3.8vw, 1.8rem);">Dashboard Overview</h2>

                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <p class="mb-4 text-center" style = "color: rgb(255, 255, 255);">Welcome to your Mini ERP system. Select a menu option to get started.</p>
                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                        <a href="employees.php" class="btn btn-primary w-100 w-md-auto">Manage Employees</a>
                        <a href="all_projects.php" class="btn btn-primary w-100 w-md-auto"> All Projects</a>
                        <a href="all_tasks.php" class="btn btn-primary w-100 w-md-auto"> All Tasks</a>
                    </div>
                <?php else: ?>
                    <p class="mb-4 text-center" style = "color: rgb(255, 255, 255);">Welcome to your workspace. View and manage your assigned tasks.</p>
                    <div class="text-center">
                        <a href="my_tasks.php" class="btn btn-primary w-100 w-md-auto">View My Tasks</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="container mt-3 mt-md-4 px-3">
        <div class="row g-3 mb-3 text-center">
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <div class="col-12 col-sm-6 col-lg-4">
                <div style="background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(35px); -webkit-backdrop-filter: blur(35px); border: 1px solid rgba(255,255,255,0.25); border-radius: 12px; padding: 18px;">
                    <h6 style="color: rgba(255,255,255,0.8); margin-bottom: 8px;">Employees</h6>
                    <h3 style="color: rgb(255, 255, 255); margin: 0; font-size: clamp(30px, 6vw, 42px);"><?php echo $employeeCount; ?></h3>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4">
                <div style="background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(35px); -webkit-backdrop-filter: blur(35px); border: 1px solid rgba(255,255,255,0.25); border-radius: 12px; padding: 18px;">
                    <h6 style="color: rgba(255,255,255,0.8); margin-bottom: 8px;">Projects</h6>
                    <h3 style="color: rgb(255, 255, 255); margin: 0; font-size: clamp(30px, 6vw, 42px);"><?php echo $projectCount; ?></h3>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4">
                <div style="background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(35px); -webkit-backdrop-filter: blur(35px); border: 1px solid rgba(255,255,255,0.25); border-radius: 12px; padding: 18px;">
                    <h6 style="color: rgba(255,255,255,0.8); margin-bottom: 8px;">Tasks</h6>
                    <h3 style="color: rgb(255, 255, 255); margin: 0; font-size: clamp(30px, 6vw, 42px);"><?php echo $taskCount; ?></h3>
                </div>
            </div>
            <?php else: ?>
            <div class="col-12 col-sm-6 col-lg-4">
                <div style="background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(35px); -webkit-backdrop-filter: blur(35px); border: 1px solid rgba(255, 193, 7, 0.5); border-radius: 12px; padding: 18px;">
                    <h6 style="color: rgba(255,255,255,0.8); margin-bottom: 8px;">Pending</h6>
                    <h3 style="color: rgb(255, 255, 255); margin: 0; font-size: clamp(30px, 6vw, 42px);"><?php echo $pendingCount; ?></h3>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4">
                <div style="background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(35px); -webkit-backdrop-filter: blur(35px); border: 1px solid rgba(33, 150, 243, 0.5); border-radius: 12px; padding: 18px;">
                    <h6 style="color: rgba(255,255,255,0.8); margin-bottom: 8px;">In Progress</h6>
                    <h3 style="color: rgb(255, 255, 255); margin: 0; font-size: clamp(30px, 6vw, 42px);"><?php echo $inProgressCount; ?></h3>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4">
                <div style="background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(35px); -webkit-backdrop-filter: blur(35px); border: 1px solid rgba(76, 175, 80, 0.5); border-radius: 12px; padding: 18px;">
                    <h6 style="color: rgba(255,255,255,0.8); margin-bottom: 8px;">Completed</h6>
                    <h3 style="color: rgb(255, 255, 255); margin: 0; font-size: clamp(30px, 6vw, 42px);"><?php echo $completedCount; ?></h3>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
