<?php
// Ensure the user is logged in
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'blogs');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch post ID from the URL
$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch the post details
$stmt = $conn->prepare("SELECT posts.*, categories.name AS category_name FROM posts LEFT JOIN categories ON posts.category = categories.id WHERE posts.id = ?");
$stmt->bind_param('i', $postId);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

// If the post doesn't exist, redirect
if (!$post) {
    header('Location: all-post.php');
    exit;
}

// Handle form submission for updating the post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $category = (int)$_POST['category'];

    // Handle image upload (optional)
    $imagePath = $post['image_path']; // Keep existing image if not uploading a new one
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $imageName = basename($_FILES['image']['name']);
        $imagePath = $targetDir . $imageName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            echo "<script>alert('Failed to upload image.');</script>";
        }
    }

    // Update the post in the database
    $stmt = $conn->prepare("UPDATE posts SET title = ?, description = ?, image_path = ?, category = ? WHERE id = ?");
    $stmt->bind_param('sssii', $title, $description, $imagePath, $category, $postId);

    if ($stmt->execute()) {
        echo "<script>alert('Post updated successfully!');</script>";
        header("Location: view-post.php?id=" . $postId); // Redirect to the updated post page
        exit;
    } else {
        echo "<script>alert('Error updating post.');</script>";
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>View Post</title>
</head>
<body class="bg-gray-100">

    <!-- Navigation Bar -->
    <nav class="bg-blue-500 p-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="index.php" class="text-white font-semibold text-lg">Blog Website</a>
            <div>
                <a href="all-post.php" class="text-white hover:text-gray-200 mx-4">All Posts</a>
                <a href="add-new-post.php" class="text-white hover:text-gray-200 mx-4">Add New Post</a>
                <a href="logout.php" class="text-white hover:text-gray-200 mx-4">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Post Details -->
    <div class="flex-1 p-8 w-[60%] mx-auto mt-6">
        <h2 class="text-3xl font-semibold text-gray-800 mb-4"><?php echo htmlspecialchars($post['title']); ?></h2>

        <p class="text-sm text-gray-500 mb-4">Category: <?php echo htmlspecialchars($post['category_name']); ?></p>

        <?php if ($post['image_path']): ?>
            <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post Image" class="w-full h-64 object-cover mb-4">
        <?php endif; ?>

        <p class="text-gray-700 mb-6"><?php echo nl2br(htmlspecialchars($post['description'])); ?></p>

        <p class="text-sm text-gray-500">Posted on: <?php echo htmlspecialchars($post['created_at']); ?></p>

        <!-- Edit Button -->
        <div class="mt-4 text-center">
            <a href="#editPostModal" class="bg-yellow-500 text-white px-6 py-2 rounded-md hover:bg-yellow-600 cursor-pointer">Edit Post</a>
        </div>
    </div>

    <!-- Edit Post Form (Modal) -->
    <div id="editPostModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center hidden">
        <div class="bg-white p-6 rounded-md w-[500px]">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Edit Post</h2>

            <form action="view-post.php?id=<?php echo $postId; ?>" method="POST" enctype="multipart/form-data">
                <!-- Title Input -->
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" name="title" id="title" class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($post['title']); ?>" required>
                </div>

                <!-- Description Input -->
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" rows="4" required><?php echo htmlspecialchars($post['description']); ?></textarea>
                </div>

                <!-- Image Upload -->
                <div class="mb-4">
                    <label for="image" class="block text-sm font-medium text-gray-700">New Image (Optional)</label>
                    <input type="file" name="image" id="image" class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" accept="image/*">
                </div>

                <!-- Category Dropdown -->
                <div class="mb-4">
                    <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="category" id="category" class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Select a Category</option>
                        <?php
                        // Fetch categories
                        $categories = [];
                        $categoryResult = $conn->query("SELECT id, name FROM categories");
                        while ($row = $categoryResult->fetch_assoc()) {
                            echo '<option value="' . $row['id'] . '" ' . ($row['id'] == $post['category'] ? 'selected' : '') . '>' . $row['name'] . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-center">
                    <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition-colors duration-200">Update Post</button>
                </div>
            </form>

            <!-- Close Modal Button -->
            <div class="text-center mt-4">
                <button class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600" onclick="document.getElementById('editPostModal').classList.add('hidden')">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        // Show Edit Post Modal
        document.querySelector('.bg-yellow-500').addEventListener('click', function() {
            document.getElementById('editPostModal').classList.remove('hidden');
        });
    </script>

</body>
</html>
