<?php
session_start(); // Ensure session is started
// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Redirect to login page if not logged in
    header("Location: /login.php");
    exit;
}
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';
// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid source code ID.";
    header("Location: /cics-repository/app/views/source_code_list.php");
    exit();
}
$id = (int)$_GET['id'];
$userId = $_SESSION['user_id'];
// Get the source code details
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM source_codes WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    
    $code = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$code) {
        $_SESSION['error'] = "Source code not found or you don't have permission to edit it.";
        header("Location: /cics-repository/app/views/source_code_list.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    $_SESSION['error'] = "Failed to retrieve source code.";
    header("Location: /cics-repository/app/views/source_code_list.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Source Code - CICS Repository</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <!-- Add Bootstrap for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 0;
        }
        .code-editor {
            font-family: monospace;
            min-height: 300px;
        }
    </style>
</head>
<body>
    <?php require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'header.php'; ?>
    
    <div class="container form-container">
        <h1>Edit Source Code</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['success']; 
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        
        <form action="/cics-repository/app/controllers/source_code_update.php" method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($code['id']); ?>">
            
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($code['title']); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($code['description']); ?></textarea>
            </div>
            
            <div class="mb-3">
                <label for="language" class="form-label">Programming Language</label>
                <select class="form-select" id="language" name="language" required>
                    <option value="">Select Language</option>
                    <option value="php" <?php echo ($code['language'] == 'php') ? 'selected' : ''; ?>>PHP</option>
                    <option value="javascript" <?php echo ($code['language'] == 'javascript') ? 'selected' : ''; ?>>JavaScript</option>
                    <option value="python" <?php echo ($code['language'] == 'python') ? 'selected' : ''; ?>>Python</option>
                    <option value="java" <?php echo ($code['language'] == 'java') ? 'selected' : ''; ?>>Java</option>
                    <option value="c" <?php echo ($code['language'] == 'c') ? 'selected' : ''; ?>>C</option>
                    <option value="cpp" <?php echo ($code['language'] == 'cpp') ? 'selected' : ''; ?>>C++</option>
                    <option value="csharp" <?php echo ($code['language'] == 'csharp') ? 'selected' : ''; ?>>C#</option>
                    <option value="ruby" <?php echo ($code['language'] == 'ruby') ? 'selected' : ''; ?>>Ruby</option>
                    <option value="html" <?php echo ($code['language'] == 'html') ? 'selected' : ''; ?>>HTML</option>
                    <option value="css" <?php echo ($code['language'] == 'css') ? 'selected' : ''; ?>>CSS</option>
                    <option value="sql" <?php echo ($code['language'] == 'sql') ? 'selected' : ''; ?>>SQL</option>
                    <option value="other" <?php echo ($code['language'] == 'other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php
                    try {
                        $stmt = $db->query("SELECT id, name FROM categories ORDER BY name");
                        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($categories as $category) {
                            $selected = ($category['id'] == $code['category_id']) ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($category['id']) . '" ' . $selected . '>' . htmlspecialchars($category['name']) . '</option>';
                        }
                    } catch (PDOException $e) {
                        error_log("DB Error: " . $e->getMessage());
                    }
                    ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="code_content" class="form-label">Source Code</label>
                <textarea class="form-control code-editor" id="code_content" name="code_content" rows="15" required><?php echo htmlspecialchars($code['code_content']); ?></textarea>
            </div>
            
            <div class="mb-3">
                <label for="visibility" class="form-label">Visibility</label>
                <select class="form-select" id="visibility" name="visibility">
                    <option value="public" <?php echo ($code['visibility'] == 'public') ? 'selected' : ''; ?>>Public</option>
                    <option value="private" <?php echo ($code['visibility'] == 'private') ? 'selected' : ''; ?>>Private</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="tags" class="form-label">Tags (comma separated)</label>
                <input type="text" class="form-control" id="tags" name="tags" value="<?php echo htmlspecialchars($code['tags']); ?>">
            </div>
            
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Update Source Code</button>
                <a href="/cics-repository/app/views/source_code_list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <!-- Add Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add any JavaScript needed for the form
        document.addEventListener('DOMContentLoaded', function() {
            // You could add syntax highlighting or form validation here
        });
    </script>
    
    <?php require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
</body>
</html>
