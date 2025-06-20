<?php
session_start();

// Redirect if already logged in
if(isset($_SESSION['user_id'])) {
    if($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: member/dashboard.php");
    }
    exit();
}

include_once 'config/database.php';
include_once 'classes/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$login_error = "";

if($_POST) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Server-side validation
    if(empty($username) || empty($password)) {
        $login_error = "Username and password are required.";
    } else {
        if($user->login($username, $password)) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            $_SESSION['full_name'] = $user->full_name;
            $_SESSION['role'] = $user->role;
            
            if($user->role == 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: member/dashboard.php");
            }
            exit();
        } else {
            $login_error = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PustakaKu - Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .brand-logo {
            color: #667eea;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="login-card p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-book brand-logo"></i>
                        <h2 class="fw-bold text-dark">PustakaKu</h2>
                        <p class="text-muted">Library Management System</p>
                    </div>
                    
                    <?php if($login_error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $login_error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="loginForm" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username or Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="username" name="username" required>
                                <div class="invalid-feedback">
                                    Please provide a valid username or email.
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="invalid-feedback">
                                    Please provide a password.
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </form>
                    
                    <div class="text-center">
                        <small class="text-muted">
                            Demo: admin/admin123 or create a member account
                        </small>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="register.php" class="text-decoration-none">
                            <i class="fas fa-user-plus me-1"></i>Create Member Account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Client-side validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                e.preventDefault();
                if (!username) {
                    document.getElementById('username').classList.add('is-invalid');
                }
                if (!password) {
                    document.getElementById('password').classList.add('is-invalid');
                }
            }
        });
    </script>
</body>
</html>
