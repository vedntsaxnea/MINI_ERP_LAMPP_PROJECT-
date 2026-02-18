<?php
session_start();
require '../config/db.php';

// Check if session and role are set
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access Denied");
}

$sql = "SELECT employees.*, users.email, users.is_active FROM employees JOIN users ON employees.user_id = users.id";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$employees = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Employee Management - Footprints</title>
    <?php include 'head.php'; ?>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container-fluid mt-4">
        <div class="header-section">
            <div class="header-content">
                <h1>
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    Employee Management
                </h1>
                <p class="subtitle">Manage your team members efficiently</p>
            </div>
            <div class="header-actions">
                <a href="dashboard.php" class="btn btn-secondary">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    Dashboard
                </a>
                <a href="employee_create.php" class="btn btn-primary">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Add New Employee
                </a>
            </div>
        </div>

        <div class="table-container">
            <div class="employee-count">
                <span>Total Employees: <strong><?= count($employees) ?></strong></span>
            </div>
            
            <?php if (empty($employees)): ?>
                <div class="empty-state">
                    <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <h3>No employees found</h3>
                    <p>Start by adding your first employee to the system.</p>
                    <a href="employee_create.php" class="btn btn-primary">Add Employee</a>
                </div>
            <?php else: ?>
                <table class="employee-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $emp): ?>
                        <tr>
                            <td>
                                <div class="employee-name">
                                    <div class="avatar">
                                        <?= strtoupper(substr(htmlspecialchars($emp['first_name']), 0, 1)) ?>
                                    </div>
                                    <span><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($emp['email']) ?></td>
                            <td><?= htmlspecialchars($emp['phone']) ?></td>
                            <td>
                                <?php if ($emp['is_active'] == 1): ?>
                                    <span class="status-badge status-active">Active</span>
                                <?php else: ?>
                                    <span class="status-badge status-inactive">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="employee_edit.php?id=<?= htmlspecialchars($emp['id']) ?>" class="btn-icon-action btn-edit" title="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </a>
                                    <a href="employee_delete.php?id=<?= htmlspecialchars($emp['id']) ?>" class="btn-icon-action btn-delete" title="Delete" onclick="return confirm('Are you sure you want to delete this employee?')">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>