<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is admin (optional security check)
// Comment out or modify as needed for your authentication system
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die("You need to be logged in to run this setup script.");
}

// Include database config
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Create source_codes table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS `source_codes` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `description` TEXT,
        `content` TEXT NOT NULL,
        `user_id` INT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    $db->exec($sql);
    
    // Add entry to user_activities table for tracking this setup action
    if (isset($_SESSION['user_id'])) {
        $activity_sql = "INSERT INTO user_activities (user_id, description, activity_date) 
                        VALUES (:user_id, 'Set up source_codes table', NOW())";
        $stmt = $db->prepare($activity_sql);
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
    }
    
    echo "<h2>Setup completed successfully!</h2>";
    echo "<p>The source_codes table has been created.</p>";
    echo "<p><a href='/cics-repository/app/views/dashboard.php'>Return to Dashboard</a></p>";
    
} catch (PDOException $e) {
    die("<p>Setup Error: " . $e->getMessage() . "</p>");
}
