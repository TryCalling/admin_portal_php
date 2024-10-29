<?php
require_once 'connectdb.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fetch all products
$sql = "SELECT * FROM products ORDER BY id ASC";
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate Total Products and Revenue
$totalProducts = count($products);
$totalRevenue = array_sum(array_column($products, 'price'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $targetDir = 'images_products/';
        
        if (!file_exists($targetDir)) {
            if (!mkdir($targetDir, 0775, true)) {
                die("Failed to create directory: " . $targetDir);
            }
        }
    
        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $image = $targetDir . $imageName;
    
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $image)) {
            die("Failed to upload image. Check directory permissions and path: " . $targetDir);
        }
    } else {
        die("Error uploading image: " . $_FILES['image']['error']);
    }

    try {
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            $id = $_POST['id'];

            // Retrieve existing product to get the current image path
            $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $existingProduct = $stmt->fetch(PDO::FETCH_ASSOC);

            // If no new image is uploaded, keep the current image
            if (!$image) {
                $image = $existingProduct['image'];
            }

            $sql = "UPDATE products SET title = ?, description = ?, price = ?, image = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $description, $price, $image, $id]);
        } else {
            // Ensure image is provided for new products
            if (!$image) {
                throw new Exception("Image is required for new products.");
            }

            $sql = "INSERT INTO products (title, description, price, image) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $description, $price, $image]);
        }

        header("Location: index.php?success=Product saved successfully");
        exit;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

$message = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
?>

<!-- HTML CSS and JS Front-end -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal</title>

    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            height: 100vh;
            overflow: hidden;
        }

        .main-container {
            display: flex;
            height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 290px;
            background-color: #2c3e50;
            color: #ecf0f1;
            transition: width 0.3s;
        }

        .user-info {
            text-align: center;
            margin: 20px 0;
        }

        .user-img {
            border-radius: 50%;
            margin-bottom: 10px;
            width: 60px;
            height: 60px;
        }

        .username {
            font-size: 23px;
            font-weight: bold;
        }

        .nav-menu {
            list-style: none;
            padding: 0;
        }

        .nav-item {
            padding: 15px 20px;
            font-size: 18px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
            border-radius: 8px;
        }

        .nav-item i {
            margin-right: 15px;
            font-size: 20px;
            transition: color 0.3s;
        }

        .nav-item:hover {
            background-color: #34495e;
            color: orange;
        }

        .nav-item:hover i {
            color: orange;
        }

        .nav-item a {
            text-decoration: none;
            color: inherit;
            font-size: 18px;
            display: flex;
            align-items: center;
            width: 100%;
        }

        /* Content Area */
        .content {
            flex-grow: 1;
            padding: 15px;
            overflow-y: auto;
        }

        /* Custom Buttons */
        .custom-btn {
        background-color: red;
        border-color: red;
        color: white;
        }

        .custom-orange-btn {
        background-color: #4CBB17;
        border-color: #4CBB17;
        color: white;
        }

        .custom-orange-btn:hover {
        background-color: orange;
        border-color: orange;
        }

        .btn-container {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 10px;
        }

        /* Table Container */
        .table-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Top Bar Styling */
        .top-bar {
            background: linear-gradient(135deg, #4CBB17, #2c3e50);
            color: white;
            padding: 15px 25px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .top-bar h3 {
            margin: 0;
            font-weight: bold;
            font-size: 24px;
            letter-spacing: 1px;
        }

        /* Dashboard Icon */
        #dashboard .top-bar h3::before {
        content: '\f015'; /* FontAwesome home icon */
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        margin-right: 10px;
        }

        /* Products Icon */
        #products .top-bar h3::before {
        content: '\f291'; /* FontAwesome box icon */
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        margin-right: 10px;
        }

    </style>
</head>

