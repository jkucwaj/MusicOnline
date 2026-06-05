<?php
$pageTitle = "Home";

require 'dbConnect.php';
include 'header1.inc.php';

// get latest 4 vinyls for home page
$stmt = $dbConnect->query("
    SELECT listingID, title, imagePath
    FROM vinylListings
    ORDER BY whenCreated DESC
    LIMIT 4
");

$latest = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="hero">
    <div class="hero-left">
        <h1>Upgrade your collection.</h1>

        <p class="hero-text">
            Browse pre-owned records, search by title/artist/genre, and view listing details.
        </p>

        <form class="hero-search" action="catalogue.php" method="get">
            <input type="text" name="search" placeholder="Search title / artist / genre">
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="hero-right">
        <div class="orb orb1"></div>
        <div class="orb orb2"></div>

        <div class="hero-card hero-card-big"></div>
        <div class="hero-card hero-card-mid"></div>
        <div class="hero-card hero-card-small"></div>
    </div>
</section>

<section class="section">
    <div class="section-head">
        <h2>Latest Vinyl Listings</h2>
    </div>

    <div class="cards-grid">
        <?php if (!empty($latest)): ?>
            <?php foreach ($latest as $r): ?>
                <?php
                $imageSrc = '';

                if (!empty($r['imagePath'])) {
                    $imageSrc = $basePath . '/uploads/img/' . htmlspecialchars($r['imagePath']);
                }
                ?>

                <div class="card">
                     <div class="card-image"><?php if (!empty($imageSrc)): ?><img src="<?php echo $imageSrc; ?>" alt="Vinyl cover"><?php endif; ?> </div>

    <div class="card-title">
        <?php echo htmlspecialchars($r['title']); ?>
    </div>

    <a class="card-btn" href="details.php?id=<?php echo (int)$r['listingID']; ?>">
        View
    </a>
</div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No listings available yet.</p>
        <?php endif; ?>
    </div>
</section>

<?php include 'footer1.inc.php'; ?>