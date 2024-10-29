<?php
require_once 'connectdb.php'; // Connect to the database
session_start(); // Start the session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if email and password are not empty
    if (empty($email) || empty($password)) {
        $error = "Email and Password cannot be empty.";
    } else {
        // Check if the user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verify the password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];  

                 // Redirect to index.php with login=true
                 header("Location: index.php?login=true");
                 exit;
            } else {
                $error = "Invalid email or password."; // Wrong password
            }
        } else {
            $error = "No user found with this email.";
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    <title>User Login</title>

    <style>
        body {
          background: linear-gradient(to right, #6a11cb, #2575fc);
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
        }

        .login-card {
          width: 100%;
          max-width: 400px;
          padding: 40px 30px;
          background: white;
          border-radius: 20px;
          box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.1);
          animation: fadeIn 1s ease-in-out;
      }

        @keyframes popIn {
            0% { transform: scale(0.8); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        h3 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            color: #444;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .form-control {
            border-radius: 15px;
            transition: all 0.3s;
        }

        .form-control:focus {
            box-shadow: 0 0 10px rgba(102, 166, 255, 0.8);
        }

        .btn-login {
            border-radius: 15px;
            font-weight: bold;
            background: linear-gradient(135deg, #66a6ff, #89f7fe);
            transition: background-color 0.3s, transform 0.2s;
        }

        .btn-login:hover {
            transform: scale(1.05);
            background: linear-gradient(135deg, #89f7fe, #66a6ff);
        }

        .input-group-text {
            background-color: rgba(102, 166, 255, 0.1);
        }

        .floating-effect {
            animation: floatUp 3s infinite ease-in-out;
        }

        @keyframes floatUp {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }

        .form-check-label {
            margin-left: 5px;
            font-size: 14px;
        }

        .alert-danger {
            margin-top: 15px;
            border-radius: 10px;
            animation: shake 0.3s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    </style>
</head>
<body>
    <div class="login-card floating-effect">
        <h3>Admin Login</h3>
        <form action="adminlogin.php" method="POST"> <!-- Ensure correct action -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    </div>
                    <input 
                        type="email" 
                        class="form-control" 
                        id="email" 
                        name="email" 
                        placeholder="Enter your email" 
                        required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    </div>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password" 
                        required>
                </div>
            </div>

            <div class="form-group form-check">
                <input type="checkbox" class="form-check-input" id="rememberMe">
                <label class="form-check-label" for="rememberMe">Remember me</label>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-login">Login</button>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger mt-3"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
</body>
</html>

