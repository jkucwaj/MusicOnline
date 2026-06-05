<?php
session_start(); // start session

$pageTitle = "Admin - All Listings"; // page title
$adminCss  = "/~s2143615/assesmentFolder/panel.css"; // admin css

require_once '../dbConnect.php'; // db connection
require_once '../functions1.php'; // helper functions
include '../header1.inc.php'; // header

$errors = []; // store errors
$listings = []; // store all listings

// if not logged in -> go to login page
if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit();
}

$userID  = (int)$_SESSION['userID']; // current user id
$isAdmin = (int)($_SESSION['ifAdmin'] ?? 0); // admin flag

// if not admin -> go to normal user panel
if ($isAdmin !== 1) {
    header("Location: userPanel.php");
    exit();
}

try {
    // get all listings with seller username
    $stmt = $dbConnect->prepare("
        SELECT v.listingID, v.title, v.artist, v.price, u.username
        FROM vinylListings v
        LEFT JOIN users u ON v.userID = u.userID
        ORDER BY v.listingID DESC
    ");
    $stmt->execute();
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $ex) {
    $errors[] = "Database error. Please try again later.";
}
?>

<div class="panel-wrap">

    <a class="panel-top-link" href="adminPanel.php">← Back to Admin Panel</a>

    <h2 class="panel-title">All Vinyl Listings</h2>
    <p class="panel-intro">
        Here the administrator can view, edit and delete all vinyl offers for content moderation.
    </p>

    <?php if (!empty($errors)): ?>
        <div class="panel-bad">
            <strong>Errors:</strong><br>
            <?php foreach ($errors as $msg): ?>
                <?php echo e($msg); ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="panel-box">

        <?php if (empty($listings)): ?>
            <p class="panel-muted">No listings found.</p>
        <?php else: ?>
            <p class="panel-muted">Total listings: <?php echo count($listings); ?></p>

            <div class="panel-table-wrap">
                <table class="panel-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Artist</th>
                            <th>Price (£)</th>
                            <th>Seller</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($listings as $l): ?>
                            <tr>
                                <td><?php echo e($l['listingID']); ?></td>
                                <td><?php echo e($l['title']); ?></td>
                                <td><?php echo e($l['artist']); ?></td>
                                <td><?php echo e(number_format((float)$l['price'], 2)); ?></td>
                                <td><?php echo e($l['username'] ?? 'Unknown'); ?></td>
                                <td>
                                    <a class="panel-action-link panel-action-edit"
                                       href="addEditForm.php?action=edit&id=<?php echo e($l['listingID']); ?>">
                                        Edit
                                    </a>

                                    <a class="panel-action-link panel-action-delete"
                                       href="deleteListing.php?id=<?php echo e($l['listingID']); ?>"
                                       onclick="return confirm('Delete this listing?');">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>

</div>

<?php include '../footer1.inc.php'; ?>