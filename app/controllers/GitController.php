<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Basic user authentication check - modify as needed
if (!isset($_SESSION['user_id'])) {
    // For testing purposes, set dummy user
    $_SESSION['user_id'] = 1;
    // In a real app, redirect to login
    // header("Location: ../login.php");
    // exit();
}

// Include database configuration
require_once '../config/database.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $user_id = $_SESSION['user_id'];

    // Validate required fields
    if (empty($title) || empty($content)) {
        $_SESSION['error'] = "Title and Source Code are required.";
        header("Location: add.php");
        exit();
    }

    try {
        // Insert into the database
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO source_codes (title, description, content, user_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $description, $content, $user_id]);

        // Set success message
        $_SESSION['success'] = "Source code successfully saved!";

        // Redirect to the list page
        header("Location: list.php");
        exit();
    } catch (PDOException $e) {
        // Log the error
        error_log("Database Error: " . $e->getMessage());
        
        // Provide user-friendly message
        $_SESSION['error'] = "Failed to save source code. Please try again later.";
        
        // For debugging (remove in production)
        $_SESSION['error'] .= " Error: " . $e->getMessage();
        
        header("Location: add.php");
        exit();
    }
} else {
    // If not POST request, redirect to the form
    header("Location: add.php");
    exit();
}
