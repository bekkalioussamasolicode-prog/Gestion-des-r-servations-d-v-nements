<?php
session_start();
require '../config/db.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit();
}

try {
  // JOIN reservations + events to get full details
  $sql = "SELECT r.id, r.reservation_date,
                   e.title, e.date_event, e.location, e.price
            FROM reservations r
            JOIN events e ON r.event_id = e.id
            WHERE r.user_id = :user_id
            ORDER BY r.reservation_date DESC";

  $stmt = $pdo->prepare($sql);
  $stmt->execute(['user_id' => $_SESSION['user_id']]);
  $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Calculate total spent
  $totalSpent = array_sum(array_column($reservations, 'price'));
} catch (PDOException $e) {
  error_log($e->getMessage());
  $reservations = [];
  $totalSpent = 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Reservations</title>
  <link rel="stylesheet" href="my_reservation.css">
</head>

<body>

  <header>
    <span class="logInfo">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
    <a href="../auth/logout.php">Logout</a>
    <a href="../events/catalogue.php" class="back-link">← Back to events</a>
  </header>

  <div class="page">

    <h1>My Reservations</h1>

    <!-- Stats row -->
    <div class="stat-row">
      <div class="stat-card">
        <div class="stat-label">Total bookings</div>
        <div class="stat-value"><?= count($reservations) ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Amount spent</div>
        <div class="stat-value"><?= number_format($totalSpent, 2) ?> DH</div>
      </div>
    </div>

    <p class="section-title">Your bookings</p>

    <?php if (empty($reservations)): ?>
      <div class="empty">
        <p>You have no reservations yet.</p>
        <p><a href="../events/catalogue.php">Browse events →</a></p>
      </div>
    <?php else: ?>
      <?php foreach ($reservations as $res): ?>
        <div class="res-card">
          <div class="res-info">
            <span class="res-title">
              <?= htmlspecialchars($res['title']) ?>
            </span>
            <span class="res-meta">
              <?= htmlspecialchars($res['date_event']) ?> ·
              <?= htmlspecialchars($res['location']) ?> ·
              <?= number_format($res['price'], 2) ?> DH
            </span>
            <span class="res-meta">
              Booked on <?= date('M d, Y', strtotime($res['reservation_date'])) ?>
            </span>
          </div>
          <span class="badge">Confirmed</span>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>

</body>

</html>