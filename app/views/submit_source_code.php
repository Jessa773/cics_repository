<?php
session_start();

// Database connection configuration
require_once '../config/database.php';

$submittedCode = $_SESSION['submitted_code'] ?? null;

if (!$submittedCode) {
    $_SESSION['error'] = "No source code submitted.";
    header("Location: /cics-repository/app/views/source_code_form.php");
    exit();
}

// Store the submitted code in the database
try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->prepare("INSERT INTO source_codes (title, description, content, user_id, created_at) 
                           VALUES (:title, :description, :content, :user_id, NOW())");
    
    // Get user ID from session (assuming you have user authentication)
    $user_id = $_SESSION['user_id'] ?? 1; // Default to 1 if not set
    
    $stmt->bindParam(':title', $submittedCode['title']);
    $stmt->bindParam(':description', $submittedCode['description']);
    $stmt->bindParam(':content', $submittedCode['content']);
    $stmt->bindParam(':user_id', $user_id);
    
    $stmt->execute();
    
    // Add success message to session
    $_SESSION['success'] = "Source code successfully saved to the database.";
    
} catch(PDOException $e) {
    // Store error message in session
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submitted Source Code</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Submitted Source Code</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success'] ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h3><?= htmlspecialchars($submittedCode['title']) ?></h3>
            </div>
            <div class="card-body">
                <p><strong>Description:</strong></p>
                <p><?= nl2br(htmlspecialchars($submittedCode['description'])) ?></p>
                <hr>
                <p><strong>Source Code:</strong></p>
                <pre><?= htmlspecialchars($submittedCode['content']) ?></pre>
            </div>
        </div>
        <div class="mt-3 text-center">
            <a href="/cics-repository/app/views/source_code_form.php" class="btn btn-secondary">Back to Form</a>
            <a href="/cics-repository/app/views/source_code_list.php" class="btn btn-primary">View All Source Codes</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
