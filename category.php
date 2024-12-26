<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'blogs');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle form submission to add a new category
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $slug = isset($_POST['slug']) ? $_POST['slug'] : '';
    $description = isset($_POST['description']) ? $_POST['description'] : '';

    // Check if fields are not empty
    if (!empty($name) && !empty($slug)) {
        // Check if the category already exists
        $result = $conn->query("SELECT * FROM categories WHERE slug = '$slug'");

        if ($result->num_rows > 0) {
            echo "<script>alert('Category with this slug already exists!');</script>";
        } else {
            // Prepare and execute the SQL statement to insert the category
            $stmt = $conn->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $slug, $description);

            if ($stmt->execute()) {
                echo "<script>alert('Category added successfully!');</script>";
                // Redirect to avoid form resubmission on page refresh
                header("Location: category.php");
                exit();
            } else {
                echo "<script>alert('Failed to add category: " . $stmt->error . "');</script>";
            }
            $stmt->close();
        }
    } else {
        echo "<script>alert('Please fill all required fields.');</script>";
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']); // Ensure the ID is an integer
    
    // Delete the category from the database
    $deleteStmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $deleteStmt->bind_param("i", $deleteId);

    if ($deleteStmt->execute()) {
        echo "<script>alert('Category deleted successfully!');</script>";
        // Redirect to avoid duplicate deletion on refresh
        header("Location: category.php");
        exit();
    } else {
        echo "<script>alert('Error deleting category: " . $deleteStmt->error . "');</script>";
    }

    $deleteStmt->close();
}

// Fetch all categories
$categories = $conn->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Category Management</title>
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
        <h1 class="text-2xl font-bold mb-6">Category</h1>

        <!-- Category Form -->
        <div class="bg-white p-6 rounded shadow-md mb-6">
            <h2 class="text-xl font-semibold mb-4">Add New Category</h2>
            <form action="category.php" method="POST">
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" id="name" class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div class="mb-4">
                    <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                    <input type="text" name="slug" id="slug" class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="4" class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
       
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition-colors duration-200">
                    Add Category
                </button>
            </form>
        </div>

        <!-- Category Table -->
        <div class="bg-white p-6 rounded shadow-md">
            <h2 class="text-xl font-semibold mb-4">All Categories</h2>
            <table class="w-full table-auto border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-4 py-2">Name</th>
                        <th class="border border-gray-300 px-4 py-2">Slug</th>
                        <th class="border border-gray-300 px-4 py-2">Description</th>
                        <th class="border border-gray-300 px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $categories->fetch_assoc()): ?>
                        <tr>
                            <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($row['slug']); ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($row['description']); ?></td>
                            <td class="border border-gray-300 px-4 py-2">
                                <!-- View button that triggers Modal -->
                                <td class="border border-gray-300 px-4 py-2">
    <a href="category-view.php?id=<?php echo $row['id']; ?>" 
       class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition-colors duration-200">
        View
    </a>
    <a href="edit.php?id=<?php echo $row['id']; ?>" 
       class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600 transition-colors duration-200">
        Edit
    </a>
    <a href="category.php?delete_id=<?php echo $row['id']; ?>" 
       class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition-colors duration-200"
       onclick="return confirm('Are you sure you want to delete this category?');">
        Delete
    </a>
</td>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for Viewing Category Details -->
    <div id="categoryModal" class="fixed inset-0 bg-black bg-opacity-50  justify-center items-center hidden">
        <div class="bg-white p-6 rounded-md max-w-lg w-full">
            <h2 class="text-2xl font-semibold mb-4">Category Details</h2>
            <div id="modalContent"></div>
            <button onclick="closeModal()" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Close</button>
        </div>
    </div>

    <script>
        function openModal(categoryId) {
            // Fetch category data via AJAX (this could also be a separate page but for simplicity, we'll use inline)
            fetch(`get-category.php?id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    // Show the modal and populate with category data
                    const modalContent = document.getElementById('modalContent');
                    modalContent.innerHTML = `
                        <h3 class="font-semibold text-lg">Name: ${data.name}</h3>
                        <p class="text-gray-600"><strong>Slug:</strong> ${data.slug}</p>
                        <p class="text-gray-600"><strong>Description:</strong> ${data.description}</p>
                    `;
                    document.getElementById('categoryModal').classList.remove('hidden');
                });
        }

        function closeModal() {
            document.getElementById('categoryModal').classList.add('hidden');
        }
    </script>

</body>
</html>
