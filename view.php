<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'blogs');

// Check if the ID is passed in the URL
if (isset($_GET['id'])) {
    $postId = (int) $_GET['id']; // Typecast to integer to prevent SQL injection
    
    // Fetch the post by ID
    $result = $conn->query("SELECT posts.*, categories.name AS category_name 
                            FROM posts 
                            LEFT JOIN categories ON posts.category = categories.id 
                            WHERE posts.id = $postId");

    // If the post exists
    if ($result && $result->num_rows > 0) {
        $post = $result->fetch_assoc();
    } else {
        echo "<p class='text-red-500 text-center'>Post not found.</p>";
        exit;
    }
} else {
    echo "<p class='text-red-500 text-center'>No post selected.</p>";
    exit;
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>View Post</title>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Main Content -->
    <div class="flex-grow p-6">

        <h1 class="text-3xl font-bold mb-6 text-center">View Post</h1>

        <div class="bg-white shadow-md rounded-md p-6 max-w-3xl mx-auto">
            <?php if (!empty($post['image_path'])): ?>
                <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post Image" class="w-full h-60 rounded-t-md mb-4 object-cover">
            <?php endif; ?>
            
            <h2 class="text-2xl font-semibold mb-4"><?php echo htmlspecialchars($post['title']); ?></h2>
            
            <p class="text-gray-700 leading-relaxed mb-4">
                <?php echo nl2br(htmlspecialchars($post['description'])); ?>
            </p>
            
            <p class="text-sm text-gray-500">Category: <span class="text-blue-600"><?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?></span></p>
            
            <p class="text-sm text-gray-500 mt-4">Posted on: <span class="text-blue-600"><?php echo htmlspecialchars($post['created_at']); ?></span></p>
            
            <a href="all-post.php" class="block mt-6 text-center text-blue-600 font-semibold hover:underline">
                Back to All Posts
            </a>
        </div>

    </div>

</body>
</html>
