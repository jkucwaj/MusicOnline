<?php
$pageTitle = "Delete Listing";

session_start();
require_once '../dbConnect.php';
include '../header1.inc.php';
include '../functions1.php';

function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

// check login
if (!isset($_SESSION['userID'])) {
    header("Location: ../account.php");
    exit();
}

echo '<h1>Delete a Listing</h1>';

$userID  = (int)$_SESSION['userID'];
$isAdmin = (int)($_SESSION['ifAdmin'] ?? 0);

// listingID can come from GET (first time) or POST (after submitting form)
$listingID = $_GET['id'] ?? $_POST['id'] ?? null;

if ($listingID === null || !ctype_digit($listingID)) {
    echo '<p>Invalid or missing listing ID, this page cannot be accessed directly.</p>';
    include '../footer1.inc.php';
    exit();
}

$listingID = (int)$listingID;

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if (isset($_POST['accepted']) && $_POST['accepted'] === 'yes')
    {
        // delete (admin deletes any, user deletes only own)
        if ($isAdmin) {
            $stmt2 = $dbConnect->prepare("DELETE FROM vinylListings WHERE listingID = :id LIMIT 1");
            $stmt2->bindValue(':id', $listingID, PDO::PARAM_INT);
        } else {
            $stmt2 = $dbConnect->prepare("DELETE FROM vinylListings WHERE listingID = :id AND userID = :uid LIMIT 1");
            $stmt2->bindValue(':id', $listingID, PDO::PARAM_INT);
            $stmt2->bindValue(':uid', $userID, PDO::PARAM_INT);
        }

        $stmt2->execute();

        if ($stmt2->rowCount() === 1)
        {
            echo '<p>Listing deleted.</p>';
            echo '<p><a href="userListings.php">Back to Listings</a></p>';
        }
        else
        {
            echo '<p>Something went wrong — listing not deleted (not found or no permission).</p>';
            echo '<p><a href="userListings.php">Back to Listings</a></p>';
        }
    }
    else
    {
        echo '<p>Listing not deleted.</p>';
        echo '<p><a href="userListings.php">Back to Listings</a></p>';
    }
}
else // method is GET, display delete confirmation
{
    // show basic info so user knows what they delete
    if ($isAdmin) {
        $stmt = $dbConnect->prepare("SELECT title, artist, price FROM vinylListings WHERE listingID = :id");
        $stmt->bindValue(':id', $listingID, PDO::PARAM_INT);
    } else {
        $stmt = $dbConnect->prepare("SELECT title, artist, price FROM vinylListings WHERE listingID = :id AND userID = :uid");
        $stmt->bindValue(':id', $listingID, PDO::PARAM_INT);
        $stmt->bindValue(':uid', $userID, PDO::PARAM_INT);
    }

    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row)
    {
        echo '<h3>Title: ' . e($row['title']) . '</h3>';
        echo '<p>Artist: ' . e($row['artist']) . '</p>';
        echo '<p>Price: £' . e($row['price']) . '</p>';
        echo '<p>Are you sure you want to delete this listing?</p>';

        echo '
            <form action="deleteListing.php" method="POST">
                <input type="hidden" name="id" value="'.e($listingID).'">

                <input type="radio" name="accepted" value="yes"> YES
                <input type="radio" name="accepted" value="no" checked> NO

                <br><br>
                <input type="submit" name="submit" value="Submit" id="btn">
            </form>

            <p><a href="userListings.php">Cancel</a></p>
        ';
    }
    else
    {
        echo '<p>Listing not found or you do not have permission to delete it.</p>';
        echo '<p><a href="userListings.php">Back to Listings</a></p>';
    }
}

include '../footer1.inc.php';
?>