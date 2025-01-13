<?php
session_start();
include '../db.php'; // 引入 PDO 数据库连接文件

// 检查用户是否已登录
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // 跳转到登录页面
    exit();
}

$user_id = $_SESSION['user_id'];

// 查询用户的订单及菜品数据
$query = "
    SELECT 
        o.id AS order_id, 
        o.address, 
        o.flavor, 
        o.total_price, 
        o.created_at, 
        oi.menu_id, 
        oi.quantity, 
        m.name AS menu_name, 
        m.image_url 
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN menu m ON oi.menu_id = m.id
    WHERE o.user_id = :user_id
    ORDER BY o.created_at DESC
";

try {
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
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
    <title>View Order</title>
    <!-- 引入 Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Historical Orders</h1>

        <?php if (!empty($orders)): ?>
            <?php 
            // 按订单分组
            $groupedOrders = [];
            foreach ($orders as $order) {
                $groupedOrders[$order['order_id']]['info'] = [
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
                You don't have any orders yet.
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="menu.php" class="btn btn-primary">Back to Menu Page</a>
        </div>
    </div>

    <!-- 引入 Bootstrap JS 和 Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
