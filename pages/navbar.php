<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">Mini ERP</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="employees.php">Employees</a></li>
          <li class="nav-item"><a class="nav-link" href="all_projects.php">Projects</a></li>
          <li class="nav-item"><a class="nav-link" href="all_tasks.php">Tasks</a></li>
        <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="my_tasks.php">My Tasks</a></li>
        <?php endif; ?>
      </ul>
      <span class="navbar-text me-3">
        <small><?php echo htmlspecialchars($_SESSION['email'] ?? 'User', ENT_QUOTES, 'UTF-8'); ?></small>
      </span>
      <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
  </div>
</nav>
