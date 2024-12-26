<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'blogs');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch category data based on the ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $category = $result->fetch_assoc();
    } else {
        $category = null;
    }
    $stmt->close();
} else {
    // Redirect if no ID is passed
    header("Location: category.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Category Details</title>
</head>
<body class="bg-gray-100">

    <!-- Navigation Bar -->
    <nav class="bg-black p-4">
        <div class="flex justify-between items-center">
            <a href="index.php" class="text-white text-lg font-semibold">Home</a>
            <ul class="flex space-x-4">
                <li><a href="category.php" class="text-white">Categories</a></li>
                <li><a href="all-posts.php" class="text-white">All Posts</a></li>
                <li><a href="add-new-post.php" class="text-white">Add New Post</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-semibold mb-6">Category Details</h1>

        <?php if ($category): ?>
            <div class="bg-white p-6 rounded-md shadow-md">
                <h2 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($category['name']); ?></h2>
                <p class="text-sm text-gray-600 mb-4"><strong>Slug:</strong> <?php echo htmlspecialchars($category['slug']); ?></p>
                <p class="text-gray-800"><?php echo nl2br(htmlspecialchars($category['description'])); ?></p>
            </div>
        <?php else: ?>
            <p class="text-red-500">Category not found.</p>
        <?php endif; ?>
    </div>

</body>
</html>
