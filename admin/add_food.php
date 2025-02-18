<?php
session_start(); // 启用 session
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Food</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center text-primary mb-4">Add Food</h1>

        <!-- Add Food Form -->
        <form id="addFoodForm" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="foodName" class="form-label">Food Name</label>
                <input type="text" class="form-control" id="foodName" name="foodName" required>
                <div class="invalid-feedback">Please enter the food name.</div>
            </div>

            <div class="mb-3">
                <label for="foodPrice" class="form-label">Price</label>
                <input type="number" class="form-control" id="foodPrice" name="foodPrice" required>
                <div class="invalid-feedback">Please enter a valid price.</div>
            </div>

            <div class="mb-3">
                <label for="foodDescription" class="form-label">Description</label>
                <textarea class="form-control" id="foodDescription" name="foodDescription" rows="3" required></textarea>
                <div class="invalid-feedback">Please provide a description.</div>
            </div>

            <div class="mb-3">
                <label for="foodImage" class="form-label">Image URL</label>
                <input type="text" class="form-control" id="foodImage" name="foodImage" required>
                <div class="invalid-feedback">Please provide a valid image URL.</div>
            </div>

            <button type="submit" class="btn btn-success w-100">Add Food</button>
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

        document.getElementById('addFoodForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const foodName = document.getElementById('foodName').value;
            const foodPrice = document.getElementById('foodPrice').value;
            const foodDescription = document.getElementById('foodDescription').value;
            const foodImage = document.getElementById('foodImage').value;

            fetch('../api/api.php?endpoint=add_food', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: foodName, price: foodPrice, description: foodDescription, image_url: foodImage })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.message === "Food added successfully") {
                    document.getElementById('addFoodForm').reset(); // Reset the form
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to add food');
            });
        });
    </script>
</body>
</html>
