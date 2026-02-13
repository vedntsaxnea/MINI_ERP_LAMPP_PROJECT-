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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Mini ERP</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Welcome to MiniERP Dashboard</h1>
            <div class="user-info">
                <span>Logged in as: <strong><?php echo htmlspecialchars($_SESSION['email'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
                <span>Role: <strong><?php echo htmlspecialchars($_SESSION['role'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </header>
        
        <nav class="main-nav">
            <ul>
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="employees.php">Employees</a></li>
            </ul>
        </nav>
        
        <main>
            <div class="dashboard-content">
                <h2>Dashboard Overview</h2>
                <p>Welcome to your Mini ERP system. Select a menu option to get started.</p>
            </div>
        </main>
    </div>
</body>
</html>