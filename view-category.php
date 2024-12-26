<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'blogs');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch category details based on the ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $category = $result->fetch_assoc();
    } else {
        echo "<script>alert('Category not found!');</script>";
        header("Location: category.php");
        exit();
    }

    $stmt->close();
} else {
    header("Location: category.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Category Details</title>
</head>
<body class="bg-gray-100 p-8">

    <!-- Navbar or Sidebar can be included here if needed -->
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-3xl font-semibold mb-4 text-center">Category Details</h1>

        <div class="mb-4">
            <h2 class="text-2xl font-semibold text-gray-800">Name:</h2>
            <p class="text-lg text-gray-600"><?php echo htmlspecialchars($category['name']); ?></p>
        </div>

        <div class="mb-4">
            <h2 class="text-2xl font-semibold text-gray-800">Slug:</h2>
            <p class="text-lg text-gray-600"><?php echo htmlspecialchars($category['slug']); ?></p>
        </div>

        <div class="mb-4">
            <h2 class="text-2xl font-semibold text-gray-800">Description:</h2>
            <p class="text-lg text-gray-600"><?php echo nl2br(htmlspecialchars($category['description'])); ?></p>
        </div>

        <div class="mt-6 text-center">
            <a href="category.php" 
               class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition-colors duration-200">
               Back to Categories
            </a>
        </div>
    </div>

</body>
</html>
