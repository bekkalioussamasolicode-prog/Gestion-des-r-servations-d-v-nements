<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_name']) || $_SESSION['user_name'] !== "oussama") {
  die("You're not an admin");
}

$search = isset($_GET['search']) ?  trim($_GET['search']) : '';

try {
  $sql = "
  SELECT e.*,
  COUNT(r.id) AS reservation_count
  FROM events e
  LEFT JOIN reservations r ON e.id = r.event_id
  WHERE e.title LIKE :search
  GROUP BY e.id
  ORDER BY e.date_event DESC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(['search' => '%' . $search . '%']);
  $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
  // count how many events are there
  $totalEvents = count($events);
  // get reservation_count column from events arr and count them
  $totalReservations = array_sum(array_column($events, 'reservation_count'));
  // get only the nbPlaces from events that has 0 and count them
  $soldOut = count(array_filter($events, fn($e) => $e['nbPlaces'] == 0));
} catch (PDOException $e) {
  error_log($e->getMessage());
  $events = [];
  $totalEvents = 0;
  $totalReservations = 0;
  $soldOut = 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel</title>
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

    .logout-link {
      color: #aaa;
      font-size: 13px;
      text-decoration: none;
    }

    .logout-link:hover {
      color: white;
    }

    .page {
      max-width: 960px;
      margin: 24px auto;
      padding: 0 20px;
    }

    /* Stats */
    .stat-row {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
      margin-bottom: 24px;
    }

    .stat-card {
      background: white;
      border-radius: 10px;
      padding: 16px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    }

    .stat-label {
      font-size: 13px;
      color: #888;
      margin-bottom: 6px;
    }

    .stat-value {
      font-size: 26px;
      font-weight: bold;
      color: #222;
    }

    .stat-value.danger {
      color: #dc3545;
    }

    /* Toolbar */
    .toolbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      margin-bottom: 16px;
    }

    .search-input {
      flex: 1;
      padding: 9px 14px;
      font-size: 14px;
      border: 1px solid #ddd;
      border-radius: 8px;
      outline: none;
    }

    .search-input:focus {
      border-color: #007BFF;
    }

    .add-btn {
      padding: 9px 18px;
      background: #1a1a2e;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      cursor: pointer;
      text-decoration: none;
      white-space: nowrap;
    }

    .add-btn:hover {
      background: #2d2d50;
    }

    /* Table */
    .table-wrap {
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    }

    thead {
      background: #f0f0f0;
    }

    th {
      padding: 11px 14px;
      font-size: 12px;
      color: #666;
      text-align: left;
      border-bottom: 1px solid #eee;
    }

    td {
      padding: 12px 14px;
      font-size: 13px;
      color: #333;
      border-bottom: 1px solid #f0f0f0;
      vertical-align: middle;
    }

    tr:last-child td {
      border-bottom: none;
    }

    tr:hover td {
      background: #fafafa;
    }

    /* Badges */
    .badge-sold {
      display: inline-block;
      padding: 3px 10px;
      font-size: 11px;
      font-weight: bold;
      background: #f8d7da;
      color: #842029;
      border-radius: 20px;
    }

    .badge-ok {
      display: inline-block;
      padding: 3px 10px;
      font-size: 11px;
      font-weight: bold;
      background: #d1e7dd;
      color: #0a3622;
      border-radius: 20px;
    }

    .res-count {
      display: inline-block;
      padding: 3px 10px;
      font-size: 12px;
      background: #cfe2ff;
      color: #084298;
      border-radius: 20px;
    }

    .empty {
      text-align: center;
      padding: 40px;
      color: #999;
    }
  </style>
</head>

<body>

  <header>
    <span class="logo">Admin Panel</span>
    <a href="../auth/logout.php" class="logout-link">Logout</a>
  </header>

  <div class="page">

    <!-- Stats -->
    <div class="stat-row">
      <div class="stat-card">
        <div class="stat-label">Total events</div>
        <div class="stat-value"><?= $totalEvents ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Total reservations</div>
        <div class="stat-value"><?= $totalReservations ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Sold out events</div>
        <div class="stat-value danger"><?= $soldOut ?></div>
      </div>
    </div>

    <!-- Toolbar: search + add button -->
    <div class="toolbar">
      <form method="GET" style="flex:1; display:flex; gap:10px;">
        <input
          class="search-input"
          type="text"
          name="search"
          placeholder="Search by event title..."
          value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="add-btn">Search</button>
      </form>
      <a href="add-event.php" class="add-btn">+ Add event</a>
    </div>

    <!-- Events table -->
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Title</th>
            <th>Date</th>
            <th>Location</th>
            <th>Price</th>
            <th>Places left</th>
            <th>Reservations</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($events)): ?>
            <tr>
              <td colspan="8" class="empty">No events found.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($events as $i => $event): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><strong><?= htmlspecialchars($event['title']) ?></strong></td>
                <td><?= htmlspecialchars($event['date_event']) ?></td>
                <td><?= htmlspecialchars($event['location']) ?></td>
                <td><?= number_format($event['price'], 2) ?> DH</td>
                <td><?= $event['nbPlaces'] ?></td>
                <td><span class="res-count"><?= $event['reservation_count'] ?></span></td>
                <td>
                  <?php if ($event['nbPlaces'] == 0): ?>
                    <span class="badge-sold">Sold out</span>
                  <?php else: ?>
                    <span class="badge-ok">Available</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</body>

</html>