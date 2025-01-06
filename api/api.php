<?php

// Database connection
$host = "localhost";
$dbname = "food_ordering";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Routes
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['endpoint'] === 'menu') {
    getMenu($pdo);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['endpoint'] === 'order') {
    createOrder($pdo);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['endpoint'] === 'register') {
    registerUser($pdo);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['endpoint'] === 'admin_register') {
    adminRegister($pdo);  // Handle admin registration
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['endpoint'] === 'login') {
    loginUser($pdo);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['endpoint'] === 'admin_login') {
    adminLogin($pdo);  // Handle admin login
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['endpoint'] === 'add_food') {
    addFood($pdo);  // Handle adding food for admin
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['endpoint'] === 'orders') {
    getUserOrders($pdo);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['endpoint'] === 'add_to_cart') {
    addToCart($pdo);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['endpoint'] === 'view_cart') {
    viewCart($pdo);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['endpoint'] === 'update_cart') {
    updateCart($pdo);
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $_GET['endpoint'] === 'delete_food') {
    deleteFood($pdo);
} else {
    http_response_code(404);
    echo json_encode(["message" => "Endpoint not found"]);
}// Add this to your routes in `api.php`
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['endpoint'] === 'edit_food') {
    editFood($pdo);
}

// Modify the `getMenu` function
function getMenu($pdo) {
    $stmt = $pdo->query("SELECT id, name, price, description, image_url FROM menu");
    $menu = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($menu);
}

function createOrder($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);

    // 验证输入
    if (!isset($input['user_id']) || !isset($input['username']) || !isset($input['phone']) || !isset($input['address']) || !isset($input['items'])) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
        return;
    }

    $userId = $input['user_id'];
    $username = $input['username'];
    $phone = $input['phone'];
    $address = $input['address'];
    $items = $input['items']; // Array of item IDs and quantities
    $flavor = isset($input['flavor']) ? $input['flavor'] : null;  // Optional flavor
    $totalPrice = 0;

    try {
        $pdo->beginTransaction();

        // 插入订单
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, username, phone, address, flavor, total_price, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
        $stmt->execute([$userId, $username, $phone, $address, $flavor]);
        $orderId = $pdo->lastInsertId();  // 获取刚插入的订单ID

        // 插入订单项并计算总价
        foreach ($items as $item) {
            $itemId = $item['id'];
            $quantity = $item['quantity'];      
            // 获取菜单项的价格
            $stmt = $pdo->prepare("SELECT price FROM menu WHERE id = ?");
            $stmt->execute([$itemId]);
            $menuItem = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$menuItem) {
                throw new Exception("Menu item not found: " . $itemId);
            }

            // 计算每个订单项的价格
            $itemPrice = $menuItem['price'] * $quantity;
            $totalPrice += $itemPrice;

            // 插入订单项
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$orderId, $itemId, $quantity, $itemPrice]);
        }

        // 更新订单总价
        $stmt = $pdo->prepare("UPDATE orders SET total_price = ? WHERE id = ?");
        $stmt->execute([$totalPrice, $orderId]);

        // 提交事务
        $pdo->commit();

        echo json_encode(["message" => "Order created successfully", "order_id" => $orderId]);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(["message" => "Failed to create order", "error" => $e->getMessage()]);
    }
}

function registerUser($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['username']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
        return;
    }

    $username = $input['username'];
    $password = password_hash($input['password'], PASSWORD_BCRYPT);

    try {
        // 检查用户名是否已经存在
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $userCount = $stmt->fetchColumn();

        if ($userCount > 0) {
            http_response_code(400);
            echo json_encode(["message" => "Username already exists"]);
            return;
        }

        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $password]);
        echo json_encode(["message" => "User registered successfully"]);
    } catch (Exception $e) {
        // Print more detailed error message for debugging
        http_response_code(500);
        echo json_encode([
            "message" => "Failed to register user",
            "error" => $e->getMessage(),
            "error_code" => $e->getCode(),
            "sql" => $stmt->queryString // 也可以输出执行的SQL查询语句
        ]);
    }
}


function loginUser($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['username']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
        return;
    }

    $username = $input['username'];
    $password = $input['password'];

    try {
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // 登录成功后，存储 user_id 到 session
            session_start();
            $_SESSION['user_id'] = $user['id']; // 保存用户 ID

            echo json_encode(["message" => "Login successful", "user_id" => $user['id']]);
        } else {
            http_response_code(401);
            echo json_encode(["message" => "Invalid username or password"]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to login", "error" => $e->getMessage()]);
    }
}

