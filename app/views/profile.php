<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - CICS Repository</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<?php require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php'; ?>

    <?php
    // Database connection
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=cics_repository", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }

    // Ensure $user is initialized
    if (!isset($user)) {
        $user = null;
    }
    ?>

    <div class="container profile-container">
        <div class="profile-header">
            <h1>Profile Settings</h1>
        </div>

        <div class="profile-content">
            <!-- User Info Section -->
            <section class="profile-section">
                <h2>User Information</h2>
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/controller/profile/update" class="profile-form">
                <?php echo isset($user['username']) ? htmlspecialchars($user['username']) : ''; ?>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" 
                               id="username" 
                               value="<?php echo ($user !== null) ? htmlspecialchars($user['username']) : ''; ?>" 
                               readonly 
                               class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="<?php echo ($user !== null) ? htmlspecialchars($user['email']) : ''; ?>" 
                               class="form-control" 
                               required>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </section>

            <!-- Password Change Section -->
            <section class="profile-section">
                <h2>Change Password</h2>
                <form method="POST" action="/controller/profile/change-password" class="password-form">
                <input type="hidden" name="csrf_token" value="<?php echo isset($_SESSION['csrf_token']) ? htmlspecialchars($_SESSION['csrf_token']) : ''; ?>">

                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" 
                               id="current_password" 
                               name="current_password" 
                               class="form-control" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" 
                               id="new_password" 
                               name="new_password" 
                               class="form-control" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               class="form-control" 
                               required>
                    </div>

                    <button type="submit" class="btn btn-warning">Change Password</button>
                </form>
            </section>

            <!-- Recent Activity Section -->
            <section class="profile-section">
                <h2>Recent Activity</h2>
                <div class="activity-list">
                    <?php if (!empty($activities)): ?>
                        <?php foreach ($activities as $activity): ?>
                            <div class="activity-item">
                                <span class="activity-date">
                                    <?php echo htmlspecialchars(date('M d, Y', strtotime($activity['date']))); ?>
                                </span>
                                <span class="activity-description">
                                    <?php echo htmlspecialchars($activity['description']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No recent activity</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <?php require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>

    <script src="../../assets/js/profile-validation.js"></script>
</body>
</html>
