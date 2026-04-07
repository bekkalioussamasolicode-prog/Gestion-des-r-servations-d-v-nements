<?php
require '../config/db.php';

try {
  $sql = "INSERT INTO users (name, email, password) VALUES (?,?,?)";
  $stmt = $pdo->prepare($sql);
} catch (PDOException $e) {
  echo $e->getMessage();
}
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
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    // This to make error message easy to understand
    try {
      $stmt->execute([
        $name,
        $email,
        $hashedPassword
      ]);
      header("Location: login.php?signSucc=true");
      exit;
    } catch (PDOException $e) {
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
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f5f5f5;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    h2 {
      margin-top: 20px;
    }

    form {
      background-color: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    label {
      font-weight: bold;
    }

    input {
      padding: 10px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    button {
      padding: 10px;
      border-radius: 5px;
      border: none;
      background-color: #007BFF;
      color: white;
      font-size: 16px;
      cursor: pointer;
    }

    button:hover {
      background-color: #0056b3;
    }

    .error-msg {
      color: red;
    }
  </style>
  <title>Sign Up</title>
</head>

<body>
  <h2>Sign Up</h2>
  <?php foreach ($errors as $err): ?>
    <p class="error-msg"><?= $err ?></p>
  <?php endforeach; ?>
  <form method="post">
    <label>Name</label>
    <input type="text" name="name" placeholder="ex: oussama" required>
    <label>Email</label>
    <input type="email" placeholder="ex: user@email.com" name="email" required>
    <label>Confirm email</label>
    <input type="email" placeholder="ex: user@email.com" name="emailConf" required>
    <label>Password</label>
    <input type="password" name="psw" required>
    <button type="submit">Sign Up</button>
  </form>
  <p>You have an account <a href="login.php">Log-In</a></p>
</body>

</html>