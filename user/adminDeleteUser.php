<?php
session_start();

$pageTitle = "Delete User Account";
$adminCss  = "/~s2143615/assesmentFolder/panel.css";

require_once '../dbConnect.php';
require_once '../functions1.php';
include '../header1.inc.php';

if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit();
}

$isAdmin = (int)($_SESSION['ifAdmin'] ?? 0);
if ($isAdmin !== 1) {
    header("Location: userPanel.php");
    exit();
}

$userID = $_GET['id'] ?? $_POST['id'] ?? null;

if ($userID === null || !ctype_digit($userID)) {
    echo "<div class='panel-wrap'><h2 class='panel-title'>Error</h2><p class='panel-intro'>Invalid user ID.</p></div>";
    include '../footer1.inc.php';
    exit();
}

$deleteUserID = (int)$userID;

// do not allow deleting own admin account
if ($deleteUserID === (int)$_SESSION['userID']) {
    echo "<div class='panel-wrap'><h2 class='panel-title'>Error</h2><p class='panel-intro'>You cannot delete your own admin account.</p></div>";
    include '../footer1.inc.php';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['accepted']) && $_POST['accepted'] === 'yes') {
        try {
            // delete user's listings first
            $stmt = $dbConnect->prepare("DELETE FROM vinylListings WHERE userID = :id");
            $stmt->bindValue(':id', $deleteUserID, PDO::PARAM_INT);
            $stmt->execute();

            // then delete user
            $stmt2 = $dbConnect->prepare("DELETE FROM users WHERE userID = :id LIMIT 1");
            $stmt2->bindValue(':id', $deleteUserID, PDO::PARAM_INT);
            $stmt2->execute();

            echo "<div class='panel-wrap'>";
            echo "<h2 class='panel-title'>Success</h2>";
            echo "<p class='panel-intro'>User account deleted.</p>";
            echo "<p><a class='panel-top-link' href='adminUsers.php'>← Back to User Accounts</a></p>";
            echo "</div>";

        } catch (PDOException $ex) {
            echo "<div class='panel-wrap'><h2 class='panel-title'>Error</h2><p class='panel-intro'>Database error. Could not delete user.</p></div>";
        }
    } else {
        echo "<div class='panel-wrap'><h2 class='panel-title'>Cancelled</h2><p class='panel-intro'>User was not deleted.</p><p><a class='panel-top-link' href='adminUsers.php'>← Back to User Accounts</a></p></div>";
    }

    include '../footer1.inc.php';
    exit();
}

// get to load user details for confirm screen
try {
    $stmt = $dbConnect->prepare("
        SELECT firstName, surName, username
        FROM users
        WHERE userID = :id
        LIMIT 1
    ");
    $stmt->bindValue(':id', $deleteUserID, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo "<div class='panel-wrap'><h2 class='panel-title'>Error</h2><p class='panel-intro'>User not found.</p></div>";
        include '../footer1.inc.php';
        exit();
    }

} catch (PDOException $ex) {
    echo "<div class='panel-wrap'><h2 class='panel-title'>Error</h2><p class='panel-intro'>Database error.</p></div>";
    include '../footer1.inc.php';
    exit();
}
?>

<div class="panel-wrap">

    <a class="panel-top-link" href="adminUsers.php">← Back to User Accounts</a>

    <h2 class="panel-title">Delete User Account</h2>
    <p class="panel-intro">
        Are you sure you want to delete this user?
    </p>

    <div class="panel-box">
        <p><strong>Username:</strong> <?php echo e($row['username']); ?></p>
        <p><strong>Name:</strong> <?php echo e(trim(($row['firstName'] ?? '') . ' ' . ($row['surName'] ?? ''))); ?></p>

        <form action="adminDeleteUser.php" method="POST">
            <p>
                <label><input type="radio" name="accepted" value="yes"> Yes</label>
                <label><input type="radio" name="accepted" value="no" checked> No</label>
            </p>

            <p>
                <input type="hidden" name="id" value="<?php echo e($deleteUserID); ?>">
                <input type="submit" value="Submit" id="btn">
            </p>
        </form>
    </div>

</div>

<?php include '../footer1.inc.php'; ?>