<?php
require_once '../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['error'] = 'Invalid security token. Please try again.';
    header('Location: login.php');
    exit;
}

if (empty($_POST['email']) || empty($_POST['password'])) {
    $_SESSION['error'] = 'Please provide both email and password.';
    header('Location: login.php');
    exit;
}

$email = trim($_POST['email']);
$password = $_POST['password'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Invalid email format.';
    header('Location: login.php');
    exit;
}

try {

    $stmt = $pdo->prepare("SELECT id, email, password, Role, is_active FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {

        if ($user['is_active'] == 0) {
            $_SESSION['error'] = 'Your account has been deactivated. Please contact your administrator.';
            header('Location: login.php');
            exit;
        }

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['Role'];
        $_SESSION['logged_in'] = true;

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        header('Location: dashboard.php');
        exit;
    } else {

        $_SESSION['error'] = 'Invalid email or password.';
        header('Location: login.php');
        exit;
    }
} catch (PDOException $e) {

    error_log('Login error: ' . $e->getMessage());
    $_SESSION['error'] = 'An error occurred. Please try again later.';
    header('Location: login.php');
    exit;
}
?>
