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
  <title>My Reservations - Event Flow</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../events/dashboard.css">
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

  <div class="dashboard-container">
    <div class="overlay" id="sidebar-overlay"></div>
    <aside class="dashboard-sidebar" id="dashboard-sidebar">
      <div class="sidebar-content">
        <h1>Dashboard</h1>
        <p>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>
        <ul class="feature-list">
          <li><a href="../events/catalogue.php"><i class="fas fa-home"></i> Catalogue</a></li>
          <li><a href="my_reservation.php"><i class="fas fa-calendar"></i> My Reservations</a></li>
          <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a></li>
        </ul>
      </div>
    </aside>

    <main class="dashboard-main">
      <div class="main-content">
        <h2 class="section-title">My Reservations</h2>

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

        <p class="section-title" style="margin-top: 2rem;">Your bookings</p>

        <?php if (empty($reservations)): ?>
          <div class="empty detail-card">
            <p>You have no reservations yet.</p>
            <a href="../events/catalogue.php" style="color:var(--primary); font-weight:600; text-decoration:none;">Browse events →</a>
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
    </main>
  </div>

  <script>
    const hamburger = document.getElementById('hamburger-menu');
    const sidebar = document.getElementById('dashboard-sidebar');
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