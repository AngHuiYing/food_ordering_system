<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
</head>
<body>
    <h1>Your Shopping Cart</h1>
    <div id="cart"></div>
    <button id="placeOrder">Place All Orders</button>
    <button onclick="window.location.href='menu.php'">Back to Menu</button>

    <script>
        let cart = JSON.parse(localStorage.getItem('cart')) || [];

        function displayCart() {
            const cartDiv = document.getElementById('cart');
            cartDiv.innerHTML = '';

            if (cart.length === 0) {
                cartDiv.innerHTML = '<p>Your cart is empty.</p>';
                return;
            }

            cart.forEach((item, index) => {
                const cartItem = document.createElement('div');
                cartItem.innerHTML = `
                    <h3>${item.name}</h3>
                    <p>Price: RM${item.price}</p>
                    <p>Quantity: <input type="number" min="1" value="${item.quantity}" onchange="updateQuantity(${index}, this.value)"></p>
                    <p>Flavor: <input type="text" value="${item.flavor}" onchange="updateFlavor(${index}, this.value)"></p>
                    <button onclick="removeFromCart(${index})">Remove</button>
                    <hr>
                `;
                cartDiv.appendChild(cartItem);
            });
        }

        // 更新数量
        function updateQuantity(index, newQuantity) {
    if (newQuantity < 1) newQuantity = 1; // 保证数量至少为1
    cart[index].quantity = newQuantity;
    localStorage.setItem('cart', JSON.stringify(cart));
    displayCart();
}

        // 更新口味
        function updateFlavor(index, newFlavor) {
            cart[index].flavor = newFlavor;
            localStorage.setItem('cart', JSON.stringify(cart));
            displayCart();
        }

        // 删除商品
        function removeFromCart(index) {
            cart.splice(index, 1);
            localStorage.setItem('cart', JSON.stringify(cart));
            displayCart();
        }

        // 一键下单
        document.getElementById('placeOrder').addEventListener('click', function() {
    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }

    const orderData = {
        user_id: <?php echo $user_id; ?>,  // Passing the user ID from PHP session
        username: 'User Name',  // You can replace this with the actual username if you have it
        phone: 'User Phone',  // You can replace this with the actual phone if you have it
        address: 'User Address',  // You can replace this with the actual address if you have it
        items: cart.map(item => ({
            id: item.id,
            quantity: item.quantity
        })),
        flavor: cart.map(item => item.flavor)  // Optional flavor for each item
    };

    fetch('api/api.php?endpoint=order', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(orderData),
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === 'Order created successfully') {
            alert('Order placed successfully!');
            localStorage.removeItem('cart');  // Clear the cart after successful order
            window.location.href = 'menu.php';  // Redirect to the menu page
        } else {
            alert('Failed to place order: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error placing order:', error);
        alert('Error placing order');
    });
});

        displayCart(); // 初始加载购物车
    </script>
</body>
</html>
