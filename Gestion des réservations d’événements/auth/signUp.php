<?php
require '../config/db.php';

$sql = "INSERT INTO users (name, email, password) VALUES (?,?,?)";
$stmt = $pdo->prepare($sql);

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

  $name = trim($_POST['name']) ?? "";
  $password = trim($_POST['psw']) ?? "";
  $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
  $emailConf = trim($_POST['emailConf']) ?? "";

  if (empty($name) || empty($password) || empty($emailConf)) {
    $errors[] = "All fields are require!.";
  }

  if (!$email) {
    $errors[] = "Invalid email format!";
  }

  if ($email !== $emailConf) {
    $errors[] = "Emails must match!";
  }

  if (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
    $errors[] = "Password must have at least one uppercase letter and one digit!";
  }

  if (empty($errors)) {
    // hash the password before saving to database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
      $stmt->execute([
        $name,
        $email,
        $hashedPassword
      ]);
      header("Location: login.php?signSucc=true");
      exit;
    } catch (PDOException $e) {
      // 23000 is the code for duplicate entry (email already exists)
      if ($e->getCode() == 23000) {
        $errors[] = "Email already exists!";
      } else {
        $errors[] = "Something went wrong!";
      }
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
  <link rel="stylesheet" href="signUp.css">
  <title>Sign Up - Event Flow</title>
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
        <h1>Join Us!</h1>
        <p>Create an account to discover exciting new experiences and effortlessly manage your event bookings.</p>
        <ul class="feature-list">
          <li>✨ Seamless Bookings</li>
          <li>📅 Easy Management</li>
          <li>🎟️ Quick Ticketing</li>
        </ul>
      </div>
    </aside>

    <main class="login-main">
      <div class="login-box">
        <h2>Sign Up</h2>
        <?php foreach ($errors as $err): ?>
          <p class="error-msg"><?= htmlspecialchars($err) ?></p>
        <?php endforeach; ?>
        <form method="post">
          <div class="input-group">
            <label>Name</label>
            <input type="text" name="name" placeholder="ex: oussama" required>
          </div>
          <div class="input-group">
            <label>Email</label>
            <input type="email" placeholder="ex: user@email.com" name="email" required>
          </div>
          <div class="input-group">
            <label>Confirm Email</label>
            <input type="email" placeholder="ex: user@email.com" name="emailConf" required>
          </div>
          <div class="input-group">
            <label>Password</label>
            <input type="password" name="psw" placeholder="********" required>
          </div>
          <button type="submit" class="login-btn">Sign Up</button>
        </form>
        <p class="signup-link">You have an account? <a href="login.php">Log-In</a></p>
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