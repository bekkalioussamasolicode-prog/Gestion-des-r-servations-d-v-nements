<?php
session_start();
require '../config/db.php';

try {
  $sql = "SELECT * FROM events WHERE date_event >= CURDATE() ORDER BY date_event ASC";
  $stmt = $pdo->query($sql);
  $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  echo "Error " . $e->getMessage();
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
      margin: 0;
    }

    h2 {
      text-align: center;
      margin-top: 20px;
    }

    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 20px;
      padding: 20px;
    }

    .card {
      background-color: white;
      padding: 15px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }

    .card h3 {
      margin-top: 0;
    }

    .card p {
      margin: 5px 0;
      color: #555;
    }

    header {
      position: relative;
      height: 50px;
      margin-bottom: 10px;
    }

    .login-btn {
      position: absolute;
      right: 20px;
      top: 10px;
      padding: 8px 15px;
      background-color: #007BFF;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
    }

    .login-btn:hover {
      background-color: #0056b3;
    }

    .logInfo {
      position: absolute;
      right: 20px;
      top: 10px;
      padding: 8px 15px;
      background-color: #28a745;
      color: white;
      border-radius: 5px;
      font-weight: bold;
    }


    .book-btn {
      display: inline-block;
      margin-top: 10px;
      padding: 8px 12px;
      background-color: #28a745;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      text-align: center;
    }

    .book-btn:hover {
      background-color: #218838;
    }
  </style>
  <title>THE events list</title>
</head>

<body>
  <h2>Upcoming Events</h2>
  <header>

    <?php if (isset($_SESSION['user_name'])) : ?>
      <span class="logInfo">
        Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>
      </span>
      <a href="../auth/logout.php">Logout</a>
    <?php else: ?>
      <a href="../auth/login.php" class="login-btn">Login</a>
    <?php endif; ?>
  </header>
  <div class="cards">
    <?php foreach ($events as $event): ?>
      <div class="card">
        <h3><?= htmlspecialchars($event["title"]) ?></h3>
        <p><strong>Date:</strong> <?= htmlspecialchars($event["date_event"]) ?></p>
        <p><strong>Places:</strong> <?= htmlspecialchars($event["nbPlaces"]) ?> places availible</p>
        <p><strong>Price:</strong> <?= htmlspecialchars($event["price"]) ?> DH</p>
        <p><strong>Location</strong> <?= htmlspecialchars($event["location"]) ?></p>
        <?php if (isset($_SESSION['user_name'])) : ?>
          <a href="../reservations/book.php?id=<?= htmlspecialchars($event['id']) ?>" class="book-btn">Book</a>
        <?php else: ?>
          <a href="../auth/login.php">Login to book</a>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
  <?php if (empty($events)): ?>
    <p style="text-align:center;">No upcoming events for now.</p>
  <?php endif; ?>

</body>

</html>