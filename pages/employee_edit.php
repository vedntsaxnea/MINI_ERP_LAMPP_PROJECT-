<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access Denied");
}

if (!isset($_GET['id'])) {
    header("Location: employees.php");
    exit;
}

$id = intval($_GET['id']);
$error = '';
$success = '';

$stmt = $pdo->prepare("SELECT employees.*, users.email FROM employees JOIN users ON employees.user_id = users.id WHERE employees.id = ?");
$stmt->execute([$id]);
$emp = $stmt->fetch();

if (!$emp) {
    die("Employee not found!");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $position = trim($_POST['position'] ?? '');

    if (empty($first_name) || empty($last_name)) {
        $error = "First name and last name are required!";
    } elseif (strlen($phone) > 15) {
        $error = "Phone number must be 15 characters or less!";
    } else {
        try {
            $update = $pdo->prepare("UPDATE employees SET first_name=?, last_name=?, phone=?, position=? WHERE id=?");
            $update->execute([$first_name, $last_name, $phone, $position, $id]);
            $success = "Employee updated successfully!";

            $stmt = $pdo->prepare("SELECT employees.*, users.email FROM employees JOIN users ON employees.user_id = users.id WHERE employees.id = ?");
            $stmt->execute([$id]);
            $emp = $stmt->fetch();
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Employee - Footprints</title>
    <?php include 'head.php'; ?>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container-fluid mt-4">
        <div class="header-section">
            <div class="header-content">
                <h1>
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    Edit Employee
                </h1>
                <p class="subtitle">Update employee information</p>
            </div>
            <div class="header-actions d-flex flex-wrap gap-2">
                <a href="employees.php" class="btn btn-secondary">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Back to List
                </a>
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
            <?php endif; ?>

            <form method="POST" class="employee-form">
                <div class="form-row">
                    <div class="form-group-inline">
                        <label for="first_name">
                            <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            First Name *
                        </label>
                        <input type="text" id="first_name" name="first_name" placeholder="Enter first name" 
                               value="<?= htmlspecialchars($emp['first_name']) ?>" required>
                    </div>

                    <div class="form-group-inline">
                        <label for="last_name">
                            <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            Last Name *
                        </label>
                        <input type="text" id="last_name" name="last_name" placeholder="Enter last name" 
                               value="<?= htmlspecialchars($emp['last_name']) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group-inline">
                        <label for="email">
                            <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            Email (Read-only)
                        </label>
                        <input type="email" id="email" name="email" 
                               value="<?= htmlspecialchars($emp['email']) ?>" disabled>
                    </div>

                    <div class="form-group-inline">
                        <label for="phone">
                            <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            Phone *
                        </label>
                        <input type="tel" id="phone" name="phone" placeholder="+1234567890" 
                               value="<?= htmlspecialchars($emp['phone']) ?>" maxlength="15" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group-inline">
                        <label for="position">
                            <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 7h-9"></path>
                                <path d="M14 17H5"></path>
                                <circle cx="17" cy="17" r="3"></circle>
                                <circle cx="7" cy="7" r="3"></circle>
                            </svg>
                            Position
                        </label>
                        <input type="text" id="position" name="position" placeholder="e.g., Developer, Manager" 
                               value="<?= htmlspecialchars($emp['position'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-actions d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary btn-large">
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Update Employee
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
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
