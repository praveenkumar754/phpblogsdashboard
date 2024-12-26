<?php
// Database Connection
$conn = new mysqli('localhost', 'root', '', 'blogs');

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the `id` parameter is passed
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $postId = (int)$_GET['id']; // Ensure ID is an integer

    // Prepare and execute the DELETE query
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->bind_param('i', $postId);

    if ($stmt->execute()) {
        // Redirect back with a success message
        header("Location: all-posts.php?message=Post deleted successfully");
        exit;
    } else {
        // Redirect back with an error message
        header("Location: all-posts.php?error=Error deleting post");
        exit;
    }
} else {
    // Redirect back if the ID parameter is missing or invalid
    header("Location: all-posts.php?error=Invalid post ID");
    exit;
}

// Close the database connection
$conn->close();
?>
