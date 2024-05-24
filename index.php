<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pharmacy</title>
    <!-- Include Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Custom Styles */
        /* You can add any additional custom styles here */
    </style>
</head>

<body class="bg-gray-100">
    <!-- Login Section -->
    <section class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md w-full sm:w-96">
            <h2 class="text-2xl font-semibold mb-4">Login</h2>
            <!-- Login Form -->
            <div class="mb-4">
                <p class="block text-gray-700 font-semibold mb-2">Login as:</p>
                <button onclick="loginAsCustomer()" class="w-full bg-blue-500 text-white font-semibold py-2 px-4 rounded-lg mb-2 hover:bg-blue-600 transition duration-300">Customer</button>
                <button onclick="loginAsAdmin()" class="w-full bg-blue-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-600 transition duration-300">Admin</button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="absolute bottom-0 w-full bg-gray-800 text-white py-4">
        <div class="container mx-auto text-center">
            <p>&copy; 2024 Pharmacy. All rights reserved.</p>
        </div>
    </footer>

    <!-- JavaScript for Admin and Customer Login -->
    <script>
        function loginAsAdmin() {
            // Redirect to admin login page
            window.location.href = "admin/admin_login.php";
        }

        function loginAsCustomer() {
            // Redirect to customer login page
            window.location.href = "login.php";
        }
    </script>

</body>

</html>
