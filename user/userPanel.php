<?php
session_start(); // start session

// do not cache protected page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

$pageTitle = "User Panel"; // page title
$adminCss = "/~s2143615/assesmentFolder/panel.css"; // extra css for panel pages

require '../dbConnect.php'; // db connection
require '../functions1.php'; // helper functions

// not logged in -> go to login page
if (!isset($_SESSION['userID'])) {
    header("Location: ../account.php");
    exit();
}

$userID = (int)$_SESSION['userID']; // current user id
$isAdmin = (int)($_SESSION['ifAdmin'] ?? 0); // admin flag

// admin -> go to admin panel
if ($isAdmin === 1) {
    header("Location: adminPanel.php");
    exit();
}

include '../header1.inc.php'; // header include after redirects

$username = e($_SESSION['username'] ?? 'user'); // safe username
?>

<main class="panel-wrap">
    <section class="panel-hero">
        <h1 class="panel-title">User Panel</h1>
        <p class="panel-sub">Hi, <?php echo $username; ?>!</p>

        <div class="panel-grid">
            <div class="panel-card">
                <h3>Quick Links</h3>

                <div class="panel-actions">
                    <a class="panel-btn-green" href="userListings.php">My Listings</a>
                    <a class="panel-btn-ghost" href="addEditForm.php?action=add">Add New Listing</a>
                    <a class="panel-btn-ghost" href="../logout.php">Logout</a>
                </div>
            </div>

            <div class="panel-card">
                <h3>Account Overview</h3>
                <p><strong>Username:</strong> <?php echo $username; ?></p>
                <p><strong>Account type:</strong> Standard user</p>
                <p class="panel-muted">Here you can manage your own vinyl listings.</p>
            </div>
        </div>
    </section>
</main>

<?php include '../footer1.inc.php'; ?>