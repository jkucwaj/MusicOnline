<?php
$pageTitle = "Manage Listing";
$adminCss = "/~s2143615/assesmentFolder/user/addEditForm.css";

session_start();
require_once '../dbConnect.php';
include '../header1.inc.php';
include '../functions1.php';

// check login
if (!isset($_SESSION['userID'])) {
    header("Location: ../account.php");
    exit();
}

$userID  = (int)$_SESSION['userID'];
$isAdmin = (int)($_SESSION['ifAdmin'] ?? 0);

$action = $_GET['action'] ?? 'add';
$listingID = $_GET['id'] ?? null;

$errors = [];
$cleanData = [];

// validate action
if ($action !== 'add' && $action !== 'edit') {
    echo '<p>Invalid action.</p>';
    include '../footer1.inc.php';
    exit();
}

// if edit load old listing data
if ($action === 'edit') {

    if ($listingID === null || !ctype_digit($listingID)) {
        echo '<p>Invalid or missing listing ID this page cannot be accessed directly</p>';
        include '../footer1.inc.php';
        exit();
    }

    $listingID = (int)$listingID;

    try {
        // admin can edit every listing
        if ($isAdmin) {
            $stmt = $dbConnect->prepare("SELECT * FROM vinylListings WHERE listingID = :id");
            $stmt->bindValue(':id', $listingID, PDO::PARAM_INT);
        } else {
            // normal user only own listing
            $stmt = $dbConnect->prepare("SELECT * FROM vinylListings WHERE listingID = :id AND userID = :uid");
            $stmt->bindValue(':id', $listingID, PDO::PARAM_INT);
            $stmt->bindValue(':uid', $userID, PDO::PARAM_INT);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            echo '<p>Listing not found or you do not have permission</p>';
            include '../footer1.inc.php';
            exit();
        }

        // old values for edit form
        $cleanData = ['title'=> $row['title'],'artist'=> $row['artist'],'genre'=> $row['genre'],'releaseYear'=> $row['releaseYear'],'condition'=> $row['condition'],'price'=> $row['price'],'description'=> $row['description']];

    } catch(PDOException $e) {
        echo '<h2>Error loading listing</h2>';
        echo '<p>' . e($e->getMessage()) . '</p>';
        include '../footer1.inc.php';
        exit();
    }
}
?>

<main class="listing-form-page">
    <section class="listing-form-section">
        <div class="listing-form-card">

            <h2 class="listing-form-title">
                <?php echo ($action === 'edit') ? 'Edit Listing' : 'Add New Listing'; ?>
            </h2>

            <form class="listing-form"
                  action="saveListing.php?action=<?php echo e($action); ?><?php echo ($action === 'edit') ? '&id=' . e($listingID) : ''; ?>"
                  method="POST"
                  enctype="multipart/form-data">

                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="id" value="<?php echo e($listingID); ?>">
                <?php endif; ?>

                <label for="title" class="listing-label">Title</label><br>
                <input class="listing-input" type="text" name="title" id="title" maxlength="100"
                       value="<?php if (isset($cleanData['title'])) echo e($cleanData['title']); ?>">
                <br><br>

                <label for="artist" class="listing-label">Artist</label><br>
                <input class="listing-input" type="text" name="artist" id="artist" maxlength="100"
                       value="<?php if (isset($cleanData['artist'])) echo e($cleanData['artist']); ?>">
                <br><br>

                <label for="genre" class="listing-label">Genre</label><br>
                <input class="listing-input" type="text" name="genre" id="genre" maxlength="50"
                       value="<?php if (isset($cleanData['genre'])) echo e($cleanData['genre']); ?>">
                <br><br>

                <label for="releaseYear" class="listing-label">Release Year</label><br>
                <input class="listing-input listing-input-small" type="number" name="releaseYear" id="releaseYear" min="1900" max="<?php echo date('Y'); ?>"
                       value="<?php if (isset($cleanData['releaseYear'])) echo e($cleanData['releaseYear']); ?>">
                <br><br>

                <label for="condition" class="listing-label">Condition</label><br>
                <select class="listing-input listing-select" name="condition" id="condition">
                    <?php
                        $conditions = ['New', 'Very Good', 'Good', 'Fair', 'Poor'];
                        $selected = $cleanData['condition'] ?? 'Good';

                        foreach ($conditions as $c) {
                            $isSelected = ($selected === $c) ? 'selected' : '';
                            echo '<option value="' . e($c) . '" ' . $isSelected . '>' . e($c) . '</option>';
                        }
                    ?>
                </select>
                <br><br>

                <label for="price" class="listing-label">Price (£)</label><br>
                <input class="listing-input" type="text" name="price" id="price" maxlength="10"
                       value="<?php if (isset($cleanData['price'])) echo e($cleanData['price']); ?>">
                <br><br>

                <label for="description" class="listing-label">Description</label><br>
                <textarea class="listing-input listing-textarea" name="description" id="description" rows="4" cols="45"><?php
                    if (isset($cleanData['description'])) echo e($cleanData['description']);
                ?></textarea>
                <br><br>

                <label for="image" class="listing-label">Album Cover</label><br>
                <input class="listing-file" type="file" name="image" id="image" accept=".jpg,.jpeg,.png,.webp">
                <br><br>

                <input class="listing-save-btn" type="submit" name="submit" value="<?php echo ($action === 'edit') ? 'Save Changes' : 'Save Listing'; ?>" id="btn">
            </form>

            <p class="listing-back-wrap">
                <a class="listing-back-link" href="userListings.php">Back to Listings</a>
            </p>

        </div>
    </section>
</main>

<?php include '../footer1.inc.php'; ?>