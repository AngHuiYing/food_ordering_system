<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">User Login</h1>
        <form id="loginForm" class="p-4 border rounded shadow-sm">
            <div class="mb-3">
                <label for="username" class="form-label">Username:</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <p id="message" class="mt-3 text-center"></p>
    </div>

    <!-- Bootstrap JS & Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            fetch('../api/api.php?endpoint=login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username: username, password: password })
            })
            .then(response => response.json())
            .then(data => {
                const message = document.getElementById('message');
                if (data.message) {
                    message.textContent = data.message;

                    // If login successful, redirect to another page (e.g., menu.php)
                    if (data.user_id) {
                        window.location.href = 'menu.php';
                    }
                } else {
                    message.textContent = 'Login failed. Please check your credentials.';
                    message.classList.add('text-danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('message').textContent = 'Failed to login.';
                document.getElementById('message').classList.add('text-danger');
            });
        });
    </script>
</body>
</html>
