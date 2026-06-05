<?php
session_start();

// title and extra css for this page
$pageTitle = "Catalogue";
$extraCSS = "catalogue.css";

// include header first
include 'header1.inc.php';

// database connection
require 'dbConnect.php';

// get search text from form
$search = htmlspecialchars(trim($_GET['search'] ?? ''));


if ($search !== '') 
{
    $search = "%".$search."%";

    $stmt = $dbConnect->prepare("
        SELECT listingID, title, artist, genre, price
        FROM vinylListings
        WHERE title LIKE :s OR artist LIKE :s OR genre LIKE :s
        ORDER BY price ASC
    ");
    
    $stmt->execute([':s' => $search]);
} 
else 
{
    $stmt = $dbConnect->query("
        SELECT listingID, title, artist, genre, price
        FROM vinylListings
        ORDER BY whenCreated DESC
    ");
}

// get all rows into array
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="catalogue-page">

    <section class="catalogue-search-section">
        <form action="catalogue.php" method="get" class="catalogue-search-form">
            <input 
                type="text" 
                name="search" 
                value="<?php echo htmlspecialchars($search); ?>" 
                placeholder="Search title / artist / genre"
                class="catalogue-search-input"
            >

            <button type="submit" class="catalogue-search-btn">Search</button>

            <?php if ($search !== ''): ?>
                <a href="catalogue.php" class="catalogue-reset-link">Reset</a>
            <?php endif; ?>
        </form>
    </section>

    <section class="catalogue-table-section">
        <div class="catalogue-table-wrap">
            <table class="catalogue-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Artist</th>
                        <th>Genre</th>
                        <th>Price</th>
                        <th>Details</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($rows) > 0): ?>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['title']); ?></td>
                                <td><?php echo htmlspecialchars($r['artist']); ?></td>
                                <td><?php echo htmlspecialchars($r['genre']); ?></td>
                                <td>£<?php echo number_format($r['price'], 2); ?></td>
                                <td>
                                    <a href="details.php?id=<?php echo (int)$r['listingID']; ?>" class="view-link">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="no-results">No listings found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>

<?php include 'footer1.inc.php'; ?>