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
  <link rel="stylesheet" href="login.css">
  <title>Login</title>
</head>

<body>
  <h2>Log-in</h2>
  <?php foreach ($errors as $err): ?>
    <p class="error-msg"><?= $err ?></p>
  <?php endforeach; ?>
  <?php
  if (isset($_GET['signSucc'])) {
    echo "<p class='signSucc'>Account created successfully! Please log in.</p>";
  }
  ?>
  <form method="post">
    <label>Email</label>
    <input type="email" placeholder="ex: user@email.com" name="email" required>
    <label>Password</label>
    <input type="password" name="psw" required>
    <button type="submit">Log-in</button>
  </form>
  <p>Create an account <a href="signUp.php">Sign-Up</a></p>
</body>

</html>