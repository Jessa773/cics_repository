<?php
session_start(); // Ensure session is started

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Redirect to login page if not logged in
    header("Location: /login.php");
    exit;
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';
$db = Database::getInstance()->getConnection();

// Get user data
$userId = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? 'User';

// Initialize counters
$documentCount = 0;
$downloadCount = 0;
$contributionCount = 0;
$recentActivities = [];

// Only query if user is logged in
if ($userId) {
    try {
        // Count user's documents
        $stmt = $db->prepare("SELECT COUNT(*) FROM documents WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $documentCount = $stmt->fetchColumn();
        
        // Count user's downloads (assuming you have a downloads table)
        $stmt = $db->prepare("SELECT COUNT(*) FROM downloads WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $downloadCount = $stmt->fetchColumn();
        
        // Count user's contributions (assuming you have a contributions table)
        $stmt = $db->prepare("SELECT COUNT(*) FROM contributions WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $contributionCount = $stmt->fetchColumn();
        
        // Get recent activities
        $stmt = $db->prepare("
            SELECT activity_date as date, description 
            FROM user_activities 
            WHERE user_id = :user_id 
            ORDER BY activity_date DESC 
            LIMIT 5
        ");
        $stmt->execute(['user_id' => $userId]);
        $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Dashboard DB error: ' . $e->getMessage());
        // Silently fail and show empty statistics
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CICS Repository</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <!-- Add Bootstrap for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dashboard-container {
            padding: 2rem 0;
        }
        .dashboard-header {
            margin-bottom: 2rem;
        }
        .dashboard-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        .stat-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            width: 30%;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #007bff;
        }
        .quick-actions {
            margin-bottom: 2rem;
        }
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        .activity-list {
            margin-top: 1rem;
        }
        .activity-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        .activity-date {
            color: #6c757d;
            margin-right: 1rem;
        }
    </style>
</head>
<body>
    <?php require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php'; ?>
    
    <div class="container dashboard-container">
        <div class="dashboard-header">
            <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        </div>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>My Files</h3>
                <p class="stat-number"><?php echo $documentCount; ?></p>
            </div>
            <div class="stat-card">
                <h3>Downloads</h3>
                <p class="stat-number"><?php echo $downloadCount; ?></p>
            </div>
            <div class="stat-card">
                <h3>Contributions</h3>
                <p class="stat-number"><?php echo $contributionCount; ?></p>
            </div>
        </div>
        
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="/cics-repository/app/views/source_code_form.php" class="btn btn-primary">Upload</a>
                <a href="/cics-repository/app/views/source_code_list.php" class="btn btn-secondary">Browse Repository</a>
                <a href="/cics-repository/app/views/profile.php" class="btn btn-info">Edit Profile</a>
            </div>
        </div>
        
        <div class="recent-activity">
            <h2>Recent Activity</h2>
            <?php if (!empty($recentActivities)): ?>
                <div class="activity-list">
                    <?php foreach ($recentActivities as $activity): ?>
                        <div class="activity-item">
                            <span class="activity-date"><?php echo htmlspecialchars(date('M d, Y', strtotime($activity['date']))); ?></span>
                            <span class="activity-description"><?php echo htmlspecialchars($activity['description']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No recent activity</p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/dashboard.js"></script>
</body>
</html>
