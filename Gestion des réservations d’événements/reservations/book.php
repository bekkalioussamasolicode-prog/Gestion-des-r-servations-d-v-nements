<?php
session_start();
require '../config/db.php';

// 1. Must be logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit();
}

// 2. Validate event ID from URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
  die("Invalid event.");
}

$message = "";
$messageType = "";

try {
  // 3. Fetch the event 
  $stmt = $pdo->prepare("SELECT * FROM events WHERE id = :id");
  $stmt->execute(['id' => $id]);
  $event = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$event) {
    die("Event not found.");
  }

  // 4. Handle booking on POST
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($event['nbPlaces'] <= 0) {
      $message = "Sorry, this event is sold out!";
      $messageType = "error";
    } else {
      // 5. Transaction: insert reservation + decrement places
      $pdo->beginTransaction();

      $stmt2 = $pdo->prepare("INSERT INTO reservations (user_id, event_id) VALUES (:user_id, :event_id)");
      $stmt2->execute([
        'user_id'  => $_SESSION['user_id'],
        'event_id' => $id
      ]);

      $stmt3 = $pdo->prepare("UPDATE events SET nbPlaces = nbPlaces - 1 WHERE id = :id");
      $stmt3->execute(['id' => $id]);

      $pdo->commit();

      $message = "Reservation confirmed!";
      $messageType = "success";

      // Refresh event data to show updated places
      $stmt->execute(['id' => $id]);
      $event = $stmt->fetch(PDO::FETCH_ASSOC);
    }
  }
} catch (PDOException $e) {
  $pdo->rollBack();
  $message = "Something went wrong. Please try again.";
  $messageType = "error";
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
  <link rel="stylesheet" href="../events/dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <title>Book Event - Event Flow</title>
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
        <div class="detail-card">
          <h2><?= htmlspecialchars($event['title']) ?></h2>
          <p><strong>Date:</strong> <?= htmlspecialchars($event['date_event']) ?></p>
          <p><strong>Location:</strong> <?= htmlspecialchars($event['location']) ?></p>
          <p><strong>Price:</strong> <?= htmlspecialchars($event['price']) ?> DH</p>
          <p><strong>Available places:</strong> <?= $event['nbPlaces'] ?></p>

          <?php if ($message): ?>
            <p class="<?= $messageType ?>"><?= $message ?></p>
          <?php endif; ?>

          <form method="POST">
            <button class="book-btn"
              <?= $event['nbPlaces'] <= 0 ? 'disabled' : '' ?>>
              <?= $event['nbPlaces'] <= 0 ? 'Sold Out' : 'Confirm Booking' ?>
            </button>
          </form>

          <a href="../events/catalogue.php" class="back">← Back to events</a>
        </div>
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