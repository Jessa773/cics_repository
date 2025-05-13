<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Basic user authentication check - modify as needed
// Remove this check if you don't have user authentication yet
if (!isset($_SESSION['user_id'])) {
    //s For now, we'll set a dummy user ID for testing
    $_SESSION['user_id'] = 1;
    // In a real application, you would redirect to login
    // header("Location: ../login.php");
    // exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Source Code - CICS Repository</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        textarea { min-height: 200px; }
        button { padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        .btn-secondary { background-color: #6c757d; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
        .alert-success { background-color: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Submit New Source Code</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        
        <form action="save.php" method="POST">
            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label for="content">Source Code *</label>
                <textarea id="content" name="content" rows="15" required></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit">Save Source Code</button>
                <a href="list.php" style="margin-left: 10px; text-decoration: none;">
                    <button type="button" class="btn-secondary">Cancel</button>
                </a>
            </div>
        </form>
    </div>
</body>
</html>
