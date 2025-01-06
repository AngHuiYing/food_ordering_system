<?php
// 引入数据库连接文件
include_once '../db.php'; // 请根据实际路径修改

// Fetch the food item to edit
if (isset($_GET['id'])) {
    $foodId = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM menu WHERE id = ?");
    $stmt->execute([$foodId]);
    $food = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$food) {
        echo json_encode(["message" => "Food item not found"]);
        exit;
    }
} else {
    echo json_encode(["message" => "Food ID is required"]);
    exit;
}

// Handle form submission to update the food item
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = $_POST;

    $name = isset($input['name']) ? $input['name'] : null;
    $price = isset($input['price']) ? $input['price'] : null;
    $description = isset($input['description']) ? $input['description'] : null;
    $imageUrl = isset($input['image_url']) ? $input['image_url'] : null;

    if (!$name || !$price || !$description || !$imageUrl) {
        echo json_encode(["message" => "Invalid input"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE menu SET name = ?, price = ?, description = ?, image_url = ? WHERE id = ?");
        $stmt->execute([$name, $price, $description, $imageUrl, $foodId]);

        echo json_encode(["message" => "Food updated successfully"]);
    } catch (Exception $e) {
        echo json_encode(["message" => "Failed to update food", "error" => $e->getMessage()]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Food Item</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center text-primary mb-4">Edit Food Item</h1>

        <!-- Edit Food Form -->
        <form method="POST" action="edit_food.php?id=<?php echo $foodId; ?>" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="name" class="form-label">Food Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($food['name']); ?>" required>
                <div class="invalid-feedback">Please enter the food name.</div>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($food['price']); ?>" required>
                <div class="invalid-feedback">Please enter a valid price.</div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($food['description']); ?></textarea>
                <div class="invalid-feedback">Please provide a description.</div>
            </div>

            <div class="mb-3">
                <label for="image_url" class="form-label">Image URL</label>
                <input type="text" class="form-control" id="image_url" name="image_url" value="<?php echo htmlspecialchars($food['image_url']); ?>" required>
                <div class="invalid-feedback">Please provide a valid image URL.</div>
            </div>

            <button type="submit" class="btn btn-warning w-100">Update Food</button>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>
