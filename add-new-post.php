<?php include 'welcome.php'; ?>

<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username'])|| !isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
// echo "Hello, " . htmlspecialchars($_SESSION['username']);


// Database connection
$conn = new mysqli('localhost', 'root', '', 'blogs');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$postId = null;  // Initialize postId variable
$showToast = false;  // Flag to determine if toast should be shown

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $category = (int)$_POST['category'];
    $createdAt = date('Y-m-d H:i:s');
    $imagePath = null;
    $username = '';
    $asd= $_SESSION['user_id'];

// Check if the session username is 'admin' or 'Admin'
if (isset($_SESSION['username']) && ($_SESSION['username'] == "admin" || $_SESSION['username'] == "Admin")) {
    $username = "admin"; // Set to 'admin' if the username matches
} else {
    $username = "user"; // Otherwise, set to 'user'
}

    // Ensure uploads directory exists
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $imageName = basename($_FILES['image']['name']);
        $imagePath = $targetDir . $imageName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $imagePath = null;
            echo "<script>setToast('Failed to upload image. Please try again.', 'error');</script>";
        }
    }

    $stmt = $conn->prepare("INSERT INTO posts (title, description, image_path, category, created_at,role,user_email) VALUES (?, ?, ?, ?, ?,?,?)");
    $stmt->bind_param('sssiiss', $title, $description, $imagePath, $category, $createdAt,$username,$asd);

    if ($stmt->execute()) {
        $postId = $conn->insert_id;
        $showToast = true; // Set flag to show toast on next page load
    } else {
        echo "<script>setToast('Error adding post: " . $stmt->error . "', 'error');</script>";
    }

    $stmt->close();
}

// Fetch categories
$categories = [];
$result = $conn->query("SELECT id, name FROM categories");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Add New Post</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100 h-screen flex flex-col">

    <!-- Toast Notification (initially hidden) -->
    <div id="toast" class="hidden fixed bottom-5 left-5 p-4 max-w-xs w-full rounded shadow-lg text-white">
        <p id="toastMessage"></p>
        <a id="viewLink" href="#" class="text-blue-500 ml-2 hidden">View</a>
    </div>

    <!-- Main content layout -->
    <div class="flex-1 p-8 w-[60%] ml-80">
        <h2 class="text-2xl font-semibold text-center text-gray-800 mb-6">Add New Post</h2>

        <form action="add-new-post.php" method="POST" enctype="multipart/form-data">
            <!-- Title Input -->
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" id="title" class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <!-- Description Input -->
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" rows="4" required></textarea>
            </div>

            <!-- Image Upload -->
            <div class="mb-4">
                <label for="image" class="block text-sm font-medium text-gray-700">Image</label>
                <input type="file" name="image" id="image" class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" accept="image/*">
            </div>

            <!-- Category Dropdown -->
            <div class="mb-4">
                <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                <select name="category" id="category" class="mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Select a Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['id']); ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-center">
                <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition-colors duration-200">Save Post</button>
            </div>
        </form>
    </div>

    <script>
        // Show toast with message and optional "View" link
        function setToast(message, type, postId = null) {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            const viewLink = document.getElementById('viewLink');

            // Set message and style based on type
            toastMessage.textContent = message;
            toast.className = `fixed bottom-5 left-5 p-4 max-w-xs w-full rounded shadow-lg text-white ${
                type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' :
                'bg-yellow-500'
            }`;

            toast.classList.remove('hidden'); // Show the toast

            // Show "View" button if postId is provided
            if (type === 'success' && postId) {
                viewLink.classList.remove('hidden');
                viewLink.href = `view-post.php?id=${postId}`;
            } else {
                viewLink.classList.add('hidden'); // Hide the link if not a success
            }

            // Auto-hide after 3 seconds
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        }

        // Display the toast if $showToast is true (set in PHP)
        <?php if ($showToast && $postId): ?>
            setToast('Post added successfully!', 'success', <?php echo $postId; ?>);
        <?php endif; ?>
    </script>

</body>
</html>
