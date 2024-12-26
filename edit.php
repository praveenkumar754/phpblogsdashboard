<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'blogs');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission to update category
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $slug = $_POST['slug'];
    $description = $_POST['description'];

    // Check if fields are not empty
    if (!empty($name) && !empty($slug)) {
        // Update the category in the database
        $stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $slug, $description, $id);

        if ($stmt->execute()) {
            echo "<script>alert('Category updated successfully!'); window.location='category.php';</script>";
            exit();
        } else {
            echo "<script>alert('Failed to update category: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Please fill all required fields.');</script>";
    }
}

// Fetch category data for the edit form
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $category = $result->fetch_assoc();
    } else {
        echo "<script>alert('Category not found'); window.location='category.php';</script>";
        exit();
    }
    $stmt->close();
} else {
    echo "<script>alert('No category ID provided'); window.location='category.php';</script>";
    exit();
}

$conn->close();
?>

<!-- HTML Form for Editing Category -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Edit Category</title>
</head>
<body class="bg-gray-100 flex h-screen">

    <!-- Sidebar -->
    <div class="bg-black w-64 text-white p-4">
        <a href="welcome.php" class="flex items-center text-white font-semibold hover:opacity-75 text-xl mb-4">
            Dashboard
        </a>
        <ul>
            <li class="mb-3 hover:bg-gray-700 p-2 rounded">
                <a href="all-posts.php" class="flex items-center hover:text-blue-500">All Posts</a>
            </li>
            <li class="mb-3 hover:bg-gray-700 p-2 rounded">
                <a href="add-new-post.php" class="flex items-center hover:text-blue-500">Add New Post</a>
            </li>
            <li class="mb-3 hover:bg-gray-700 p-2 rounded">
                <a href="category.php" class="flex items-center hover:text-blue-500">Category</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-grow p-8">
        <h1 class="text-2xl font-bold mb-6">Edit Category</h1>

        <!-- Category Edit Form -->
        <div class="bg-white p-6 rounded shadow-md">
            <h2 class="text-xl font-semibold mb-4">Edit Category</h2>
            <form action="edit.php" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($category['id']); ?>">
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($category['name']); ?>"
                           class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div class="mb-4">
                    <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                    <input type="text" name="slug" id="slug" value="<?php echo htmlspecialchars($category['slug']); ?>"
                           class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="4"
                              class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($category['description']); ?></textarea>
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition-colors duration-200">
                    Update Category
                </button>
            </form>
        </div>
    </div>

</body>
</html>
