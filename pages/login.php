<?php 
require_once '../config/db.php'; 

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Ensure a default admin exists (only if missing)
$default_admin_email = 'admin@test.com';
$default_admin_password = 'admin123';
try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$default_admin_email]);
    if (!$stmt->fetch()) {
        $hash = password_hash($default_admin_password, PASSWORD_DEFAULT);
        $insert = $pdo->prepare('INSERT INTO users (email, password, Role) VALUES (?, ?, ?)');
        $insert->execute([$default_admin_email, $hash, 'admin']);
    }
} catch (PDOException $e) {
    error_log('Admin auto-create failed: ' . $e->getMessage());
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mini ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5">
                <div class="login-container">
        <h2 class="text-center">Login</h2>
        <?php 
        if(isset($_SESSION['error'])) { 
            echo "<p class='error alert alert-danger text-center'>" . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . "</p>"; 
            unset($_SESSION['error']); 
        } 
        if(isset($_SESSION['success'])) { 
            echo "<p class='success alert alert-success text-center'>" . htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') . "</p>"; 
            unset($_SESSION['success']); 
        } 
        ?>
        <form action="auth_login.php" method="POST" class="w-100">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-group mb-3">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="form-group mb-4">
                <label for="password">Password </label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary d-block mx-auto px-5">Submit</button>
        </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>