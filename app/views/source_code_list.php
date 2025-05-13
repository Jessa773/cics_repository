<!DOCTYPE html>
<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['error'] = "You must log in to access this page.";
    header("Location: /cics-repository/app/views/login.php");
    exit();
}

// Show success message
if (isset($_SESSION['success'])) {
    echo "<p style='color: green;'>{$_SESSION['success']}</p>";
    unset($_SESSION['success']);
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM source_codes WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $sourceCodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching data: " . $e->getMessage();
}

$user = $_SESSION['user'] ?? null;
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Source Code List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1200px;
        }
        .page-header {
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 1rem;
        }
        .card {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: none;
            border-radius: 8px;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .table td, .table th {
            padding: 1rem;
            vertical-align: middle;
        }
        .btn-action {
            margin: 0 0.2rem;
        }
    </style>
</head>
<body>
<?php require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php'; ?>
    <div class="container mt-5 mb-5">
        <div class="row mb-4 page-header">
            <div class="col">
                <h2 class="fw-bold text-primary"><i class="bi bi-house-door me-2"></i>Home</h2>
            </div>
        <div class="row mb-4 page-header">
            <div class="col">
                <h2 class="fw-bold text-primary"><i class="bi bi-code-square me-2"></i>Source Code Repository</h2>
            </div>
            <div class="col text-end">
                <a href="/cics-repository/app/views/source_code_form.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Add New Code
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Created Date</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($sourceCodes) && !empty($sourceCodes)): ?>
                                <?php foreach ($sourceCodes as $code): ?>
                                    <tr>
                                        <td class="fw-medium"><?= htmlspecialchars($code['title']) ?></td>
                                        <td class="text-muted"><?= htmlspecialchars($code['description']) ?></td>
                                        <td class="text-nowrap"><?= htmlspecialchars($code['created_at']) ?></td>
                                        <td class="text-center">
                                            <a href="/cics-repository/app/views/view_source_code.php?id=<?= $code['id'] ?>" class="btn btn-sm btn-outline-info btn-action">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="/source-code/edit/<?= $code['id'] ?>" class="btn btn-sm btn-outline-warning btn-action">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger btn-action" onclick="deleteCode(<?= $code['id'] ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                        No source codes found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteCode(id) {
            if (confirm('Are you sure you want to delete this code?')) {
                window.location.href = `/source-code/delete/${id}`;
            }
        }
    </script>
</body>
</html>
