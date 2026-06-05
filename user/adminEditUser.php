<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

$pageTitle = "Edit User Account";
$extraCSS = "panel.css";

require_once '../dbConnect.php';
require_once '../functions1.php';

// security checks
if (!isset($_SESSION['userID'])) {
    header("Location: ../account.php");
    exit();
}

$isAdmin = (int)($_SESSION['ifAdmin'] ?? 0);

if ($isAdmin !== 1) {
    header("Location: userPanel.php");
    exit();
}

include '../header1.inc.php';

// arrays for form and errors 
$errors = [];
$cleanData = [];

// check id from url 
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    echo "<div class='panel-wrap'><h2 class='panel-title'>Error</h2><p class='panel-intro'>Invalid user ID</p></div>";
    include '../footer1.inc.php';
    exit();
}

$editUserID = (int)$_GET['id'];

try {
    // load selectd user 
    $stmt = $dbConnect->prepare("
        SELECT userID, firstName, surName, email, ifAdmin
        FROM users
        WHERE userID = :id
        LIMIT 1
    ");
    $stmt->bindValue(':id', $editUserID, PDO::PARAM_INT);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<div class='panel-wrap'><h2 class='panel-title'>Error</h2><p class='panel-intro'>User not found</p></div>";
        include '../footer1.inc.php';
        exit();
    }

    // put old data into form first time 
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $cleanData['firstName'] = $user['firstName'] ?? '';
        $cleanData['surName'] = $user['surName'] ?? '';
        $cleanData['email'] = $user['email'] ?? '';
        $cleanData['ifAdmin'] = (int)($user['ifAdmin'] ?? 0);
    }

} catch (PDOException $ex) {
    echo "<div class='panel-wrap'><h2 class='panel-title'>Error</h2><p class='panel-intro'>Error loading user</p></div>";
    include '../footer1.inc.php';
    exit();
}

// after submit validate and update 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // first name check 
    if (empty($_POST['firstName'])) {
        $errors[] = "You need to enter first name";
    } else {
        $cleanData['firstName'] = cleanUp($_POST['firstName']);
    }

    // surname check 
    if (empty($_POST['surName'])) {
        $errors[] = "You need to enter surname";
    } else {
        $cleanData['surName'] = cleanUp($_POST['surName']);
    }

    //email check 
    if (empty($_POST['email'])) {
        $errors[] = "You need to enter email";
    } else {
        $email = cleanUp($_POST['email']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "You need to enter valid email";
        } else {
            $cleanData['email'] = $email;
        }
    }
    // admin checkbox 
    $cleanData['ifAdmin'] = isset($_POST['ifAdmin']) ? 1 : 0;

    // small protection for own admin account 
    if ($editUserID === (int)$_SESSION['userID'] && $cleanData['ifAdmin'] === 0) {
        $errors[] = "You cannot remove admin from your own account";
    }

    if (empty($errors)) {
        try {
            // update selected user 
            $stmt = $dbConnect->prepare("
                UPDATE users
                SET firstName = :fn,
                    surName = :sn,
                    email = :em,
                    ifAdmin = :ad
                WHERE userID = :id
            ");

            $stmt->bindValue(':fn', $cleanData['firstName'], PDO::PARAM_STR);
            $stmt->bindValue(':sn', $cleanData['surName'], PDO::PARAM_STR);
            $stmt->bindValue(':em', $cleanData['email'], PDO::PARAM_STR);
            $stmt->bindValue(':ad', $cleanData['ifAdmin'], PDO::PARAM_INT);
            $stmt->bindValue(':id', $editUserID, PDO::PARAM_INT);
            $stmt->execute();

            echo "<div class='panel-wrap'>";
            echo "<h2 class='panel-title'>Success</h2>";
            echo "<p class='panel-intro'>User account updated</p>";
            echo "<p><a class='panel-top-link' href='adminUsers.php'>← Back to User Accounts</a></p>";
            echo "</div>";

            include '../footer1.inc.php';
            exit();

        } catch (PDOException $ex) {
            $errors[] = "Database error please try again later";
        }
    }
}
?>

<div class="panel-wrap">

    <a class="panel-top-link" href="adminUsers.php">← Back to User Accounts</a>

    <h2 class="panel-title">Edit User Account</h2>
    <p class="panel-intro">Update user details below</p>

    <?php if (!empty($errors)): ?>
        <div class="panel-bad">
            <strong>Errors</strong><br>
            <?php foreach ($errors as $msg): ?>
                <?php echo e($msg); ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="panel-box">

        <form method="POST" action="adminEditUser.php?id=<?php echo e($editUserID); ?>">

            <p>
                <label for="firstName">First Name</label><br>
                <input type="text" name="firstName" id="firstName" value="<?php echo e($cleanData['firstName'] ?? ''); ?>">
            </p>

            <p>
                <label for="surName">Surname</label><br>
                <input type="text" name="surName" id="surName" value="<?php echo e($cleanData['surName'] ?? ''); ?>">
            </p>

            <p>
                <label for="email">Email</label><br>
                <input type="text" name="email" id="email" value="<?php echo e($cleanData['email'] ?? ''); ?>">
            </p>

            <p>
                <label>
                    <input type="checkbox" name="ifAdmin" value="1" <?php echo ((int)($cleanData['ifAdmin'] ?? 0) === 1) ? 'checked' : ''; ?>>
                    Admin account
                </label>
            </p>

            <p>
                <input type="submit" value="Save Changes" id="btn">
            </p>

        </form>

    </div>

</div>

<?php include '../footer1.inc.php'; ?>