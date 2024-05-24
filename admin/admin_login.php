<?php
// Include database connection file
include_once 'db_connection.php';

// Start session
session_start();

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Retrieve admin data from database
    $sql = "SELECT * FROM admins WHERE Username=? AND Password=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password); // Bind the username and password parameters
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Admin found, log in
        $row = $result->fetch_assoc();
        $_SESSION['admin_id'] = $row['AdminID'];
        $_SESSION['admin_username'] = $username; // Store the username in the session
        // Redirect to admin dashboard
        header("Location: a_dashboard.php");
        exit();
    } else {
        $login_error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pharmacy</title>
    <!-- Include Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f3f4f6;
        }

        .container {
            min-height: 80vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-card {
            width: 400px; /* Adjusted width */
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .login-header {
            background-color: #4b7bec;
            color: #ffffff;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            padding: 1.5rem;
            text-align: center;
        }

        .login-header h2 {
            font-size: 1.5rem;
        }

        .login-form {
            padding: 2rem;
        }

        .input-group {
            margin-bottom: 1.5rem;
        }

        .input-group label {
            font-size: 1rem;
            font-weight: 600;
            color: #4b6584;
            display: block;
            margin-bottom: 0.5rem;
        }

        .input-group input {
            width: 100%;
            padding: 0.75rem;
            border-radius: 5px;
            border: 1px solid #d1d5db;
            font-size: 1rem;
        }

        .input-group input:focus {
            outline: none;
            border-color: #4b7bec;
        }

        .login-btn {
            width: 100%;
            padding: 0.75rem;
            border-radius: 5px;
            background-color: #4b7bec;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .login-btn:hover {
            background-color: #3867d6;
        }

        .error-msg {
            color: #eb3b5a;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="login-card">
            <div class="login-header">
                <h2>CITY PHARMACY</h2>
            </div>
            <div class="login-form">
                <form action="#" method="POST" class="space-y-4">
                    <div class="input-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Enter your username" required
                            class="border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    </div>
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required
                            class="border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    </div>
                    <button type="submit" name="login" value="admin"
                        class="login-btn">Login</button>
                    <?php if(isset($login_error)) { echo '<p class="error-msg">' . $login_error . '</p>'; } ?>
                </form>
                <div class="text-center mt-4">
                    <p class="text-gray-600">or</p>
                    <a href="customer_login.php" class="text-blue-500 font-semibold hover:text-blue-700">Login as Customer</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
