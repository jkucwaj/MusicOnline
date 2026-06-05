<?php
session_start();

$pageTitle = "Contact Us";
$extraCSS = "contactUs.css";

require_once 'dbConnect.php';
require_once 'functions1.php';

$errors = [];
$cleanData = [
    'name' => '',
    'email' => '',
    'subject' => '',
    'message' => ''
];

$sent = false;

// if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // validate name
    if (empty($_POST['name'])) {
        $errors[] = "Please enter your name";
    } else {
        $cleanData['name'] = cleanUp($_POST['name']);
    }

    // validate email
    if (empty($_POST['email'])) {
        $errors[] = "Please enter your email";
    } else {
        $cleanData['email'] = cleanUp($_POST['email']);

        if (!filter_var($cleanData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter valid email";
        }
    }

    // validate subject
    if (empty($_POST['subject'])) {
        $errors[] = "Please enter subject";
    } else {
        $cleanData['subject'] = cleanUp($_POST['subject']);
    }

    // validate message
    if (empty($_POST['message'])) {
        $errors[] = "Please enter your message";
    } else {
        $cleanData['message'] = cleanUp($_POST['message']);
    }

    // save message into database
    if (empty($errors)) {
        try {
            $stmt = $dbConnect->prepare("
                INSERT INTO contact (name, email, subject, message)
                VALUES (:name, :email, :subject, :message)
            ");

            $stmt->bindValue(':name', $cleanData['name'], PDO::PARAM_STR);
            $stmt->bindValue(':email', $cleanData['email'], PDO::PARAM_STR);
            $stmt->bindValue(':subject', $cleanData['subject'], PDO::PARAM_STR);
            $stmt->bindValue(':message', $cleanData['message'], PDO::PARAM_STR);

            $stmt->execute();

            $sent = true;

            $cleanData = [
                'name' => '',
                'email' => '',
                'subject' => '',
                'message' => ''
            ];

        } catch (PDOException $ex) {
            $errors[] = "Database error please try again later";
        }
    }
}

include 'header1.inc.php';
?>

<div class="contact-page">

    <div class="contact-card">
        <div class="contact-heading">
            <h2>Contact Us</h2>
            <p>
                Have a question about a vinyl listing or your account send us a message below
            </p>
        </div>

        <?php if ($sent): ?>
            <div class="contact-success">
                <h3>Message sent</h3>
                <p>Thank you your message has been saved successfully</p>
            </div>
        <?php else: ?>

            <?php if (!empty($errors)): ?>
                <div class="contact-errors">
                    <strong>Please fix these things</strong><br>
                    <?php foreach ($errors as $msg): ?>
                        <?php echo e($msg); ?><br>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form class="contact-form" method="POST" action="contactUs.php">

                <div class="form-row">
                    <label for="name">Name</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="<?php echo e($cleanData['name']); ?>"
                        maxlength="100"
                    >
                </div>

                <div class="form-row">
                    <label for="email">Email</label>
                    <input
                        type="text"
                        name="email"
                        id="email"
                        value="<?php echo e($cleanData['email']); ?>"
                        maxlength="100"
                    >
                </div>

                <div class="form-row">
                    <label for="subject">Subject</label>
                    <input
                        type="text"
                        name="subject"
                        id="subject"
                        value="<?php echo e($cleanData['subject']); ?>"
                        maxlength="150"
                    >
                </div>

                <div class="form-row">
                    <label for="message">Message</label>
                    <textarea
                        name="message"
                        id="message"
                        rows="7"
                        maxlength="3000"
                    ><?php echo e($cleanData['message']); ?></textarea>
                </div>

                <div class="contact-btn-wrap">
                    <button type="submit" class="contact-btn">Send Message</button>
                </div>

            </form>
        <?php endif; ?>
    </div>

</div>

<?php include 'footer1.inc.php'; ?>