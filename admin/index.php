<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('Please login first'); window.location.href = '../admin/login.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center text-primary mb-4">Welcome, Admin!</h1>

        <!-- Admin Actions -->
        <div class="text-center mb-4">
            <a href="add_food.php" class="btn btn-success">Add Food</a>
            <a href="view_order.php" class="btn btn-success">View Orders</a>
            <a href="../frontend/logout.php" class="btn btn-danger">Logout</a>
        </div>

        <h2 class="text-center mb-4">Food Menu</h2>

        <!-- Food List -->
        <div id="food-list" class="row g-4"></div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <script>
        // Fetch the food menu from API
        fetch('../api/api.php?endpoint=menu')
            .then(response => response.json())
            .then(data => {
                const foodListDiv = document.getElementById('food-list');
                if (data && data.length > 0) {
                    data.forEach(item => {
                        const foodItem = document.createElement('div');
                        foodItem.classList.add('col-md-4');  // 3 columns on medium screens
                        foodItem.innerHTML = `
                            <div class="card">
                                <img src="${item.image_url}" alt="${item.name}" class="card-img-top" style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title">${item.name}</h5>
                                    <p class="card-text">${item.description}</p>
                                    <p><strong>Price: $${item.price}</strong></p>
                                    <button class="btn btn-primary" onclick="editFood(${item.id})">Edit</button>
                                    <button class="btn btn-danger" onclick="deleteFood(${item.id})">Delete</button>
                                </div>
                            </div>
                        `;
                        foodListDiv.appendChild(foodItem);
                    });
                } else {
                    foodListDiv.innerHTML = '<p>No food items found.</p>';
                }
            })
            .catch(error => {
                console.error('Error fetching menu:', error);
                document.getElementById('food-list').innerHTML = '<p>Error fetching food list.</p>';
            });

        function editFood(foodId) {
            // Redirect to an edit page with food ID
            window.location.href = `edit_food.php?id=${foodId}`;
        }

        function deleteFood(foodId) {
            if (confirm("Are you sure you want to delete this food item?")) {
                fetch(`../api/api.php?endpoint=delete_food&id=${foodId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message === 'Food deleted successfully') {
                        alert('Food deleted successfully');
                        window.location.reload();  // Refresh the page to remove the deleted food
                    } else {
                        alert('Failed to delete food');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete food');
                });
            }
        }
    </script>
</body>
</html>
