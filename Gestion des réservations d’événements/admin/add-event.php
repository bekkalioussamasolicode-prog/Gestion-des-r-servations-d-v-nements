<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_name']) || $_SESSION['user_name'] !== "oussama") {
  header("Location: ../auth/login.php");
  exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title    = trim($_POST['title']);
  $date     = trim($_POST['date_event']);
  $location = trim($_POST['location']);
  $places   = (int) $_POST['nbPlaces'];
  $price    = (float) $_POST['price'];

  // Validation
  if (empty($title) || empty($date) || empty($location)) {
    $errors[] = "All fields are required.";
  }
  if ($places <= 0) {
    $errors[] = "Number of places must be greater than 0.";
  }
  if ($price < 0) {
    $errors[] = "Price cannot be negative.";
  }

  if (empty($errors)) {
    try {
      $stmt = $pdo->prepare("INSERT INTO events (title, date_event, nbPlaces, price, location)
                                   VALUES (:title, :date_event, :nbPlaces, :price, :location)");
      $stmt->execute([
        'title'      => $title,
        'date_event' => $date,
        'nbPlaces'   => $places,
        'price'      => $price,
        'location'   => $location,
      ]);
      $success = true;
    } catch (PDOException $e) {
      error_log($e->getMessage());
      $errors[] = "Something went wrong. Please try again.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Event</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: Arial, sans-serif;
      background: #f5f5f5;
      min-height: 100vh;
    }

    header {
      background: #1a1a2e;
      padding: 14px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      color: white;
      font-weight: bold;
      font-size: 16px;
    }

    .back-link {
      color: #aaa;
      font-size: 13px;
      text-decoration: none;
    }

    .back-link:hover {
      color: white;
    }

    .page {
      max-width: 500px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .card {
      background: white;
      border-radius: 12px;
      padding: 28px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }

    h2 {
      font-size: 18px;
      margin-bottom: 20px;
      color: #222;
    }

    label {
      display: block;
      font-size: 13px;
      color: #555;
      margin-bottom: 5px;
      margin-top: 14px;
    }

    input {
      width: 100%;
      padding: 10px 12px;
      font-size: 14px;
      border: 1px solid #ddd;
      border-radius: 8px;
      outline: none;
    }

    input:focus {
      border-color: #007BFF;
    }

    .submit-btn {
      width: 100%;
      margin-top: 22px;
      padding: 11px;
      background: #1a1a2e;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      cursor: pointer;
    }

    .submit-btn:hover {
      background: #2d2d50;
    }

    .error-msg {
      color: #dc3545;
      font-size: 13px;
      margin-top: 12px;
    }

    .success-msg {
      background: #d1e7dd;
      color: #0a3622;
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 16px;
      font-size: 14px;
    }

    .success-msg a {
      color: #0a3622;
      font-weight: bold;
    }
  </style>
</head>

<body>

  <header>
    <span class="logo">Admin Panel</span>
    <a href="index.php" class="back-link">← Back to events</a>
  </header>

  <div class="page">
    <div class="card">
      <h2>Add a new event</h2>

      <?php if ($success): ?>
        <div class="success-msg">
          Event added successfully! <a href="index.php">View all events →</a>
        </div>
      <?php endif; ?>

      <?php foreach ($errors as $err): ?>
        <p class="error-msg"><?= htmlspecialchars($err) ?></p>
      <?php endforeach; ?>

      <form method="POST">
        <label>Title</label>
        <input type="text" name="title"
          value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>"
          placeholder="e.g. Tech Summit 2025" required>

        <label>Date</label>
        <input type="date" name="date_event"
          value="<?= isset($_POST['date_event']) ? htmlspecialchars($_POST['date_event']) : '' ?>"
          required>

        <label>Location</label>
        <input type="text" name="location"
          value="<?= isset($_POST['location']) ? htmlspecialchars($_POST['location']) : '' ?>"
          placeholder="e.g. Casablanca" required>

        <label>Number of places</label>
        <input type="number" name="nbPlaces" min="1"
          value="<?= isset($_POST['nbPlaces']) ? (int)$_POST['nbPlaces'] : '' ?>"
          placeholder="e.g. 100" required>

        <label>Price (DH)</label>
        <input type="number" name="price" min="0" step="0.01"
          value="<?= isset($_POST['price']) ? (float)$_POST['price'] : '' ?>"
          placeholder="e.g. 150.00" required>

        <button type="submit" class="submit-btn">Add event</button>
      </form>
    </div>
  </div>

</body>

</html>