<?php
// Include database connection
require 'db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query to fetch the user based on the email
    $sql = "SELECT * FROM blogs WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // If user is found, check if the password is correct
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Start the session and set session variables
            session_start();
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id'] = $user['email']; // Store user ID to manage sessions

            // Redirect to the welcome page
            header("Location: welcome.php");
            exit();
        } else {
            $message = "Invalid password.";
        }
    } else {
        $message = "No user found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
<style>
    
    </style>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-6 rounded shadow-md w-full max-w-sm">
        <h2 class="text-2xl font-bold mb-4">Login</h2>
        
        <!-- Display any error message -->
        <?php if ($message): ?>
            <p class="text-red-500"><?php echo $message; ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-4">
                <label class="block text-gray-700">Email</label>
                <input type="email" name="email" class="border border-gray-300 rounded-lg w-full p-2" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Password</label>
                <input type="password" name="password" class="border border-gray-300 rounded-lg w-full p-2" required>
            </div>
            <button type="submit" class="bg-blue-600 text-white rounded-lg px-4 py-2 hover:bg-blue-700">Login</button>
        </form>

        <p class="mt-4 text-sm">Don't have an account? <a href="register.php" class="text-blue-600">Register</a></p>
    </div>
</body>
</html>