function getUserOrders($pdo) {
    if (!isset($_GET['user_id'])) {
        http_response_code(400);
        echo json_encode(["message" => "User ID is required"]);
        return;
    }

    $userId = $_GET['user_id'];

    try {
        $stmt = $pdo->prepare("SELECT id, total_price, created_at FROM orders WHERE user_id = ?");
        $stmt->execute([$userId]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($orders);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to retrieve orders", "error" => $e->getMessage()]);
    }
}

// Admin login
function adminLogin($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['username']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
        return;
    }

    $username = $input['username'];
    $password = $input['password'];

    try {
        $stmt = $pdo->prepare("SELECT id, password FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            echo json_encode(["message" => "Login successful", "admin_id" => $admin['id']]);
        } else {
            http_response_code(401);
            echo json_encode(["message" => "Invalid username or password"]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to login", "error" => $e->getMessage()]);
    }
}

// Admin register
function adminRegister($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['username']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
        return;
    }

    $username = $input['username'];
    $password = password_hash($input['password'], PASSWORD_BCRYPT);

    try {
        $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $password]);
        echo json_encode(["message" => "Admin registered successfully"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to register admin", "error" => $e->getMessage()]);
    }
}

// Add food
function addFood($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['name']) || !isset($input['price']) || !isset($input['description']) || !isset($input['image_url'])) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
        return;
    }

    $name = $input['name'];
    $price = $input['price'];
    $description = $input['description'];
    $imageUrl = $input['image_url'];

    try {
        $stmt = $pdo->prepare("INSERT INTO menu (name, price, description, image_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $price, $description, $imageUrl]);
        echo json_encode(["message" => "Food added successfully"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to add food", "error" => $e->getMessage()]);
    }
}

function addToCart($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['user_id']) || !isset($input['menu_id']) || !isset($input['quantity'])) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
        return;
    }

    $userId = $input['user_id'];
    $menuId = $input['menu_id'];
    $quantity = $input['quantity'];

    try {
        // 使用 INSERT ON DUPLICATE KEY UPDATE 简化代码（假设表中有 UNIQUE 索引）
        $stmt = $pdo->prepare("
            INSERT INTO cart (user_id, menu_id, quantity)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
        ");
        $stmt->execute([$userId, $menuId, $quantity]);

        echo json_encode(["message" => "Item added to cart successfully"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to add item to cart", "error" => $e->getMessage()]);
    }
}

function viewCart($pdo) {
    if (!isset($_GET['user_id'])) {
        http_response_code(400);
        echo json_encode(["message" => "User ID is required"]);
        return;
    }

    $userId = $_GET['user_id'];

    try {
        $stmt = $pdo->prepare("
            SELECT c.id, c.quantity, m.name, m.price, (c.quantity * m.price) AS total_price
            FROM cart c
            JOIN menu m ON c.menu_id = m.id
            WHERE c.user_id = ?
        ");
        $stmt->execute([$userId]);
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($cartItems);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to retrieve cart", "error" => $e->getMessage()]);
    }
}

function updateCart($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['cart_id']) || !isset($input['quantity'])) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
        return;
    }

    $cartId = $input['cart_id'];
    $quantity = $input['quantity'];

    try {
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$quantity, $cartId]);

        echo json_encode(["message" => "Cart updated successfully"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to update cart", "error" => $e->getMessage()]);
    }
}

function removeFromCart($pdo) {
    parse_str(file_get_contents('php://input'), $input);

    if (!isset($input['cart_id'])) {
        http_response_code(400);
        echo json_encode(["message" => "Cart ID is required"]);
        return;
    }

    $cartId = $input['cart_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ?");
        $stmt->execute([$cartId]);

        echo json_encode(["message" => "Item removed from cart successfully"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to remove item from cart", "error" => $e->getMessage()]);
    }


}

// Add the editFood function to `api.php`
function editFood($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['id']) || !isset($input['name']) || !isset($input['price']) || !isset($input['description']) || !isset($input['image_url'])) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
        return;
    }

    $id = $input['id'];
    $name = $input['name'];
    $price = $input['price'];
    $description = $input['description'];
    $imageUrl = $input['image_url'];

    try {
        $stmt = $pdo->prepare("UPDATE menu SET name = ?, price = ?, description = ?, image_url = ? WHERE id = ?");
        $stmt->execute([$name, $price, $description, $imageUrl, $id]);

        echo json_encode(["message" => "Food updated successfully"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to update food", "error" => $e->getMessage()]);
    }
}

// 删除菜品
function deleteFood($pdo) {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "Food ID is required"]);
        return;
    }

    $foodId = $_GET['id'];

    try {
        // 删除菜单中的指定菜品
        $stmt = $pdo->prepare("DELETE FROM menu WHERE id = ?");
        $stmt->execute([$foodId]);

        echo json_encode(["message" => "Food deleted successfully"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to delete food", "error" => $e->getMessage()]);
    }
}

?>
