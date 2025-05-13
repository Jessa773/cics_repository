<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $_SESSION['error'] = "You must log in to access this page.";
    header("Location: /cics-repository/app/views/login.php");
    exit();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';

// Get the action from the form
$action = $_POST['action'] ?? '';

// Check if action is valid
if (empty($action)) {
    $_SESSION['error'] = "Invalid action.";
    header("Location: /cics-repository/app/views/source_code_list.php");
    exit();
}

// Get user ID from session
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    $_SESSION['error'] = "User ID is not set in the session.";
    header("Location: /cics-repository/app/views/login.php");
    exit();
}

// Handle different actions
switch ($action) {
    case 'create':
        handleCreate($userId);
        break;
    case 'update':
        handleUpdate($userId);
        break;
    case 'delete':
        handleDelete($userId);
        break;
    default:
        $_SESSION['error'] = "Invalid action.";
        header("Location: /cics-repository/app/views/source_code_list.php");
        exit();
}

// Function to handle creating new source code
function handleCreate($userId) {
    // Validate required fields
    if (
        empty($_POST['title']) ||
        empty($_POST['language']) ||
        empty($_POST['category_id']) ||
        empty($_POST['code_content'])
    ) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: /cics-repository/app/views/source_code_submit.php");
        exit();
    }
    
    // Sanitize inputs
    $title = htmlspecialchars(trim($_POST['title']));
    $description = isset($_POST['description']) ? htmlspecialchars(trim($_POST['description'])) : '';
    $language = htmlspecialchars(trim($_POST['language']));
    $categoryId = (int)$_POST['category_id'];
    $codeContent = $_POST['code_content']; // Don't sanitize code content to preserve functionality
    $visibility = isset($_POST['visibility']) ? htmlspecialchars($_POST['visibility']) : 'public';
    $tags = isset($_POST['tags']) ? htmlspecialchars(trim($_POST['tags'])) : '';
    
    // Handle file upload if present
    $filePath = null;
    if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] != UPLOAD_ERR_NO_FILE) {
        $uploadResult = handleFileUpload($userId);
        if ($uploadResult['success']) {
            $filePath = $uploadResult['path'];
        } else {
            $_SESSION['error'] = $uploadResult['message'];
            header("Location: /cics-repository/app/views/source_code_submit.php");
            exit();
        }
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Insert new source code
        $stmt = $db->prepare("
            INSERT INTO source_codes 
            (user_id, title, description, language, category_id, code_content, file_path, visibility, tags, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $result = $stmt->execute([
            $userId,
            $title,
            $description,
            $language,
            $categoryId,
            $codeContent,
            $filePath,
            $visibility,
            $tags
        ]);
        
        if ($result) {
            $_SESSION['success'] = "Source code successfully submitted!";
            header("Location: /cics-repository/app/views/source_code_list.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to submit source code.";
            header("Location: /cics-repository/app/views/source_code_submit.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage());
        $_SESSION['error'] = "Database error occurred. Please try again.";
        header("Location: /cics-repository/app/views/source_code_submit.php");
        exit();
    }
}

// Function to handle updating existing source code
function handleUpdate($userId) {
    // Check if ID is provided
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        $_SESSION['error'] = "Invalid source code ID.";
        header("Location: /cics-repository/app/views/source_code_list.php");
        exit();
    }
    
    $id = (int)$_POST['id'];
    
    // Validate required fields
    if (
        empty($_POST['title']) ||
        empty($_POST['language']) ||
        empty($_POST['category_id']) ||
        empty($_POST['code_content'])
    ) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: /cics-repository/app/views/source_code_edit.php?id=$id");
        exit();
    }
    
    // Sanitize inputs
    $title = htmlspecialchars(trim($_POST['title']));
    $description = isset($_POST['description']) ? htmlspecialchars(trim($_POST['description'])) : '';
    $language = htmlspecialchars(trim($_POST['language']));
    $categoryId = (int)$_POST['category_id'];
    $codeContent = $_POST['code_content']; // Don't sanitize code content to preserve functionality
    $visibility = isset($_POST['visibility']) ? htmlspecialchars($_POST['visibility']) : 'public';
    $tags = isset($_POST['tags']) ? htmlspecialchars(trim($_POST['tags'])) : '';
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Check if the source code exists and belongs to the user
        $stmt = $db->prepare("SELECT file_path FROM source_codes WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        $existingCode = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existingCode) {
            $_SESSION['error'] = "Source code not found or you don't have permission to edit it.";
            header("Location: /cics-repository/app/views/source_code_list.php");
            exit();
        }
        
        $currentFilePath = $existingCode['file_path'];
        $filePath = $currentFilePath; // Default to keeping the current file
        
        // Handle file upload if present
        if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] != UPLOAD_ERR_NO_FILE) {
            $uploadResult = handleFileUpload($userId);
            if ($uploadResult['success']) {
                // Delete old file if it exists
                if (!empty($currentFilePath) && file_exists(dirname(__FILE__, 3) . $currentFilePath)) {
                    unlink(dirname(__FILE__, 3) . $currentFilePath);
                }
                $filePath = $uploadResult['path'];
            } else {
                $_SESSION['error'] = $uploadResult['message'];
                header("Location: /cics-repository/app/views/source_code_edit.php?id=$id");
                exit();
            }
        } else if (isset($_POST['remove_file']) && $_POST['remove_file'] == 1) {
            // Remove existing file if requested
            if (!empty($currentFilePath) && file_exists(dirname(__FILE__, 3) . $currentFilePath)) {
                unlink(dirname(__FILE__, 3) . $currentFilePath);
            }
            $filePath = null;
        }
        
        // Update the source code
        $stmt = $db->prepare("
            UPDATE source_codes 
            SET title = ?, description = ?, language = ?, category_id = ?, 
                code_content = ?, file_path = ?, visibility = ?, tags = ?, updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        
        $result = $stmt->execute([
            $title,
            $description,
            $language,
            $categoryId,
            $codeContent,
            $filePath,
            $visibility,
            $tags,
            $id,
            $userId
        ]);
        
        if ($result) {
            $_SESSION['success'] = "Source code successfully updated!";
            header("Location: /cics-repository/app/views/source_code_list.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to update source code.";
            header("Location: /cics-repository/app/views/source_code_edit.php?id=$id");
            exit();
        }
    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage());
        $_SESSION['error'] = "Database error occurred. Please try again.";
        header("Location: /cics-repository/app/views/source_code_edit.php?id=$id");
        exit();
    }
}

