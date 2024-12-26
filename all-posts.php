<?php include 'welcome.php'; ?>

<?php
// Database Connection
$conn = new mysqli('localhost', 'root', '', 'blogs');

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize Variables
$searchQuery = '';
if (isset($_GET['search'])) {
    $searchQuery = $conn->real_escape_string($_GET['search']);
}

// Pagination Setup
$postsPerPage = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $postsPerPage;

// Build Search SQL
$searchSQL = "";
if (!empty($searchQuery)) {
    $searchSQL = "WHERE posts.title LIKE '%$searchQuery%' OR posts.description LIKE '%$searchQuery%'";
}

// Total Posts Count
$totalPostsResult = $conn->query("SELECT COUNT(*) as total FROM posts $searchSQL");
$totalPosts = $totalPostsResult ? $totalPostsResult->fetch_assoc()['total'] : 0;
$totalPages = ceil($totalPosts / $postsPerPage);

// Fetch Posts
// Define the email variable (you can change this dynamically based on the logged-in user or another source)
$userEmail = $_SESSION['user_id'];  

$postsQuery = "SELECT posts.*, categories.name AS category_name 
               FROM posts 
               LEFT JOIN categories ON posts.category = categories.id 
               $searchSQL 
               WHERE posts.user_email = ? 
               ORDER BY posts.created_at DESC LIMIT ?, ?";

// Prepare and bind parameters
$stmt = $conn->prepare($postsQuery);
$stmt->bind_param('sii', $userEmail, $offset, $postsPerPage);  // 's' for string, 'i' for integer

$stmt->execute();
$postsResult = $stmt->get_result();


// Handle Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_selected'])) {
    if (!empty($_POST['post_ids'])) {
        $postIds = $_POST['post_ids'];
        $placeholders = implode(',', array_fill(0, count($postIds), '?'));

        $stmt = $conn->prepare("DELETE FROM posts WHERE id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($postIds)), ...$postIds);

        if ($stmt->execute()) {
            echo "<p class='text-green-600'>Selected posts deleted successfully!</p>";
        } else {
            echo "<p class='text-red-600'>Error deleting posts: " . $stmt->error . "</p>";
        }

        $stmt->close();
    } else {
        echo "<p class='text-red-600'>No posts selected for deletion.</p>";
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
    <title>All Posts</title>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex-1 p-6">
        <h1 class="text-3xl font-bold mb-6 text-center">All Posts</h1>

        <!-- Search Bar -->
        <form action="all-post.php" method="GET" class="mb-6 w-[80%] mx-auto flex">
            <input
                type="text"
                name="search"
                placeholder="Search posts by title or description"
                class="flex-1 p-3 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                value="<?php echo htmlspecialchars($searchQuery); ?>">
            <button
                type="submit"
                class="bg-blue-500 text-white px-4 py-2 rounded-r-md hover:bg-blue-600 transition-colors duration-200">
                Search
            </button>
        </form>

        <div class="bg-white shadow-md rounded-lg p-6 w-[80%] mx-auto">
            <form method="POST" action="">
                <table class="w-full table-auto border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="border border-gray-300 p-2">
                                <input type="checkbox" id="select_all" onclick="toggleSelectAll(this)">
                            </th>
                            <th class="border border-gray-300 p-2">Image</th>
                            <th class="border border-gray-300 p-2">Title</th>
                            <th class="border border-gray-300 p-2">Role</th>
                            <th class="border border-gray-300 p-2">Category</th>
                            <th class="border border-gray-300 p-2">Date</th>
                            <th class="border border-gray-300 p-2">Actions</th>
                          
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($postsResult->num_rows > 0): ?>
                            <?php while ($row = $postsResult->fetch_assoc()): ?>
                                <tr>
                                    <td class="border border-gray-300 p-2 text-center">
                                        <input type="checkbox" name="post_ids[]" value="<?php echo $row['id']; ?>">
                                    </td>
                                    <td class="border border-gray-300 p-2 text-center">
                                        <?php if ($row['image_path']): ?>
                                            <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Post Image" class="h-16 w-16 object-cover mx-auto rounded-md">
                                        <?php else: ?>
                                            <span>No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($row['role']); ?></td>
                                    

                                    <td class="border border-gray-300 p-2">
                                        <?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?>
                                    </td>
                                    <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($row['created_at']); ?></td>
                                    <td class="border border-gray-300 p-2">
                                        <a href="view-post.php?id=<?php echo $row['id']; ?>" class="text-green-500 hover:text-green-700">View</a> |
                                        <a href="edit-post.php?id=<?php echo $row['id']; ?>" class="text-blue-500 hover:text-blue-700">Edit</a> | 
                                        <a href="delete-post.php?id=<?php echo $row['id']; ?>" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center p-4">No posts found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="mt-4 flex justify-between items-center">
                    <button
                        type="submit"
                        name="delete_selected"
                        class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition-colors duration-200">
                        Delete Selected
                    </button>

                    <nav>
                        <ul class="flex space-x-2">
                            <li><a href="?page=1" class="text-blue-500">First</a></li>
                            <li><a href="?page=<?php echo max(1, $page - 1); ?>" class="text-blue-500">Previous</a></li>
                            <li><a href="?page=<?php echo min($totalPages, $page + 1); ?>" class="text-blue-500">Next</a></li>
                            <li><a href="?page=<?php echo $totalPages; ?>" class="text-blue-500">Last</a></li>
                        </ul>
                    </nav>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleSelectAll(source) {
            document.querySelectorAll('input[name="post_ids[]"]').forEach(checkbox => checkbox.checked = source.checked);
        }
    </script>

</body>
</html>
