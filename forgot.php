<?php
session_start();
include 'config.php';

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['emailaddress'] ?? '');

    if (empty($emailaddress)) {
        $error = "Please enter your email address.";
    } elseif (!filter_var($emailaddress, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } else {
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE emailaddress = ?");
        $stmt->bind_param("s", $emailaddress);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            $token = bin2hex(random_bytes(50));

            $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

            $stmtUpdate = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ?  WHERE id = ?");
            $stmtUpdate->bind_param("ssi", $token, $expires, $user['id']);
            $stmtUpdate->execute();
            $stmtUpdate->close();

            $resetLink = "http://" . $_SERVER['HTTP_POST'] . dirname($_SERVER['PHP_SELF']) . "/reset.php?token=" . $token;
            $subject = "TastyBytes Password Reset";
            $body = "Hi " . htmlspecialchars($user['username']) . ",\n\n";
            $body .= "Click the link below to reset your password:\n";
            $body .= $resetLink . "\n\n";
            $body .= "This link will expire in 1 hour.";

            if (mail($emailaddress, $subject, $body)) {
                $message = "A password reset link has been sent to " . htmlspecialchars($emailaddress);
            } else {
                $error = "Failed to send the email. Please try again later.";
            }
        } else {
            $error = "No account found with that email address.";
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
    <title>TastyBites - Forgot Password</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-9ndCyUa0Dke3vQOiAfNjqxdcfHOm7lggJMAqQklHlKQEOOZpD6c6UJ0T1zOokmBt"
        crossorigin="anonymous" />

    <link
        href="https://fonts.googleapis.com/css2?family=Felipa:wght@400&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

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
            width: 524px;
            position: relative;
            padding: 0;
        }

        .right-panel {
            flex: 1;
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/images/background.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding: 60px;
        }

        .brand-title {
            font-family: 'Felipa', cursive;
            font-size: 60px;
            color: #000;
            text-align: center;
            margin-top: 185px;
            margin-bottom: 60px;
        }

        .welcome-text {
            font-family: 'Poppins', sans-serif;
            font-weight: 300;
            font-size: 20px;
            color: #000;
            text-align: center;
            margin-bottom: 65px;
        }

        .form-container {
            position: absolute;
            left: 79px;
            top: 377px;
            width: 365px;
        }

        .custom-input {
            background: white;
            border: 1px solid #000;
            border-radius: 12px;
            height: 60px;
            width: 100%;
            padding: 0 19px;
            font-family: 'Poppins', sans-serif;
            font-weight: 300;
            font-size: 15px;
            color: #8f8f8f;
            margin-bottom: 35px;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }

            .left-panel {
                width: 100%;
                height: 100vh;
            }

            .right-panel {
                display: none;
            }

            .form-container {
                position: relative;
                left: auto;
                top: auto;
                width: 90%;
                margin: 0 auto;
            }

            .brand-title {
                margin-top: 50px;
                font-size: 48px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="left-panel">
            <div class="text-center">
                <h1 class="brand-title">TastyBites</h1>
                <p class="welcome-text">Forgot Password</p>
            </div>

            <div class="form-container">
                <form id="forgotForm">
                    <input style="background: white; border: 1px solid #000; border-radius: 12px; height: 60px; width: 100%; padding: 0 19px; font-family: 'Poppins', sans-serif; font-weight: 300; font-size: 15px; color: #8f8f8f; margin-bottom: 35px;"
                        type="email"
                        class="custom-input"
                        placeholder="Enter Email"
                        id="email"
                        required />
                    <div style="display: flex; gap: 20px;">
                        <button type="button" onclick="window.location.href='login.php'"
                            style="Background: #454545ff; border: none; margin-left: 20px; border-radius: 12px; height: 60px; width: 100%; color: white; font-family: 'Poppins', sans-serif; font-weight: 500; font-size: 22px; margin-bottom: 20px; cursor: pointer;">
                            Back
                        </button>

                        <button type="submit"
                            style="Background: #f09d58; border: none; border-radius: 12px; height: 60px; width: 100%; color: white; 
                        font-family: 'Poppins', sans-serif; font-weight: 500; font-size: 22px; margin-bottom: 20px; cursor: pointer;">
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="right-panel">
            <h2 class="right-title" style="font-family: 'Felipa', cursive; font-size: 95px; color: white; line-height: 1.1; margin-bottom: 40px; max-width: 910px;">An unforgettable symphony of taste.</h2>
            <p class="right-description" style="font-family: 'Poppins', sans-serif; font-weight: 400; font-size: 16px; color: white; line-height: 1.6; text-align: justify; max-width: 640px;">
                TastyBites is your go-to recipe companion — helping you create, organize, and share delicious dishes with ease.
                Whether you’re cooking at home or exploring new flavors, TastyBites makes every meal memorable.
            </p>
        </div>
    </div>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-GeOa4bHjk+qjV5QVlTHvYMhqQNbdHO19HKm0pGXZOr7mGOqcfh5UhMx4K8mvQHPq"
        crossorigin="anonymous">
    </script>

    <script>
        document.getElementById('forgotForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('email').value;
            if (email) {
                alert(`A password reset link has been sent to ${email}`);
            } else {
                alert('Please enter your email to reset password');
            }
        });
    </script>
</body>

</html>