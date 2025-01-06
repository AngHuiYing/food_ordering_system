<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id']; // 获取登录的 user_id
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center text-primary mb-4">Place Your Order</h1>
        
        <form id="orderForm">
            <div class="mb-3">
                <label for="user_id" class="form-label">User ID:</label>
                <input type="text" id="user_id" name="user_id" value="<?php echo $user_id; ?>" class="form-control" readonly>
            </div>

            <div class="mb-3">
                <label for="username" class="form-label">Your Name:</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number:</label>
                <input type="text" id="phone" name="phone" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Address:</label>
                <textarea id="address" name="address" class="form-control" required></textarea>
                <p>If you are ordering in store, please fill in the words "table" and write the table number at behind.</p>
            </div>

            <!-- Order Item Section -->
            <h3 class="mt-4">Order Item</h3>
            <div class="mb-3">
                <label for="item_name" class="form-label">Item Name:</label>
                <input type="text" id="item_name" name="item_name" class="form-control" readonly>
            </div>

            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity:</label>
                <input type="number" id="quantity" name="quantity" class="form-control" min="1" value="1" required>
            </div>

            <div class="mb-3">
                <label for="flavor" class="form-label">Flavor Preferences:</label>
                <input type="text" id="flavor" name="flavor" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Place Order</button>
        </form>

        <div class="d-flex justify-content-center mt-4">
            <button class="btn btn-outline-secondary" onclick="window.location.href='menu.php'">Back to Menu</button>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <script>
        // 从localStorage获取菜品信息
        const orderItem = JSON.parse(localStorage.getItem('orderItem'));
        console.log(orderItem);  // 调试输出

        if (orderItem) {
            document.getElementById('item_name').value = orderItem.name;
        }

        document.getElementById('orderForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const userId = document.getElementById('user_id').value;
            const username = document.getElementById('username').value;
            const phone = document.getElementById('phone').value;
            const address = document.getElementById('address').value;
            const quantity = document.getElementById('quantity').value;
            const flavor = document.getElementById('flavor').value;

            // 创建订单数据
            const orderData = {
                user_id: userId,
                username: username,
                phone: phone,
                address: address,
                items: [{
                    id: orderItem.id,
                    name: orderItem.name,
                    quantity: quantity,
                    price: orderItem.price
                }],
                flavor: flavor
            };

            console.log(orderData);  // 调试输出请求数据

            // 提交订单数据
            fetch('../api/api.php?endpoint=order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(orderData)
            })
            .then(response => response.json())
            .then(data => alert(data.message))
            .catch(error => alert('Error placing order'));
        });
    </script>
</body>
</html>
