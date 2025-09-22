<?php

session_start();
include 'config.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);

    if (empty($username) || empty($password)) {
        $error = "Please enter username and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s" , $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_id'] = $user['id'];

                if ($remember) {
                    setcookie('username', $user['username'], time() + (86400 * 30), "/"); // 30 days
                }

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }

        $stmt->close();
    }  
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TastyBites - Login</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Felipa:wght@400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

     <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
        }
        .login-container {
            height: 100vh;
            display: flex;
        }
        .left-panel {
            background: white;
            width: 500px;
            position: relative;
            padding: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .right-panel {
            flex: 1;
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://api.oneworld.id/uploads/scotland_1893646_1920_cbd620b582.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding: 40px;
        }
        .left-content {
            width: 100%;
            max-width: 250px;
            text-align: center;
        }
        .brand-title {
            font-family: 'Felipa', cursive;
            font-size: 38px;
            color: #000;
            text-align: center;
            margin-bottom: 30px;
        }
        .welcome-text {
            font-family: 'Poppins', sans-serif;
            font-weight: 300;
            font-size: 16px;
            color: #000;
            text-align: center;
            margin-bottom: 35px;
        }
        .form-container {
            width: 100%;
        }
        .custom-input {
            background: white;
            border: 1px solid #000;
            border-radius: 10px;
            height: 42px;
            width: 100%;
            padding: 0 14px;
            font-family: 'Poppins', sans-serif;
            font-weight: 300;
            font-size: 13px;
            color: #8f8f8f;
            margin-bottom: 20px;
        }
        .custom-input:focus {
            outline: none;
            border-color: #000;
            color: #000;
        }
        .remember-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            font-family: 'Poppins', sans-serif;
            font-weight: 300;
            font-size: 10px;
        }
        .remember-left {
            display: flex;
            align-items: center;
            color: #595959;
        }
        .remember-checkbox {
            width: 12px;
            height: 12px;
            border: 0.5px solid #636060;
            border-radius: 2px;
            margin-right: 6px;
        }
        .forgot-password {
            color: #000;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
        }
        .login-btn {
            background: #f09d58;
            border: none;
            border-radius: 10px;
            height: 42px;
            width: 100%;
            color: white;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            font-size: 16px;
            margin-bottom: 15px;
            cursor: pointer;
        }
        .login-btn:hover {
            background: #e8914a;
        }
        .register-link {
            text-align: center;
            font-family: 'Poppins', sans-serif;
            font-weight: 300;
            font-size: 10px;
            color: #000;
        }
        .register-link a {
            font-weight: 400;
            text-decoration: underline;
            color: #000;
        }
        .right-title {
            font-family: 'Felipa', cursive;
            font-size: 65px;
            color: white;
            line-height: 1.1;
            margin-bottom: 30px;
            max-width: 700px;
        }
        .right-description {
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            font-size: 14px;
            color: white;
            line-height: 1.6;
            text-align: justify;
            max-width: 520px;
        }
        
        /* Mobile Responsive Styles */
        @media (max-width: 800px) {
            body {
                overflow: auto;
            }
            
            .login-container {
                flex-direction: column;
                height: 100vh;
                background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1689997122000-c94449288dd1?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxnb3VybWV0JTIwZm9vZCUyMHJlc3RhdXJhbnQlMjBkaXNofGVufDF8fHx8MTc1NTY4NzYzNnww&ixlib=rb-4.1.0&q=80&w=1080&utm_source=figma&utm_medium=referral');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
            }
            
            .left-panel {
                background: transparent;
                width: 100%;
                height: 100vh;
                padding: 40px 38px;
                position: relative;
                justify-content: flex-start;
                padding-top: 100px;
            }
            
            .right-panel {
                display: none;
            }
            
            .left-content {
                max-width: 300px;
                width: 100%;
            }
            
            .brand-title {
                font-size: 40px;
                color: white;
                margin-bottom: 50px;
                margin-top: 30px;
            }
            
            .welcome-text {
                font-size: 20px;
                color: white;
                margin-bottom: 60px;
            }
            
            .custom-input {
                height: 60px;
                font-size: 15px;
                margin-bottom: 25px;
                border-radius: 12px;
                padding: 0 19px;
            }
            
            .remember-section {
                font-size: 10px;
                margin-bottom: 15px;
            }
            
            .remember-left {
                color: white;
            }
            
            .remember-checkbox {
                width: 15px;
                height: 15px;
                border: 0.5px solid #636060;
                background: white;
            }
            
            .forgot-password {
                color: white;
            }
            
            .login-btn {
                height: 55px;
                font-size: 22px;
                border-radius: 12px;
                margin-bottom: 0;
            }
            
            .register-link {
                display: block;
            }
        }

        @media (max-width: 475px) {
            .left-panel {
                padding: 40px 20px;
                padding-top: 80px;
            }
            
            .left-content {
                max-width: 280px;
            }
            
            .brand-title {
                font-size: 36px;
            }
            
            .welcome-text {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Panel -->
        <div class="left-panel">
            <div class="left-content">
                <h1 class="brand-title">TastyBites</h1>
                <p class="welcome-text">Welcome!</p>
                
                <div class="form-container">
                <form id="loginForm" method="POST" action="">
                    <input 
                        type="text" 
                        class="custom-input" 
                        placeholder="Username"
                        id="username"
                        name="username"
                        required
                        value="<?php echo htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES); ?>"
                    />
                    <input 
                        type="password" 
                        class="custom-input" 
                        placeholder="Password"
                        id="password"
                        name="password"
                        required
                    />
                    
                    <div class="remember-section">
                        <div class="remember-left">
                            <input type="checkbox" class="remember-checkbox" id="remember" name="remember" <?php if(isset($_POST['remember'])) echo 'checked'; ?> />
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="forgot.php" class="forgot-password">Forgot Password?</a>
                    </div>

                    <?php if($error): ?>
                        <div class="text-danger mb-2"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <button type="submit" class="login-btn">Login</button>
                    
                    <div class="register-link">
                        New Here? <a href="register.php">Register</a>
                    </div>
                </form>
                </div>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="right-panel">
            <h2 class="right-title">An unforgettable symphony of taste.</h2>
            <p class="right-description">
                Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum 
                sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Lorem ipsum dolor sit amet, 
                consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et 
                magnis dis parturient montes, nascetur ridiculus mus.
            </p>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
