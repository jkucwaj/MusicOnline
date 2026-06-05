<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

$pageTitle = "Admin - User Accounts";
$extraCSS = "panel.css";

require_once '../dbConnect.php';
require_once '../functions1.php';

// check login first 
if (!isset($_SESSION['userID'])) {
    header("Location: ../account.php");
    exit();
}

$userID = (int)$_SESSION['userID'];
$isAdmin = (int)($_SESSION['ifAdmin'] ?? 0);

// only admin can open this page 
if ($isAdmin !== 1) {
    header("Location: userPanel.php");
    exit();
}

include '../header1.inc.php';

//arrays for page
$errors = [];
$users = [];

try {
    // load all users for admin table 
    $stmt = $dbConnect->prepare("
        SELECT userID, username, firstName, surName, email, ifAdmin, created_at
        FROM users
        ORDER BY userID DESC
    ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $ex) {
    $errors[] = "Database error please try again later";
}
?>

<div class="panel-wrap">

    <a class="panel-top-link" href="adminPanel.php">← Back to Admin Panel</a>

    <h2 class="panel-title">Manage User Accounts</h2>
    <p class="panel-intro">
        Here admin can view edit and delete user accounts
    </p>

    <?php if (!empty($errors)): ?>
        <div class="panel-bad">
            <strong>Errors</strong><br>
            <?php foreach ($errors as $msg): ?>
                <?php echo e($msg); ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="panel-box">

        <?php if (empty($users)): ?>
            <p class="panel-muted">No user accounts found</p>
        <?php else: ?>
            <div class="panel-table-wrap">
                <table class="panel-table">
                    <thead>
                        <tr>
                            <th style="width:80px;">UserID</th>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th style="width:120px;">Role</th>
                            <th style="width:190px;">Created</th>
                            <th style="width:180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?php echo e($u['userID']); ?></td>
                                <td><?php echo e($u['username']); ?></td>
                                <td><?php echo e(trim(($u['firstName'] ?? '') . ' ' . ($u['surName'] ?? ''))); ?></td>
                                <td><?php echo e($u['email']); ?></td>
                                <td>
                                    <?php if ((int)$u['ifAdmin'] === 1): ?>
                                        <span class="panel-badge panel-badge-admin">Admin</span>
                                    <?php else: ?>
                                        <span class="panel-badge panel-badge-user">User</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($u['created_at']); ?></td>
                                <td>
                                    <a class="panel-action-link panel-action-edit" href="adminEditUser.php?id=<?php echo e($u['userID']); ?>">Edit</a>
                                    <a class="panel-action-link panel-action-delete" href="adminDeleteUser.php?id=<?php echo e($u['userID']); ?>">Delete</a>
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