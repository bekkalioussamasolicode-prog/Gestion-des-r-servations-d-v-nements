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
  <title>Book Event</title>
  <link rel="stylesheet" href="book.css">
</head>

<body>

  <header>
    <span class="logInfo">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
    <a href="../auth/logout.php">Logout</a>
  </header>

  <div class="card">
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

</body>

</html>