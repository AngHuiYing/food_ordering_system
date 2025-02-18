<?php
include_once('../db.php');

// 查询所有用户的订单及菜品数据
$query = "
    SELECT 
        o.id AS order_id, 
        o.user_id, 
        o.address, 
        o.flavor, 
        o.total_price, 
        o.created_at, 
        oi.menu_id, 
        oi.quantity, 
        m.name AS menu_name, 
        m.image_url, 
        u.username AS user_name
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN menu m ON oi.menu_id = m.id
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("查询失败: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Orders</title>
    <!-- 引入 Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">View All Orders</h1>

        <?php if (!empty($orders)): ?>
            <?php 
            // 按订单分组
            $groupedOrders = [];
            foreach ($orders as $order) {
                $groupedOrders[$order['order_id']]['info'] = [
                    'user_name' => $order['user_name'],
                    'address' => $order['address'],
                    'flavor' => $order['flavor'],
                    'total_price' => $order['total_price'],
                    'created_at' => $order['created_at'],
                ];
                $groupedOrders[$order['order_id']]['items'][] = [
                    'menu_name' => $order['menu_name'],
                    'image_url' => $order['image_url'],
                    'quantity' => $order['quantity'],
                ];
            }
            ?>

            <?php foreach ($groupedOrders as $orderId => $orderData): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h2 class="h5 mb-0">Order ID: <?php echo htmlspecialchars($orderId); ?></h2>
                        <p><strong>User:</strong> <?php echo htmlspecialchars($orderData['info']['user_name']); ?></p>
                    </div>
                    <div class="card-body">
                        <p><strong>Address/Table Number:</strong> <?php echo htmlspecialchars($orderData['info']['address']); ?></p>
                        <p><strong>Flavor:</strong> <?php echo htmlspecialchars($orderData['info']['flavor']); ?></p>
                        <p><strong>Total Price:</strong> RM <?php echo number_format($orderData['info']['total_price'], 2); ?></p>
                        <p><strong>Created At:</strong> <?php echo htmlspecialchars($orderData['info']['created_at']); ?></p>
                        
                        <h3 class="h6 mt-4">Menu Details:</h3>
                        <table class="table table-bordered mt-2">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Image</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderData['items'] as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['menu_name']); ?></td>
                                        <td>
                                            <?php if (!empty($item['image_url'])): ?>
                                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['menu_name']); ?>" width="100" class="img-thumbnail">
                                            <?php else: ?>
                                                No Image
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info text-center">
                No orders available.
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
    </div>

    <!-- 引入 Bootstrap JS 和 Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
