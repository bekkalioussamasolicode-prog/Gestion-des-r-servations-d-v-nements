<?php
session_start();
require '../config/db.php';
// Is there an user online?
if (isset($_SESSION['user_id'])) {
  header("Location:../events/catalogue.php");
  exit;
}
$errors = [];
try {
  // Get the user that has the same email as email input
  $sql = "SELECT * FROM users WHERE email = :email";
  $stmt = $pdo->prepare($sql);
} catch (PDOException $e) {
  error_log($e->getMessage());
  $errors[] = "Database error. Please try again later.";
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
  $password = trim($_POST['psw']) ?? "";
  if (empty($email) || empty($password)) {
    $errors[] = "All fields are required!";
  }
  if (!$email) {
    $errors[] = "Invalid email format!";
  }
  if (empty($errors)) {
    try {
      $stmt->execute([
        "email" => $email
      ]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($user && password_verify($password, $user["password"])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];

        header("Location:../events/catalogue.php");
        exit;
      } else {
        $errors[] = "Email or password is incorrect!";
      }
    } catch (PDOException $e) {
      error_log($e->getMessage());
      $errors[] = "Something went wrong. Please try again later.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="login.css">
  <title>Login - Event Flow</title>
</head>

<body>
  <header class="mobile-header">
    <div class="logo">EventApp</div>
    <div class="hamburger" id="hamburger-menu">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </header>

  <div class="login-container">
    <div class="overlay" id="sidebar-overlay"></div>
    <aside class="login-sidebar" id="login-sidebar">
      <div class="sidebar-content">
        <h1>Welcome Back!</h1>
        <p>Log in to manage your events, check reservations, and discover exciting new experiences.</p>
        <ul class="feature-list">
          <li>✨ Seamless Bookings</li>
          <li>📅 Easy Management</li>
          <li>🎟️ Quick Ticketing</li>
        </ul>
      </div>
    </aside>

    <main class="login-main">
      <div class="login-box">
        <h2>Log-in</h2>
        <?php foreach ($errors as $err): ?>
          <p class="error-msg"><?= htmlspecialchars($err) ?></p>
        <?php endforeach; ?>
        <?php
        if (isset($_GET['signSucc'])) {
          echo "<p class='signSucc'>Account created successfully! Please log in.</p>";
        }
        ?>
        <form method="post">
          <div class="input-group">
            <label>Email</label>
            <input type="email" placeholder="Ex: user@email.com" name="email" required>
          </div>
          <div class="input-group">
            <label>Password</label>
            <input type="password" name="psw" required>
          </div>
          <button type="submit" class="login-btn">Log-in</button>
        </form>
        <p class="signup-link">Create an account <a href="signUp.php">Sign-Up</a></p>
      </div>
    </main>
  </div>

  <script>
    const hamburger = document.getElementById('hamburger-menu');
    const sidebar = document.getElementById('login-sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    function toggleSidebar() {
      hamburger.classList.toggle('active');
      sidebar.classList.toggle('active');
      overlay.classList.toggle('active');
    }

    hamburger.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', toggleSidebar);
  </script>
</body>

</html>