<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

$pageTitle = "Admin Panel";
$extraCSS = "panel.css";

require_once '../dbConnect.php';
require_once '../functions1.php';

//check if user is logged in 
if (!isset($_SESSION['userID'])) {
    header("Location: ../account.php");
    exit();
}

$userID = (int)$_SESSION['userID'];
$isAdmin = (int)($_SESSION['ifAdmin'] ?? 0);

//check if this account is admin 
if ($isAdmin !== 1) {
    header("Location: userPanel.php");
    exit();
}

include '../header1.inc.php';

// arrays for errors and data from db 
$errors = [];
$stats = [
    'totalUsers' => 0,
    'totalListings' => 0
];
$recentListings = [];

try {
    // to count all users 
    $stmt = $dbConnect->prepare("SELECT COUNT(*) AS totalUsers FROM users");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $stats['totalUsers'] = (int)$row['totalUsers'];
    }

    //to count all listings
    $stmt = $dbConnect->prepare("SELECT COUNT(*) AS totalListings FROM vinylListings");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $stats['totalListings'] = (int)$row['totalListings'];
    }

    // load few newest listings for dashboard
    $stmt = $dbConnect->prepare("
        SELECT listingID, title, artist, price
        FROM vinylListings
        ORDER BY listingID DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recentListings = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $ex) {
    $errors[] = "Database error please try again later";
}
?>

<div class="panel-wrap">

    <h2 class="panel-title">Admin Panel</h2>
    <p class="panel-sub">
        <strong>Logged in as:</strong> Admin UserID <?php echo e($userID); ?>
    </p>

    <?php if (!empty($errors)): ?>
        <div class="panel-bad">
            <strong>Errors</strong><br>
            <?php foreach ($errors as $msg): ?>
                <?php echo e($msg); ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="panel-hero">
        <div class="panel-grid">

            <div class="panel-card">
                <h3>Quick Links</h3>
                <div class="panel-actions">
                    <a class="panel-btn-green" href="adminUsers.php">Manage Users</a>
                    <a class="panel-btn-ghost" href="adminListings.php">View All Listings</a>
                    <a class="panel-btn-ghost" href="../logout.php">Logout</a>
                </div>
            </div>

            <div class="panel-card">
                <h3>Site Overview</h3>
                <p><strong>Total users:</strong> <?php echo e($stats['totalUsers']); ?></p>
                <p><strong>Total listings:</strong> <?php echo e($stats['totalListings']); ?></p>
                <p class="panel-muted">Numbers loaded from database</p>
            </div>

        </div>
    </div>

    <div class="section" style="margin-top:22px;">
        <div class="section-head">
            <h2>Recent Listings</h2>
        </div>

        <?php if (empty($recentListings)): ?>
            <p class="panel-muted">No listings found</p>
        <?php else: ?>
            <table class="panel-table">
                <thead>
                    <tr>
                        <th style="width:80px;">ID</th>
                        <th>Title</th>
                        <th>Artist</th>
                        <th style="width:120px;">Price (£)</th>
                        <th style="width:160px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentListings as $l): ?>
                        <tr>
                            <td><?php echo e($l['listingID']); ?></td>
                            <td><?php echo e($l['title']); ?></td>
                            <td><?php echo e($l['artist']); ?></td>
                            <td><?php echo e(number_format((float)$l['price'], 2)); ?></td>
                            <td>
                                <a href="addEditForm.php?action=edit&id=<?php echo e($l['listingID']); ?>">Edit</a>
                                |
                                <a href="deleteListing.php?id=<?php echo e($l['listingID']); ?>" onclick="return confirm('Delete this listing?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

<?php include '../footer1.inc.php'; ?>