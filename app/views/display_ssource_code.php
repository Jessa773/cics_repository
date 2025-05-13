<?php
session_start();

$submittedCode = $_SESSION['submitted_code'] ?? null;

if (!$submittedCode) {
    $_SESSION['error'] = "No source code submitted.";
    header("Location: /cics-repository/app/views/source_code_form.php");
    exit();
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
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
