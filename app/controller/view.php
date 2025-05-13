<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Basic user authentication check
if (!isset($_SESSION['user_id'])) {
    // For testing purposes
    $_SESSION['user_id'] = 1;
}

// Include database configuration
require_once '../config/database.php';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid source code ID.";
    header("Location: list.php");
    exit();
}

$id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Get the source code details
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM source_codes WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    
    $code = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$code) {
        $_SESSION['error'] = "Source code not found or you don't have permission to view it.";
        header("Location: list.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $_SESSION['error'] = "Failed to retrieve source code.";
    header("Location: list.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($code['title']); ?> - CICS Repository</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        pre { background-color: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .btn { display: inline-block; padding: 8px 12px; margin: 2px; text-decoration: none; color: white; border-radius: 4px; }
        .btn-primary { background-color: #007bff; }
        .btn-warning { background-color: #ffc107; color: #212529; }
        .btn-danger { background-color: #dc3545; }
        .actions { margin: 20px 0; }
        .meta { color: #666; font-size: 0.9em; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($code['title']); ?></h1>
        
        <div class="meta">
            Created: <?php echo date('F j, Y, g:i a', strtotime($code['created_at'])); ?>
            <?php if ($code['updated_at'] != $code['created_at']): ?>
                | Updated: <?php echo date('F j, Y, g:i a', strtotime($code['updated_at'])); ?>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($code['description'])): ?>
            <h3>Description</h3>
            <p><?php echo nl2br(htmlspecialchars($code['description'])); ?></p>
        <?php endif; ?>
        
        <h3>Source Code</h3>
        <pre><code><?php echo htmlspecialchars($code['content']); ?></code></pre>
        
        <div class="actions">
            <a href="list.php" class="btn btn-primary">Back to List</a>
            <a href="edit.php?id=<?php echo $code['id']; ?>" class="btn btn-warning">Edit</a>
            <a href="delete.php?id=<?php echo $code['id']; ?>" class="btn btn-danger" 
               onclick="return confirm('Are you sure you want to delete this source code?')">Delete</a>
        </div>
    </div>
</body>
</html>
