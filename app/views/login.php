<?php 
session_start(); // Ensure session is started

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load DB class
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';

// Get PDO connection
$pdo = Database::getInstance()->getConnection();

$user = $_SESSION['user'] ?? null;
$error = '';

try {
    $pdo->query('SELECT 1'); // Test connection

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = "Please fill in all fields";
        } else {
            $stmt = $pdo->prepare("SELECT user_id, username, password, role FROM users WHERE username = :u");
            $stmt->execute(['u' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['role']      = $user['role'];
                $_SESSION['logged_in'] = true;

                // Define the redirect path based on the user's role
                $redirect = ($user['role'] === 'admin') ? '/admin/dashboard.php' : '/app/views/dashboard.php';

                // Handle AJAX requests
                if (
                    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
                ) {
                    ob_clean();
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'redirect' => $redirect]);
                    exit;
                }

                // Redirect for non-AJAX requests
                header("Location: /cics-repository/app/views/dashboard.php");
                exit;
            } else {
                $error = "Invalid username or password";
            }
        }

        // Handle AJAX error response
        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $error]);
            exit;
        }
    }
} catch (PDOException $e) {
    error_log('Login DB error: ' . $e->getMessage());
    $error = "System error. Please try again later.";

    if (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $error
        ]);
        exit;
    }

    if (ini_get('display_errors')) {
        echo "<pre>DB Exception: " . $e->getMessage() . "</pre>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login – CICS Repository</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
</head>
<body>

<?php require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php'; ?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header text-center">
          <h3>Login</h3>
        </div>
        <div class="card-body">
          <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>

          <form id="loginForm" method="POST" action="">
            <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <input type="text" id="username" name="username" class="form-control" required />
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" id="password" name="password" class="form-control" required />
            </div>
            <div id="loginError" class="text-danger mb-3" style="display: none;"></div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary">Login</button>
            </div>
          </form>

          <div class="mt-3 text-center">
            <a href="/forgot-password">Forgot Password?</a>
          </div>
          <div class="mt-3 text-center">
            Don't have an account? <a href="/cics-repository/app/views/register.php">Register here</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Modified the JavaScript handling to ensure proper form submission -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const loginForm = document.getElementById('loginForm');
  const loginError = document.getElementById('loginError');
  
  loginForm.addEventListener('submit', function(e) {
    // For regular form submission, we let the PHP handle it
    // Only intercept for AJAX if needed
    if (false) { // Disabled AJAX handling to ensure PHP redirects work
      e.preventDefault();
      
      const formData = new FormData(loginForm);
      
      fetch(loginForm.action || window.location.href, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          window.location.href = data.redirect;
        } else {
          loginError.textContent = data.message;
          loginError.style.display = 'block';
        }
      })
      .catch(error => {
        loginError.textContent = 'An error occurred. Please try again.';
        loginError.style.display = 'block';
      });
    }
  });
});
</script>
</body>
</html>
