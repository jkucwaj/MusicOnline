<?php
session_start();// start session for logged user

// page title and css for this page
$pageTitle = "My Listings";
$extraCSS = "user/userListings.css";

// database and helper functions
require '../dbConnect.php';
require '../functions1.php';

// if user not logged in send to login page
if (!isset($_SESSION['userID'])) {
    header("Location: ../account.php");
    exit();
}

// get current user data from session
$userID = (int)$_SESSION['userID'];
$isAdmin = (int)($_SESSION['ifAdmin'] ?? 0);

// include shared header
include '../header1.inc.php';

try {
    // admin can see all listings
    if ($isAdmin === 1) {
        $stmt = $dbConnect->prepare("
            SELECT listingID, title, artist, price
            FROM vinylListings
            ORDER BY listingID DESC
        ");
        $stmt->execute();
    } else {
        // normal user can see only own listings
        $stmt = $dbConnect->prepare("
            SELECT listingID, title, artist, price
            FROM vinylListings
            WHERE userID = :uid
            ORDER BY listingID DESC
        ");
        $stmt->execute([':uid' => $userID]);
    }

    // save query results into array
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // if query fails just show empty table
    $listings = [];
}
?>

<div class="user-listings-page">
    <section class="user-listings-header">
        <h1>My Listings</h1>
        <p class="user-listings-sub">
            Here you can edit or delete your vinyl listings.
        </p>
    </section>

    <section class="user-listings-table-section">
        <div class="user-listings-table-wrap">
            <table class="user-listings-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Artist</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($listings)) : ?>
                        <?php foreach ($listings as $listing) : ?>
                            <tr>
                                <td><?php echo e($listing['title']); ?></td>
                                <td><?php echo e($listing['artist']); ?></td>
                                <td>£<?php echo number_format((float)$listing['price'], 2); ?></td>
                                <td class="actions-cell">
                                    <a class="action-link edit-link" href="addEditForm.php?action=edit&id=<?php echo (int)$listing['listingID']; ?>">
                                        Edit
                                    </a>

                                    <a class="action-link delete-link" href="deleteListing.php?id=<?php echo (int)$listing['listingID']; ?>" onclick="return confirm('Are you sure you want to delete this listing?');">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4" class="no-results">No listings found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="user-listings-bottom">
            <a class="add-listing-btn" href="addEditForm.php?action=add">Add New Listing</a>
        </div>
    </section>
</div>

<?php include '../footer1.inc.php'; ?>