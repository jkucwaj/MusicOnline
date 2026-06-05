<?php
session_start();
require '../dbConnect.php';
require '../functions1.php';

// user must be logged in
if (!isset($_SESSION['userID'])) {
    header("Location: ../account.php");
    exit();
}

// page title
$pageTitle = "Save Listing";

// get user data from session
$userID  = (int)$_SESSION['userID'];
$isAdmin = (int)($_SESSION['ifAdmin'] ?? 0);

// get action and listing id
$action = $_GET['action'] ?? $_POST['action'] ?? 'add';
$idFromGet = $_GET['id'] ?? null;
$idFromPost = $_POST['id'] ?? null;
$listingID = $idFromGet ?? $idFromPost;

// this page should work only after form submit
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    include '../header1.inc.php';
    echo "<p>This page cannot be accessed directly.</p>";
    include '../footer1.inc.php';
    exit();
}

// if edit we need correct listing id
if ($action === 'edit') {
    if ($listingID === null || !ctype_digit((string)$listingID)) {
        include '../header1.inc.php';
        echo "<p>Invalid or missing listing ID.</p>";
        include '../footer1.inc.php';
        exit();
    }

    $listingID = (int)$listingID;
}

// arrays for errors and clean form data
$errors = [];
$cleanData = [];

// fields to validate
$fields = ['title', 'artist', 'genre', 'releaseYear', 'condition', 'price', 'description'];

foreach ($fields as $field) {
    // check if field is empty
    if (empty($_POST[$field])) {
        $fieldName = ucfirst(preg_replace('/([A-Z])/', ' $1', $field));
        $errors[] = "You need to enter a $fieldName";
    } else {
        // clean entered value
        $data = cleanUp($_POST[$field]);

        // year check
        if ($field === 'releaseYear') {
            if (!filter_var($data, FILTER_VALIDATE_INT) || (int)$data < 1900 || (int)$data > (int)date('Y')) {
                $errors[] = "You need to enter a valid Release Year";
            } else {
                $cleanData[$field] = (int)$data;
            }
        }
        // price check
        elseif ($field === 'price') {
            if (!is_numeric($data) || (float)$data <= 0) {
                $errors[] = "You need to enter a valid Price";
            } else {
                $cleanData[$field] = (float)$data;
            }
        }
        // normal text fields
        else {
            $cleanData[$field] = $data;
        }
    }
}

// image is optional
$imageFileName = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

    // if upload failed
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Image upload failed";
    } else {
        // allowed image types and max size
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 2 * 1024 * 1024;

        if ($_FILES['image']['size'] > $maxSize) {
            $errors[] = "Image is too large max 2MB";
        } elseif (!in_array($_FILES['image']['type'], $allowedTypes)) {
            $errors[] = "Only JPG PNG or WEBP images are allowed";
        } else {
            // create safe unique file name
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $safeName = uniqid('vinyl_', true) . "." . $ext;

            // use your real folder from project
            $uploadFolder = "../uploads/img/";
            $destination = $uploadFolder . $safeName;

            // move uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $imageFileName = $safeName;
            } else {
                $errors[] = "Could not save uploaded image";
            }
        }
    }
}

// if there is errors stop here
if (!empty($errors)) {
    include '../header1.inc.php';

    echo "<h2>The following errors occurred</h2>";

    foreach ($errors as $message) {
        echo e($message) . "<br>";
    }

    echo '<p><a href="userListings.php">Back to Listings</a></p>';

    include '../footer1.inc.php';
    exit();
}

