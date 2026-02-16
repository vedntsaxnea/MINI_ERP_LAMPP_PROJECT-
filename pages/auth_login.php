<?php
require_once '../config/db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['error'] = 'Invalid security token. Please try again.';
    header('Location: login.php');
    exit;
}

// Validate input
if (empty($_POST['email']) || empty($_POST['password'])) {
    $_SESSION['error'] = 'Please provide both email and password.';
    header('Location: login.php');
    exit;
}

$email = trim($_POST['email']);
$password = $_POST['password'];

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Invalid email format.';
    header('Location: login.php');
    exit;
}

try {
    // Fetch user from database
    $stmt = $pdo->prepare("SELECT id, email, password, Role, is_active FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verify user exists and password is correct
    if ($user && password_verify($password, $user['password'])) {
        // Check if user account is active
        if ($user['is_active'] == 0) {
            $_SESSION['error'] = 'Your account has been deactivated. Please contact your administrator.';
            header('Location: login.php');
            exit;
        }
        
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['Role'];
        $_SESSION['logged_in'] = true;
        
        // Generate new CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        // Redirect to dashboard
        header('Location: dashboard.php');
        exit;
    } else {
        // Invalid credentials
        $_SESSION['error'] = 'Invalid email or password.';
        header('Location: login.php');
        exit;
    }
} catch (PDOException $e) {
    // Log error (in production, log to file instead of displaying)
    error_log('Login error: ' . $e->getMessage());
    $_SESSION['error'] = 'An error occurred. Please try again later.';
    header('Location: login.php');
    exit;
}
?>
