//php new
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


//old  
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



//js new
<script>
    window.addEventListener('load', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const isLoggedIn = urlParams.has('login');
        const savedSection = localStorage.getItem('activeSection') || 'dashboard';

        const sectionToShow = isLoggedIn ? 'dashboard' : savedSection;
        showSection(sectionToShow);
        setActiveMenu(sectionToShow);

        if (isLoggedIn) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });

    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', () => {
            const target = item.getAttribute('data-target');
            localStorage.setItem('activeSection', target);
            showSection(target);
            setActiveMenu(target);
        });
    });

    function showSection(target) {
        document.querySelectorAll('.active-content').forEach(content => {
            content.style.display = content.id === target ? 'block' : 'none';
        });
    }

    function setActiveMenu(target) {
        document.querySelectorAll('.nav-item').forEach(nav => {
            nav.classList.remove('active');
            if (nav.getAttribute('data-target') === target) {
                nav.classList.add('active');
            }
        });
    }

    // Populate product details in modal for editing
    function editProduct(product) {
        document.getElementById('product_id').value = product.id;
        document.querySelector('[name="title"]').value = product.title;
        document.querySelector('[name="description"]').value = product.description;
        document.querySelector('[name="price"]').value = product.price;
        document.querySelector('[name="image"]').value = ''; // Clear image field
        $('#addProductModal').modal('show');
    }

    // Reset modal form for adding new product
    function addNewProduct() {
        document.getElementById('product_id').value = ''; // Clear hidden ID field
        document.querySelector('[name="title"]').value = '';
        document.querySelector('[name="description"]').value = '';
        document.querySelector('[name="price"]').value = '';
        document.querySelector('[name="image"]').value = '';
        $('#addProductModal').modal('show');
    }

    function deleteProduct(id) {
        if (confirm('Are you sure?')) {
            window.location.href = 'delete.php?id=' + id;
        }
    }
</script>

//js new 1
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

    // Populate product details in modal for editing
    function editProduct(product) {
        document.getElementById('product_id').value = product.id;
        document.getElementById('modalTitle').textContent = 'Edit Product';
        document.querySelector('[name="title"]').value = product.title;
        document.querySelector('[name="description"]').value = product.description;
        document.querySelector('[name="price"]').value = product.price;
        document.getElementById('image').required = false; // Image not required for edit
        document.getElementById('imageHelpText').textContent = 'Upload a new image to replace the current one, or leave blank to keep the existing image.';
        $('#productModal').modal('show');
    }

    // Reset modal form for adding new product
    function addNewProduct() {
        document.getElementById('product_id').value = ''; // Clear hidden ID field
        document.getElementById('modalTitle').textContent = 'Add New Product';
        document.querySelector('[name="title"]').value = '';
        document.querySelector('[name="description"]').value = '';
        document.querySelector('[name="price"]').value = '';
        document.getElementById('image').required = true; // Require image for new product
        document.getElementById('imageHelpText').textContent = 'Please upload an image for the new product.';
        $('#productModal').modal('show');
    }

    // Confirm before deleting product
    function deleteProduct(id) {
        if (confirm('Are you sure?')) {
            window.location.href = 'delete.php?id=' + id;
        }
    }
</script>



//js old
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

        // Populate product details in modal for editing
        function editProduct(product) {
            document.getElementById('product_id').value = product.id;
            document.querySelector('[name="title"]').value = product.title;
            document.querySelector('[name="description"]').value = product.description;
            document.querySelector('[name="price"]').value = product.price;
            $('#addProductModal').modal('show');
        }

        // Confirm before deleting product
        function deleteProduct(id) {
            if (confirm('Are you sure?')) {
                window.location.href = 'delete.php?id=' + id;
            }
        }

    </script>