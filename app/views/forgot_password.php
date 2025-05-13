<?php
session_start(); // Ensure session is started

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load DB class
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';

// Get PDO connection
$pdo = Database::getInstance()->getConnection();

$message = '';
$messageType = '';

try {
    $pdo->query('SELECT 1'); // Test connection

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $message = "Please enter your email address";
            $messageType = "danger";
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Please enter a valid email address";
            $messageType = "danger";
        } else {
            // Check if the email exists in the database
            $stmt = $pdo->prepare("SELECT user_id, username, email FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user) {
                // Generate a unique token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Store the token in the database
                $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
                $stmt->execute([
                    'user_id' => $user['user_id'],
                    'token' => $token,
                    'expires_at' => $expires
                ]);

                // Construct the reset link
                $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;

                // In a real application, you would send an email with the reset link
                // For demonstration purposes, we'll just show the link on the page
                $message = "If an account with that email exists, a password reset link has been sent. Please check your email.";
                $messageType = "success";
                
                // This is for demonstration only - in production, send an actual email and don't display the link
                $demoMessage = "<strong>For demonstration:</strong> Your password reset link is: <a href='$resetLink'>$resetLink</a>";
            } else {
                // For security reasons, don't indicate whether the email exists or not
                $message = "If an account with that email exists, a password reset link has been sent. Please check your email.";
                $messageType = "success";
            }
        }
    }
} catch (PDOException $e) {
    error_log('Forgot Password DB error: ' . $e->getMessage());
    $message = "System error. Please try again later.";
    $messageType = "danger";

    if (ini_get('display_errors')) {
        echo "<pre>DB Exception: " . $e->getMessage() . "</pre>";
    }
}

// Define the fixed paths for links
$loginPath = '/cics-repository/app/views/login.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Forgot Password – CICS Repository</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
</head>
<body>

<?php require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php'; ?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header text-center">
          <h3>Forgot Password</h3>
        </div>
        <div class="card-body">
          <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
            <?php if (isset($demoMessage)): ?>
              <div class="alert alert-info"><?php echo $demoMessage; ?></div>
            <?php endif; ?>
          <?php endif; ?>

          <p class="mb-4">Enter your email address below and we'll send you a link to reset your password.</p>

          <form id="forgotPasswordForm" method="POST" action="">
            <div class="mb-3">
              <label for="email" class="form-label">Email Address</label>
              <input type="email" id="email" name="email" class="form-control" required />
            </div>
            <div class="d-grid mb-3">
              <button type="submit" class="btn btn-primary">Send Reset Link</button>
            </div>
          </form>

          <div class="mt-3 text-center">
            <a href="<?php echo $loginPath; ?>">Back to Login</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const forgotPasswordForm = document.getElementById('forgotPasswordForm');
  
  forgotPasswordForm.addEventListener('submit', function(e) {
    // Client-side validation
    const email = document.getElementById('email').value.trim();
    
    if (email === '') {
      e.preventDefault();
      alert('Please enter your email address');
      return false;
    }
    
    // Simple email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      e.preventDefault();
      alert('Please enter a valid email address');
      return false;
    }
  });
});
</script>
</body>
</html>
