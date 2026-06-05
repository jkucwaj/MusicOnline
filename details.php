<?php
$pageTitle = "Details";
$extraCSS = "details.css";

include 'header1.inc.php';
require 'dbConnect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die("Invalid ID");
}

$stmt = $dbConnect->prepare("
    SELECT *FROM vinylListings WHERE listingID = :id");
$stmt->execute([':id' => $id]);

$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die("Listing not found");
}

$imageSrc = '';

if (!empty($row['imagePath'])) {
    $imageSrc = $basePath . '/uploads/img/' . htmlspecialchars($row['imagePath']);
}
?>

<div class="details-page">
    <div class="details-card">

        <a href="catalogue.php" class="back-btn">Back to catalogue</a>

        <h1 class="details-heading">Listing Details</h1>
        <h2 class="details-title"><?php echo htmlspecialchars($row['title']); ?></h2>

        <div class="details-layout">

            <div class="details-image">
                <?php if (!empty($imageSrc)): ?>
                    <img src="<?php echo $imageSrc; ?>" alt="Vinyl cover">
                <?php else: ?>
                    <p>No image available</p>
                <?php endif; ?>
            </div>

            <div class="details-info">
                <p><strong>Artist</strong> <?php echo htmlspecialchars($row['artist']); ?></p>
                <p><strong>Genre</strong> <?php echo htmlspecialchars($row['genre']); ?></p>
                <p><strong>Release Year</strong> <?php echo htmlspecialchars($row['releaseYear']); ?></p>
                <p><strong>Condition</strong> <?php echo htmlspecialchars($row['condition']); ?></p>
                <p><strong>Price</strong> £<?php echo number_format((float)$row['price'], 2); ?></p>
            </div>

        </div>

        <div class="details-description">
            <h3>Description</h3>
            <p><?php echo nl2br(htmlspecialchars($row['description'] ?? 'No description available')); ?></p>
        </div>

    </div>
</div>

<?php include 'footer1.inc.php'; ?>