<?php
session_start();
require '../config/db.php';

try {
  // Only show events that are upcoming (date_event >= today) and order them by date ascending
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
  <link rel="stylesheet" href="catalogue.css">
  <title>THE events list</title>
</head>

<body>
  <h2>Upcoming Events</h2>
  <header>

    <?php if (isset($_SESSION['user_name'])) : ?>
      <span class="logInfo">
        Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>
      </span>
      <a href="../reservations/my_reservation.php" class="dashboard-link">Dashboard</a>
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
        <p><strong>Price:</strong> <?= $event["price"] ?> DH</p>
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