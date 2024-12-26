<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])|| !isset($_SESSION['user_id'])) { // Ensure session 'username' is set during login
    // If the user is not logged in, redirect to the login page
    header('Location: index.php'); // Redirect to login page
    exit;
}
   
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Welcome</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100 h-screen flex flex-col">

    <!-- Navigation Bar -->
    <nav class="bg-black h-[10%] w-full flex items-center px-4">
        <div class="flex items-center ">
            <img src="https://upload.wikimedia.org/wikipedia/commons/2/20/WordPress_logo.svg" 
                 alt="WordPress Logo" 
                 class="h-7 w-15 mr-5" 
                 style="filter: invert(1);">

            <a href="welcome.php" class="flex items-center text-white text-lg font-semibold hover:opacity-75">
                Home
            </a>
           
        </div>
        
        <div class="flex ml-auto items-center space-x-4">
            <!-- Welcome message -->
            <span class="text-white">
                Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> 
            </span>

            

            <!-- Logout Link -->
            <a href="logout.php" class="text-blue-400 hover:underline">Logout</a>
        </div>
    </nav>

    <!-- Main content layout with Sidebar -->
    <div class="flex flex-row h-full">
        <!-- Sidebar -->
        <div id="sidebar" class="bg-black w-64 text-white p-4 transform transition-all duration-300 ease-in-out">
            <a href="welcome.php" class="flex items-center text-white font-semibold hover:opacity-75 text-xl mb-4">
                Dashboard
            </a>
            
            <ul>
                <li class="mb-3 hover:bg-gray-700 p-2 rounded">
                    <a href="#" class="flex items-center" onclick="togglePostOptions()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v18h14V3H5z" />
                        </svg>
                        Post
                    </a>
                    <ul id="postOptions" class="pl-6 hidden">
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
                </li>
            </ul>
        </div>





        
    <!-- Toggle function for sidebar options -->
    <script>
        function togglePostOptions() {
            const postOptions = document.getElementById('postOptions');
            postOptions.classList.toggle('hidden');
        }

        // Function to toggle the sidebar visibility
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full'); // Hide sidebar
        }
    </script>

</body>
</html>
