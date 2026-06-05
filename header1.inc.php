<?php
// start session only if not started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// main folder path for links and files
$basePath = "/~s2143615/assesmentFolder";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'MusicOnline'; ?></title>

<link rel="stylesheet" href="<?php echo $basePath; ?>/index.css?v=2">

<?php
// extra css for normal pages
if (!empty($extraCSS)) {
    echo '<link rel="stylesheet" href="' . $basePath . '/' . htmlspecialchars($extraCSS) . '?v=2">';
}

// extra css for admin or user panels
if (!empty($adminCss)) {
    echo '<link rel="stylesheet" href="' . htmlspecialchars($adminCss) . '?v=2">';
}
?>

</head>

<body>

<header>
<div class="header-container">

<div class="logo">
<a href="<?php echo $basePath; ?>/index.php">
<img src="<?php echo $basePath; ?>/uploads/img/music.png" alt="MusicOnline logo">
</a>
</div>

<nav>

<a href="<?php echo $basePath; ?>/index.php">Home</a>

<a href="<?php echo $basePath; ?>/catalogue.php">Catalogue</a>

<?php
// show account link depending on login status
if (isset($_SESSION['userID']))
{
    if (!empty($_SESSION['username']))
    {
        echo "<span class='hello'>Hello, " . htmlspecialchars($_SESSION['username']) . "</span>";
    }

    if ((int)($_SESSION['ifAdmin'] ?? 0) === 1)
    {
        echo '<a href="' . $basePath . '/user/adminPanel.php">Account</a>';
    }
    else
    {
        echo '<a href="' . $basePath . '/user/userPanel.php">Account</a>';
    }
}
else
{
    echo '<a href="' . $basePath . '/account.php">Account</a>';
}
?>

<a class="contact-btn" href="<?php echo $basePath; ?>/contactUs.php">Contact Us</a>

</nav>

<div class="header-spacer"></div>

</div>
</header>

<main>