<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Basic user authentication check - modify as needed
if (!isset($_SESSION['user_id'])) {
    // For testing purposes
    $_SESSION['user_id'] = 1;
    // In a real app, redirect to login
    // header("Location: ../login.php");
    // exit();
}

// Include database configuration
require_once '../config/database.php';

// Get all source codes for the logged-in user
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id, title, description, created_at FROM source_codes WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $sourceCodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $_SESSION['error'] = "Failed to retrieve source codes.";
    $sourceCodes = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Source Codes - CICS Repository</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn { display: inline-block; padding: 8px 12px; margin: 2px; text-decoration: none; color: white; border-radius: 4px; }
        .btn-primary { background-color: #007bff; }
        .btn-info { background-color: #17a2b8; }
        .btn-warning { background-color: #ffc107; color: #212529; }
        .btn-danger { background-color: #dc3545; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
        .alert-success { background-color: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="container">
        <h1>My Source Codes</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['success']; 
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        
        <a href="add.php" class="btn btn-primary">Add New Source Code</a>
        
        <?php if (empty($sourceCodes)): ?>
            <p>You haven't added any source codes yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sourceCodes as $code): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($code['title']); ?></td>
                        <td><?php 
                            $desc = htmlspecialchars($code['description'] ?? '');
                            echo strlen($desc) > 100 ? substr($desc, 0, 100) . '...' : $desc; 
                        ?></td>
                        <td><?php echo isset($code['created_at']) ? date('M j, Y', strtotime($code['created_at'])) : 'N/A'; ?></td>
                        <td>
                            <a href="view.php?id=<?php echo $code['id']; ?>" class="btn btn-info">View</a>
                            <a href="edit.php?id=<?php echo $code['id']; ?>" class="btn btn-warning">Edit</a>
                            <a href="delete.php?id=<?php echo $code['id']; ?>" class="btn btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this source code?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
