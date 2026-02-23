<?php
require_once '../config/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['error'] = 'Please login to access this page.';
    header('Location: login.php');
    exit;
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
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4" style = "color: rgb(255, 255, 255);">Welcome to MiniERP Dashboard</h1>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4" style = "color: rgb(255, 255, 255);">Dashboard Overview</h2>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <p class="mb-4" style = "color: rgb(255, 255, 255);">Welcome to your Mini ERP system. Select a menu option to get started.</p>
                    <div class="d-flex gap-3">
                        <a href="employees.php" class="btn btn-primary">Manage Employees</a>
                        <a href="all_projects.php" class="btn btn-primary"> All Projects</a>
                        <a href="all_tasks.php" class="btn btn-primary"> All Tasks</a>
                    </div>
                <?php else: ?>
                    <p class="mb-4" style = "color: rgb(255, 255, 255);">Welcome to your workspace. View and manage your assigned tasks.</p>
                    <div>
                        <a href="my_tasks.php" class="btn btn-primary">View My Tasks</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>