// Function to handle deleting source code
function handleDelete($userId) {
    // Check if ID is provided
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        $_SESSION['error'] = "Invalid source code ID.";
        header("Location: /cics-repository/app/views/source_code_list.php");
        exit();
    }
    
    $id = (int)$_POST['id'];
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // First, check if the source code exists and belongs to the user
        $stmt = $db->prepare("SELECT file_path FROM source_codes WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        $existingCode = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existingCode) {
            $_SESSION['error'] = "Source code not found or you don't have permission to delete it.";
            header("Location: /cics-repository/app/views/source_code_list.php");
            exit();
        }
        
        // Delete the file if it exists
        if (!empty($existingCode['file_path']) && file_exists(dirname(__FILE__, 3) . $existingCode['file_path'])) {
            unlink(dirname(__FILE__, 3) . $existingCode['file_path']);
        }
        
        // Delete the source code
        $stmt = $db->prepare("DELETE FROM source_codes WHERE id = ? AND user_id = ?");
        $result = $stmt->execute([$id, $userId]);
        
        if ($result) {
            $_SESSION['success'] = "Source code successfully deleted!";
        } else {
            $_SESSION['error'] = "Failed to delete source code.";
        }
        
        header("Location: /cics-repository/app/views/source_code_list.php");
        exit();
    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage());
        $_SESSION['error'] = "Database error occurred. Please try again.";
        header("Location: /cics-repository/app/views/source_code_list.php");
        exit();
    }
}

// Function to handle file uploads
function handleFileUpload($userId) {
    // Define upload directory
    $uploadDir = dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
    
    // Check if directory exists, if not create it
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return [
                'success' => false,
                'message' => "Failed to create upload directory. Please contact an administrator."
            ];
        }
    }
    
    // Check if upload directory is writable
    if (!is_writable($uploadDir)) {
        return [
            'success' => false,
            'message' => "Upload directory is not writable. Please contact an administrator."
        ];
    }
    
    // Generate unique filename
    $fileName = $userId . '_' . time() . '_' . basename($_FILES['file_upload']['name']);
    $targetFile = $uploadDir . $fileName;
    
    // Check file size (limit to 5MB)
    if ($_FILES['file_upload']['size'] > 5000000) {
        return [
            'success' => false,
            'message' => "File is too large. Maximum size is 5MB."
        ];
    }
    
    // Move the uploaded file
    if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $targetFile)) {
        return [
            'success' => true,
            'path' => '/uploads/' . $fileName // Store relative path in database
        ];
    } else {
        $uploadError = $_FILES['file_upload']['error'];
        $errorMessage = "File upload failed.";
        
        // Provide more detailed error message based on PHP upload error code
        switch ($uploadError) {
            case UPLOAD_ERR_INI_SIZE:
                $errorMessage = "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $errorMessage = "The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMessage = "The uploaded file was only partially uploaded.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMessage = "No file was uploaded.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $errorMessage = "Missing a temporary folder.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $errorMessage = "Failed to write file to disk.";
                break;
            case UPLOAD_ERR_EXTENSION:
                $errorMessage = "File upload stopped by extension.";
                break;
        }
        
        error_log("File upload error: " . $errorMessage);
        
        return [
            'success' => false,
            'message' => $errorMessage
        ];
    }
}
