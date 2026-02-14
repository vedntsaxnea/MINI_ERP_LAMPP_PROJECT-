<?php
session_start();
require '../config/db.php';

// Check if session and role are set
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access Denied");
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: employees.php");
    exit;
}

$id = intval($_GET['id']);
$error = '';
$success = '';

// Get the employee and user_id
$stmt = $pdo->prepare("SELECT employees.*, users.email FROM employees JOIN users ON employees.user_id = users.id WHERE employees.id = ?");
$stmt->execute([$id]);
$emp = $stmt->fetch();

if (!$emp) {
    die("Employee not found!");
}

// Handle deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_delete'])) {
    try {
        $pdo->beginTransaction();

        // Delete from employees table (cascading should handle this, but explicit is safer)
        $deleteEmp = $pdo->prepare("DELETE FROM employees WHERE id = ?");
        $deleteEmp->execute([$id]);

        // Delete from users table
        $deleteUser = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $deleteUser->execute([$emp['user_id']]);

        $pdo->commit();
        
        header("refresh:2;url=employees.php");
        $success = "Employee deleted successfully! Redirecting...";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Employee - Footprints</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="employees-container">
        <div class="header-section">
            <div class="header-content">
                <h1>
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    Delete Employee
                </h1>
                <p class="subtitle">Confirm employee deletion</p>
            </div>
        </div>

        <div class="form-container">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            <?php elseif (!$error): ?>
                <div class="delete-confirmation">
                    <div class="warning-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3.05h16.94a2 2 0 0 0 1.71-3.05L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                    </div>

                    <h2>Are you sure?</h2>
                    <p class="warning-text">This action cannot be undone. You are about to permanently delete the following employee:</p>

                    <div class="employee-details">
                        <div class="detail-row">
                            <span class="detail-label">Name:</span>
                            <span class="detail-value"><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?= htmlspecialchars($emp['email']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Phone:</span>
                            <span class="detail-value"><?= htmlspecialchars($emp['phone']) ?></span>
                        </div>
                        <?php if (!empty($emp['position'])): ?>
                        <div class="detail-row">
                            <span class="detail-label">Position:</span>
                            <span class="detail-value"><?= htmlspecialchars($emp['position']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <form method="POST" class="delete-form">
                        <input type="hidden" name="confirm_delete" value="1">
                        <div class="form-actions">
                            <button type="submit" class="btn btn-danger btn-large">
                                <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                                Delete Permanently
                            </button>
                            <a href="employees.php" class="btn btn-secondary btn-large">
                                <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>