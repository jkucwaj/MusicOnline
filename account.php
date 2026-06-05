<?php
// start session and connect database
session_start();
require 'dbConnect.php';

// page title and css file
$pageTitle = "Account";
$extraCSS = "account.css";

// if user already logged in send to correct panel
if (isset($_SESSION['userID'])) {
    if ((int)($_SESSION['ifAdmin'] ?? 0) === 1) {
        header("Location: user/adminPanel.php");
    } else {
        header("Location: user/userPanel.php");
    }
    exit();
}

// messges for page
$message = "";
$errors = [];

// register new user
if (isset($_POST['registerBtn'])) {

    $username = trim($_POST['regUsername'] ?? '');
    $email = trim($_POST['regEmail'] ?? '');
    $password = $_POST['regPassword'] ?? '';

    if ($username === '' || $email === '' || $password === '') {
        $errors[] = "Please fill in all register fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter valid email.";
    } else {
        try {
            // check if username or email already exists
            $check = $dbConnect->prepare("
                SELECT userID
                FROM users
                WHERE username = :u OR email = :e
            ");
            $check->execute([
                ':u' => $username,
                ':e' => $email
            ]);

            if ($check->fetch()) {
                $errors[] = "Username or email already exists.";
            } else {
                // hash password and save user
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $dbConnect->prepare("
                    INSERT INTO users (username, email, password_hash, ifAdmin)
                    VALUES (:u, :e, :p, 0)
                ");

                $stmt->execute([
                    ':u' => $username,
                    ':e' => $email,
                    ':p' => $passwordHash
                ]);

                $message = "Registered successfully. You can now login.";
            }

        } catch (PDOException $e) {
            $errors[] = "Registration error.";
        }
    }
}

// login existing user
if (isset($_POST['loginBtn'])) {

    $username = htmlspecialchars(trim($_POST['loginUsername'] ?? ''));
    $password = htmlspecialchars($_POST['loginPassword'] ?? '');

    if ($username === '' || $password === '') {
        $errors[] = "Please enter username and password.";
    } else {
        try {
            // get user by username
            $stmt = $dbConnect->prepare("
                SELECT *
                FROM users
                WHERE username = :u
            ");
            $stmt->execute([':u' => $username]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // check password and create session
            if ($user && password_verify($password, $user['password_hash'])) {

                $_SESSION['userID'] = $user['userID'];
                $_SESSION['ifAdmin'] = $user['ifAdmin'];
                $_SESSION['username'] = $user['username'];

                // send admin and normal user to their own panel
                if ((int)$user['ifAdmin'] === 1) {
                    header("Location: user/adminPanel.php");
                } else {
                    header("Location: user/userPanel.php");
                }
                exit();

            } else {
                $errors[] = "Invalid login.";
            }

        } catch (PDOException $e) {
            $errors[] = "Login error.";
        }
    }
}

include 'header1.inc.php';
?>

<div class="account-page">

    <div class="account-hero">
        <h1>Access your account</h1>
        <p>
            Please login to manage your listings or register to create a new account.
        </p>
    </div>

    <?php if (!empty($message)) : ?>
        <p class="account-success"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if (!empty($errors)) : ?>
        <div class="account-error">
            <?php foreach ($errors as $error) : ?>
                <?php echo htmlspecialchars($error); ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="account-forms">

        <div class="account-box">
            <h2>Login</h2>
            <form method="post" action="account.php">
                <input type="text" name="loginUsername" placeholder="Username" required>
                <input type="password" name="loginPassword" placeholder="Password" required>
                <button type="submit" name="loginBtn">ok</button>
            </form>
        </div>

        <div class="account-box">
            <h2>Register</h2>
            <form method="post" action="account.php">
                <input type="text" name="regUsername" placeholder="Username" required>
                <input type="email" name="regEmail" placeholder="Email" required>
                <input type="password" name="regPassword" placeholder="Password" required>
                <button type="submit" name="registerBtn">ok</button>
            </form>
        </div>

    </div>

</div>

<?php include 'footer1.inc.php'; ?>