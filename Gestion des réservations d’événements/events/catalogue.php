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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <title>Catalogue - Event Flow</title>
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
        <?php if (isset($_SESSION['user_name'])) : ?>
          <p>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>
          <ul class="feature-list">
            <li><a href="../events/catalogue.php"><i class="fas fa-home"></i> Catalogue</a></li>
            <li><a href="../reservations/my_reservation.php"><i class="fas fa-calendar"></i> My Reservations</a></li>
            <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a></li>
          </ul>
        <?php else: ?>
          <p>Discover exciting experiences.</p>
          <ul class="feature-list">
            <li><a href="../events/catalogue.php"><i class="fas fa-home"></i> Catalogue</a></li>
            <li><a href="../auth/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
          </ul>
        <?php endif; ?>
      </div>
    </aside>

    <main class="dashboard-main">
      <div class="main-content">
        <h2>Upcoming Events</h2>
        
        <div class="cards">
          <?php foreach ($events as $event): ?>
            <div class="card">
              <h3><?= htmlspecialchars($event["title"]) ?></h3>
              <p><strong>Date:</strong> <?= htmlspecialchars($event["date_event"]) ?></p>
              <p><strong>Places:</strong> <?= htmlspecialchars($event["nbPlaces"]) ?> places availible</p>
              <p><strong>Price:</strong> <?= $event["price"] ?> DH</p>
              <p><strong>Location:</strong> <?= htmlspecialchars($event["location"]) ?></p>
              <?php if (isset($_SESSION['user_name'])) : ?>
                <a href="../reservations/book.php?id=<?= htmlspecialchars($event['id']) ?>" class="book-btn">Book</a>
              <?php else: ?>
                <a href="../auth/login.php" class="book-btn">Login to book</a>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
        
        <?php if (empty($events)): ?>
          <p style="text-align:center;">No upcoming events for now.</p>
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