<body>
    <div class="main-container">
        <!-- Sidebar Section -->
        <div class="sidebar" id="sidebar">
            <div class="user-info">
                <img src="https://img.freepik.com/premium-photo/colorful-display-word-code-wall-with-words-code-code-code_1099689-742.jpg"
                     alt="User Image" class="user-img" />
                <h3 class="username">Admin</h3>
            </div>
            <ul class="nav-menu">
                <li class="nav-item active" data-target="dashboard">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </li>
                <li class="nav-item" data-target="products">
                    <i class="fas fa-box"></i> <span>Manage Products</span>
                </li>
                <li class="nav-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <a href="logout.php" class="text-white">Logout</a>
                </li>
            </ul>
        </div>

        <!-- Content Section -->
        <div class="content">
            <div id="dashboard" class="active-content">
                <div class="top-bar">
                    <h3>Dashboard</h3>
                </div>
            </div>

            <div id="products" class="active-content" style="display: none;">
                <div class="top-bar">
                    <h3>Manage Products</h3>
                </div>

                <?php if (isset($message) && $message): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card bg-primary text-white mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Total Products</h5>
                                <p class="card-text"><?= $totalProducts ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-success text-white mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Total Revenue</h5>
                                <p class="card-text">$<?= number_format($totalRevenue, 2) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2>Products List</h2>
                        <button class="btn custom-orange-btn" data-toggle="modal" data-target="#addProductModal">Add New Product</button>
                    </div>
                    <table class="table mt-4">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Image</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= $product['id'] ?></td>
                                    <td><?= htmlspecialchars($product['title']) ?></td>
                                    <td><?= htmlspecialchars($product['description']) ?></td>
                                    <td><img src="<?= $product['image'] ?>" alt="Image" width="80"></td>
                                    <td>$<?= number_format($product['price'], 2) ?></td>
                                    <td>
                                        <button class="btn btn-warning" onclick="editProduct(<?= htmlspecialchars(json_encode($product)) ?>)">Edit</button>
                                        <button class="btn btn-danger" onclick="deleteProduct(<?= $product['id'] ?>)">Delete</button>
                                    </td>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add New Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="index.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Product</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="product_id">
                        <div class="form-group">
                            <input type="text" name="title" class="form-control" placeholder="Product Title" required>
                        </div>
                        <div class="form-group">
                            <textarea name="description" class="form-control" placeholder="Description"></textarea>
                        </div>
                        <div class="form-group">
                            <input type="file" name="image" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <input type="number" name="price" class="form-control" step="0.01" placeholder="Price" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn custom-btn" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_product_id">
                    <div class="form-group">
                        <input type="text" name="title" id="edit_title" class="form-control" placeholder="Product Title" required>
                    </div>
                    <div class="form-group">
                        <textarea name="description" id="edit_description" class="form-control" placeholder="Description"></textarea>
                    </div>
                    <div class="form-group">
                        <input type="file" name="image" id="edit_image" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <input type="number" name="price" id="edit_price" class="form-control" step="0.01" placeholder="Price" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn custom-btn" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <script>
        
        // Ensure the dashboard is shown on first load or after login
        window.addEventListener('load', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const isLoggedIn = urlParams.has('login'); // Check if login occurred
            const savedSection = localStorage.getItem('activeSection') || 'dashboard'; // Default to 'dashboard'

            // If login just happened, reset to Dashboard
            const sectionToShow = isLoggedIn ? 'dashboard' : savedSection;

            showSection(sectionToShow); // Display the correct section
            setActiveMenu(sectionToShow); // Set the menu item as active

            // Clear the login parameter to prevent re-triggering
            if (isLoggedIn) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });

        // Handle navigation clicks and store the active section
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', () => {
                const target = item.getAttribute('data-target');

                // Save the selected section to localStorage
                localStorage.setItem('activeSection', target);

                showSection(target); // Show the selected section
                setActiveMenu(target); // Set the clicked menu item as active
            });
        });


        // Function to display the selected section and hide others
        function showSection(target) {
            document.querySelectorAll('.active-content').forEach(content => {
                content.style.display = content.id === target ? 'block' : 'none';
            });
        }

        // Function to mark the clicked menu item as active
        function setActiveMenu(target) {
            document.querySelectorAll('.nav-item').forEach(nav => {
                nav.classList.remove('active');
            if (nav.getAttribute('data-target') === target) {
            nav.classList.add('active');
                }
            });
        }

        function addProduct() {
            document.getElementById('add_product_id').value = ''; // Clear hidden ID field
            document.querySelector('[name="title"]').value = '';
            document.querySelector('[name="description"]').value = '';
            document.querySelector('[name="price"]').value = '';
            $('#addProductModal').modal('show');
        }

        // Populate product details in modal for editing
        function editProduct(product) {
            document.getElementById('product_id').value = product.id;
            document.querySelector('[name="title"]').value = product.title;
            document.querySelector('[name="description"]').value = product.description;
            document.querySelector('[name="price"]').value = product.price;
            $('#editProductModal').modal('show');
        }

        // Confirm before deleting product
        function deleteProduct(id) {
            if (confirm('Are you sure?')) {
                window.location.href = 'delete.php?id=' + id;
            }
        }

    </script>


    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
</body>
</html>

