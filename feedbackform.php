<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Set fixed receiver email
$to_email = "yuritsantos.0@gmail.com";
$sender_email = $subject = $message_content = '';
$errors = [];

// Start session for rate limiting
session_start();

// Rate limiting (60 seconds between submissions)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['last_submit']) && (time() - $_SESSION['last_submit'] < 60)) {
        $errors[] = "Please wait 60 seconds between submissions";
    } else {
        $_SESSION['last_submit'] = time();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($errors)) {
    $sender_email = htmlspecialchars(trim($_POST['email']));
    $subject = htmlspecialchars(trim($_POST['subject']));
    $message_content = htmlspecialchars(trim($_POST['message']));

    // Validation
    $errors = [];
    if (empty($sender_email)) {
        $errors[] = "Your email is required";
    } elseif (!filter_var($sender_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    if (empty($subject)) {
        $errors[] = "Subject is required";
    }
    if (empty($message_content)) {
        $errors[] = "Message is required";
    }

    if (empty($errors)) {
        try {
            // Configure main email
            $mail = new PHPMailer(true);
            $mail->SMTPDebug = 2; // Enable verbose debug output
            $mail->Debugoutput = function($str, $level) {
                error_log("PHPMailer: [$level] $str");
            };

            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.elasticemail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'yuritsantos.0@gmail.com';
            $mail->Password = '79A4B1C180F1BA8388CFCD5722E98DC26E91';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 2525;
            $mail->Timeout = 15;

            // Recipients
            $mail->setFrom('yuritsantos.0@gmail.com', 'Website Feedback', false);
            $mail->addAddress($to_email);
            $mail->addReplyTo($sender_email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = "
                <h3 style='color: #0f766e;'>New Feedback Received</h3>
                <p><strong>From:</strong> $sender_email</p>
                <p><strong>Subject:</strong> $subject</p>
                <p><strong>Message:</strong></p>
                <p>$message_content</p>
            ";

            // Send main email
            $mail->send();

            

            // Clear form values
            $sender_email = $subject = $message_content = '';
            echo "<div class='success'>Thank you for your feedback! We'll respond shortly.</div>";

        } catch (Exception $e) {
            $errorMessage = "Message could not be sent. Mailer Error: " . htmlspecialchars($mail->ErrorInfo);
            error_log($errorMessage);
            echo "<div class='error'>$errorMessage</div>";
        }
    } else {
        displayErrors($errors);
    }
}

function displayErrors($errors) {
    echo "<div class='error'>";
    foreach ($errors as $error) {
        echo "<p>$error</p>";
    }
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Form</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    :root {
        --primary: #0f766e;
        --success: #22c55e;
        --error: #ef4444;
        --background: #f8fafc;
        --text: #0f172a;
    }

    body {
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        margin: 0;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        padding: 1rem;
    }

    .feedback-form {
        width: 100%;
        max-width: 600px;
        background: white;
        border-radius: 1.5rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        margin: 1rem;
        overflow: hidden;
    }

    .form-header {
        background: linear-gradient(135deg, var(--primary) 0%, #0d554f 100%);
        color: white;
        padding: 2rem;
        text-align: center;
    }

    .form-header h2 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 700;
    }

    .form-header .form-subtitle {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.95);
        margin: 0.5rem auto 0;
        max-width: 80%;
        line-height: 1.4;
        font-weight: 300;
    }

    .form-content {
        padding: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
        position: relative;
    }

    label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--text);
        font-weight: 500;
        font-size: 0.9rem;
    }

    input,
    textarea {
        width: 100%;
        padding: 0.8rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 0.75rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f8fafc;
        box-sizing: border-box;
    }

    input:focus,
    textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1);
        background: white;
    }

    textarea {
        height: 150px;
        resize: vertical;
    }

    button {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, var(--primary) 0%, #0d554f 100%);
        color: white;
        border: none;
        border-radius: 0.75rem;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(15, 118, 110, 0.2);
    }

    .error {
        background: #fee2e2;
        color: var(--error);
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid var(--error);
        border-radius: 0.5rem;
        animation: slideIn 0.3s ease-out;
    }

    .success {
        background: #dcfce7;
        color: var(--success);
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid var(--success);
        border-radius: 0.5rem;
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 480px) {
        .feedback-form {
            margin: 0.5rem;
            border-radius: 1rem;
        }
        
        .form-header {
            padding: 1.5rem;
        }
        
        .form-header .form-subtitle {
            font-size: 0.8rem;
            max-width: 90%;
            margin-top: 0.25rem;
        }
        
        .form-content {
            padding: 1.5rem;
        }
    }
    </style>
</head>
<body>
    <div class="feedback-form">
        <div class="form-header">
            <h2>FEEDBACK</h2>
            <p class="form-subtitle">We would love to hear your thoughts, suggestions, or concerns!</p>
        </div>
        
        <div class="form-content">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <!-- User Email Field -->
                <div class="form-group">
                    <label for="email">Your Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($sender_email); ?>"
                           placeholder="your.email@example.com"
                           required>
                </div>

                <!-- Subject Field -->
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" 
                           value="<?php echo htmlspecialchars($subject); ?>"
                           placeholder="Feedback Subject"
                           required>
                </div>

                <!-- Message Field -->
                <div class="form-group">
                    <label for="message">Your Message</label>
                    <textarea id="message" name="message" 
                              placeholder="Write your message here..."
                              required><?php echo htmlspecialchars($message_content); ?></textarea>
                </div>

                <button type="submit">
                    <i class="fas fa-paper-plane"></i>
                    Send Message
                </button>
            </form>
        </div>
    </div>
</body>
</html>