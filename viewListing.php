<?php
$pageTitle = "Listing Details";

require_once 'dbConnect.php';
include 'header1.inc.php';
include 'functions1.php';

echo '<h1>Listing Details</h1>';

$listingID = $_GET['id'] ?? null;

if ($listingID === null || !ctype_digit($listingID)) {
    echo '<p>Invalid or missing listing ID, this page cannot be accessed directly.</p>';
    include 'footer1.inc.php';
    exit();
}

$listingID = (int)$listingID;

try
{
    $stmt = $dbConnect->prepare("
        SELECT listingID, userID, title, artist, genre, releaseYear, `condition`, price, description, imagePath, whenCreated
        FROM vinylListings
        WHERE listingID = :id
        LIMIT 1
    ");
    $stmt->bindValue(':id', $listingID, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo '<p>Listing not found.</p>';
        echo '<p><a href="catalogue.php">Back to catalogue</a></p>';
        include 'footer1.inc.php';
        exit();
    }

    // image (public pages should point to correct folder where images are stored)
    // If images are stored in /user/uploads/vinyl/ (from your earlier setup):
    $imgPath = null;
    if (!empty($row['imagePath'])) {
        $imgPath = 'user/uploads/vinyl/' . $row['imagePath'];
    }

    echo '<h2>' . e($row['title']) . '</h2>';
    echo '<p><strong>Artist:</strong> ' . e($row['artist']) . '</p>';
    echo '<p><strong>Genre:</strong> ' . e($row['genre']) . '</p>';
    echo '<p><strong>Release Year:</strong> ' . e($row['releaseYear']) . '</p>';
    echo '<p><strong>Condition:</strong> ' . e($row['condition']) . '</p>';
    echo '<p><strong>Price:</strong> £' . e($row['price']) . '</p>';

    if (!empty($row['description'])) {
        echo '<p><strong>Description:</strong><br>' . nl2br(e($row['description'])) . '</p>';
    }

    if ($imgPath !== null) {
        echo '<p><strong>Image:</strong></p>';
        echo '<img src="'.e($imgPath).'" alt="Vinyl image" style="max-width:320px; height:auto;">';
    }

    echo '<hr>';
    echo '<p><a href="catalogue.php">Back to catalogue</a></p>';

}
catch(PDOException $e)
{
    echo '<h2>Error loading listing</h2>';
    echo '<p>'.e($e->getMessage()).'</p>';
}

include 'footer1.inc.php';
?>