<?php
$pageTitle = 'Registration';
include('header1.inc.php');

session_start();

$errors = [];
$cleanData = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $fields = ['username', 'email', 'password'];

    foreach ($fields as $field)
    {
        if (empty($_POST[$field]))
        {
            $errors[] = "You need to enter a " . ucfirst($field) . ".";
        }
        else
        {
            $cleanData[$field] = trim($_POST[$field]);
        }
    }

    $cleanData['ifAdmin'] = isset($_POST['ifAdmin']) ? 1 : 0;

    if (empty($errors))
    {
        try
        {
            require('dbConnect.php');

            $check = $dbConnect->prepare(
                "SELECT userID FROM users 
                 WHERE username = :u OR email = :e 
                 LIMIT 1"
            );

            $check->execute([
                ':u' => $cleanData['username'],
                ':e' => $cleanData['email']
            ]);

            if ($check->fetch())
            {
                $errors[] = "Username or email already exists.";
            }
            else
            {
                $passwordHash = password_hash($cleanData['password'], PASSWORD_DEFAULT);

                $stmt = $dbConnect->prepare("
                    INSERT INTO users (username, email, password_hash, ifAdmin, created_at)
                    VALUES (:username, :email, :pass, :ifAdmin, NOW())
                ");

                $stmt->bindValue(':username', $cleanData['username']);
                $stmt->bindValue(':email', $cleanData['email']);
                $stmt->bindValue(':pass', $passwordHash);
                $stmt->bindValue(':ifAdmin', $cleanData['ifAdmin'], PDO::PARAM_INT);

                if ($stmt->execute())
                {
                    echo "<h2>Successful registration</h2>";
                    echo "<p><a href='login.php'>Go to login</a></p>";
                }
            }
        }
        catch(PDOException $e)
        {
            echo "<h2>Error registering user</h2>";
            echo "<p>".$e->getMessage()."</p>";
        }
    }

    if (!empty($errors))
    {
        echo "<p>The following errors occurred:</p>";
        foreach ($errors as $message)
        {
            echo htmlspecialchars($message) . "<br>";
        }
    }
}
?>

<h1>Registration Form</h1>

<form action="register.php" method="POST">
    <p>Username:
        <input type="text" name="username"
               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
    </p>

    <p>Email:
        <input type="email" name="email"
               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
    </p>

    <p>Password:
        <input type="password" name="password">
    </p>

    <p>
        <label>
            <input type="checkbox" name="ifAdmin" value="1">
            Make admin
        </label>
    </p>

    <p><input type="submit" value="Submit"></p>
</form>

<?php include('footer1.inc.php'); ?>
