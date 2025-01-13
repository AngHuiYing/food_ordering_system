<?php
session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$user_id) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center text-primary mb-4">Menu</h1>

        <div id="menu" class="row row-cols-1 row-cols-md-3 g-4"></div>

        <div class="d-flex justify-content-center mt-4">
        <button class="btn btn-outline-success" onclick="window.location.href='view_order.php'">View Order</button>
            <button class="btn btn-outline-danger" onclick="window.location.href='logout.php'">Logout</button>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <script>
        // Fetch menu data from the API
        fetch('../api/api.php?endpoint=menu')
            .then(response => response.json())
            .then(data => {
                const menuDiv = document.getElementById('menu');
                
                // Iterate through the fetched menu items and display them
                data.forEach(item => {
                    const menuItem = document.createElement('div');
                    menuItem.classList.add('col');
                    menuItem.innerHTML = `
                        <div class="card h-100">
                            <img src="${item.image_url}" class="card-img-top" alt="${item.name}">
                            <div class="card-body">
                                <h5 class="card-title">${item.name}</h5>
                                <p class="card-text">${item.description}</p>
                                <p class="card-text"><strong>Price:</strong> RM${item.price}</p>
                                <button class="btn btn-primary" onclick="addToOrder(${item.id}, '${item.name}', ${item.price})">Add to Order</button>
                            </div>
                        </div>
                    `;
                    menuDiv.appendChild(menuItem);
                });
            })
            .catch(error => {
                alert('Error fetching menu items');
            });

        function addToOrder(itemId, itemName, itemPrice) {
            // Store the menu item in localStorage and redirect to the order page
            localStorage.setItem('orderItem', JSON.stringify({id: itemId, name: itemName, price: itemPrice}));
            window.location.href = 'order.php';  // Redirect to order page
        }
    </script>
</body>
</html>
