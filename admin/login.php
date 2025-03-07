
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center text-primary mb-4">Admin Login</h1>

        <form id="adminLoginForm" class="p-4 border rounded shadow-sm bg-white">
            <div class="mb-3">
                <label for="adminUsername" class="form-label">Username:</label>
                <input type="text" id="adminUsername" name="adminUsername" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="adminPassword" class="form-label">Password:</label>
                <input type="password" id="adminPassword" name="adminPassword" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <div class="text-center mt-4">
            <a href="register.php" class="btn btn-link">Don't have an account? Register here</a>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <script>
        document.getElementById('adminLoginForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const username = document.getElementById('adminUsername').value;
            const password = document.getElementById('adminPassword').value;

            fetch('../api/api.php?endpoint=admin_login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username: username, password: password })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message === 'Login successful') {
                    window.location.href = 'index.php';  // Redirect to admin dashboard
                } else {
                    alert(data.message);
                }
            });
        });
    </script>
</body>
</html>
