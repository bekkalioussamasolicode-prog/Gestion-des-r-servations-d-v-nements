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
      background: white;
      border-bottom: 1px solid #eee;
      padding: 12px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    header span {
      font-weight: bold;
      font-size: 16px;
    }

    .back-link {
      font-size: 13px;
      color: #007BFF;
      text-decoration: none;
    }

    .back-link:hover {
      text-decoration: underline;
    }

    .page {
      max-width: 800px;
      margin: 24px auto;
      padding: 0 20px;
    }

    /* Stat cards */
    .stat-row {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 12px;
      margin-bottom: 24px;
    }

    .stat-card {
      background: white;
      border-radius: 10px;
      padding: 16px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
    }

    .stat-label {
      font-size: 13px;
      color: #888;
      margin-bottom: 6px;
    }

    .stat-value {
      font-size: 26px;
      font-weight: bold;
      color: #333;
    }

    /* Reservation cards */
    .section-title {
      font-size: 15px;
      font-weight: bold;
      color: #333;
      margin-bottom: 12px;
    }

    .res-card {
      background: white;
      border: 1px solid #eee;
      border-radius: 10px;
      padding: 16px;
      margin-bottom: 12px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    .res-info {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .res-title {
      font-size: 15px;
      font-weight: bold;
      color: #222;
    }

    .res-meta {
      font-size: 13px;
      color: #777;
    }

    .badge {
      font-size: 12px;
      padding: 5px 12px;
      border-radius: 20px;
      background: #d4edda;
      color: #155724;
      white-space: nowrap;
      font-weight: bold;
    }

    .empty {
      text-align: center;
      padding: 60px 20px;
      color: #999;
      font-size: 15px;
    }

    .empty a {
      color: #007BFF;
      text-decoration: none;
    }
  </style>
</head>

<body>

  <header>
    <span>My Reservations</span>
    <a href="../events/catalogue.php" class="back-link">← Back to events</a>
  </header>

  <div class="page">

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
        <p><a href="catalogue.php">Browse events →</a></p>
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