try {
    // if edit and no new image then keep old one
    if ($action === 'edit' && $imageFileName === null) {
        if ($isAdmin) {
            $stmtImg = $dbConnect->prepare("
                SELECT imagePath
                FROM vinylListings
                WHERE listingID = :id
            ");
            $stmtImg->bindValue(':id', $listingID, PDO::PARAM_INT);
        } else {
            $stmtImg = $dbConnect->prepare("
                SELECT imagePath
                FROM vinylListings
                WHERE listingID = :id AND userID = :uid
            ");
            $stmtImg->bindValue(':id', $listingID, PDO::PARAM_INT);
            $stmtImg->bindValue(':uid', $userID, PDO::PARAM_INT);
        }

        $stmtImg->execute();
        $imgRow = $stmtImg->fetch(PDO::FETCH_ASSOC);
        $imageFileName = $imgRow['imagePath'] ?? null;
    }

    // add new listing
    if ($action === 'add') {
        $stmt = $dbConnect->prepare("
            INSERT INTO vinylListings(userID, title, artist, genre, releaseYear, `condition`, price, description, imagePath)
            VALUES
            (:userID, :title, :artist, :genre, :releaseYear, :condition, :price, :description, :imagePath)
        ");

        $stmt->bindValue(':userID', $userID, PDO::PARAM_INT);
        $stmt->bindValue(':title', $cleanData['title']);
        $stmt->bindValue(':artist', $cleanData['artist']);
        $stmt->bindValue(':genre', $cleanData['genre']);
        $stmt->bindValue(':releaseYear', $cleanData['releaseYear'], PDO::PARAM_INT);
        $stmt->bindValue(':condition', $cleanData['condition']);
        $stmt->bindValue(':price', $cleanData['price']);
        $stmt->bindValue(':description', $cleanData['description']);
        $stmt->bindValue(':imagePath', $imageFileName);

        $stmt->execute();

        include '../header1.inc.php';
        echo "<h2>Success</h2>";
        echo "<p>Listing added</p>";
        echo '<p><a href="userListings.php">Back to Listings</a></p>';
        include '../footer1.inc.php';
        exit();
    }
    // edit old listing
    else {
        if ($isAdmin) {
            $stmt = $dbConnect->prepare("
                UPDATE vinylListings
                SET title = :title,
                    artist = :artist,
                    genre = :genre,
                    releaseYear = :releaseYear,
                    `condition` = :condition,
                    price = :price,
                    description = :description,
                    imagePath = :imagePath
                WHERE listingID = :id
                LIMIT 1
            ");
            $stmt->bindValue(':id', $listingID, PDO::PARAM_INT);
        } else {
            $stmt = $dbConnect->prepare("
                UPDATE vinylListings
                SET title = :title,
                    artist = :artist,
                    genre = :genre,
                    releaseYear = :releaseYear,
                    `condition` = :condition,
                    price = :price,
                    description = :description,
                    imagePath = :imagePath
                WHERE listingID = :id AND userID = :uid
                LIMIT 1
            ");
            $stmt->bindValue(':id', $listingID, PDO::PARAM_INT);
            $stmt->bindValue(':uid', $userID, PDO::PARAM_INT);
        }

        $stmt->bindValue(':title', $cleanData['title']);
        $stmt->bindValue(':artist', $cleanData['artist']);
        $stmt->bindValue(':genre', $cleanData['genre']);
        $stmt->bindValue(':releaseYear', $cleanData['releaseYear'], PDO::PARAM_INT);
        $stmt->bindValue(':condition', $cleanData['condition']);
        $stmt->bindValue(':price', $cleanData['price']);
        $stmt->bindValue(':description', $cleanData['description']);
        $stmt->bindValue(':imagePath', $imageFileName);

        $stmt->execute();

        include '../header1.inc.php';

        if ($stmt->rowCount() === 1) {
            echo "<h2>Success</h2>";
            echo "<p>Listing updated</p>";
        } else {
            echo "<h2>Note</h2>";
            echo "<p>No changes saved or no permission</p>";
        }

        echo '<p><a href="userListings.php">Back to Listings</a></p>';
        include '../footer1.inc.php';
        exit();
    }

} catch (PDOException $e) {
    include '../header1.inc.php';
    echo '<h2>Error saving listing</h2>';
    echo '<p>' . e($e->getMessage()) . '</p>';
    echo '<p><a href="userListings.php">Back to Listings</a></p>';
    include '../footer1.inc.php';
    exit();
}
?>