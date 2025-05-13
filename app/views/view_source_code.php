<?php
session_start();
require_once '../config/database.php';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid source code ID.";
    header("Location: /cics-repository/app/views/source_code_list.php");
    exit();
}

$code_id = $_GET['id'];

// Fetch source code details from database
try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->prepare("
        SELECT sc.*, u.username 
        FROM source_codes sc
        JOIN users u ON sc.user_id = u.id
        WHERE sc.id = :id
    ");
    $stmt->bindParam(':id', $code_id);
    $stmt->execute();
    
    $code = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$code) {
        $_SESSION['error'] = "Source code not found.";
        header("Location: /cics-repository/app/views/source_code_list.php");
        exit();
    }
    
} catch(PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: /cics-repository/app/views/source_code_list.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($code['title']) ?> - Source Code Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add syntax highlighting library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/vs2015.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Source Code Details</h1>
        
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h3><?= htmlspecialchars($code['title']) ?></h3>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <p><strong>Submitted by:</strong> <?= htmlspecialchars($code['username']) ?></p>
                    <p><strong>Date:</strong> <?= date('F d, Y g:i A', strtotime($code['created_at'])) ?></p>
                </div>
                
                <h4>Description:</h4>
                <p><?= nl2br(htmlspecialchars($code['description'])) ?></p>
                
                <h4 class="mt-4">Source Code:</h4>
                <pre><code class="code-highlight"><?= htmlspecialchars($code['content']) ?></code></pre>
            </div>
        </div>
        
        <div class="mt-3 text-center">
            <a href="/cics-repository/app/views/source_code_list.php" class="btn btn-primary">Back to List</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Add highlight.js for syntax highlighting -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            document.querySelectorAll('pre code').forEach((el) => {
                hljs.highlightElement(el);
            });
        });
    </script>
</body>
</html>
