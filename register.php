<?php
session_start();
include 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname'] ?? '');
    $middlename = trim($_POST['middlename'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $emailaddress = trim($_POST['emailaddress'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    if ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (!preg_match('/^[A-Za-z]{1,30}( [A-Za-z]{1,30}){0,2}$/', $firstname)) {
        $error = "Invalid first name.";
    } elseif (!preg_match('/^[A-Za-z]{2,30}$/', $lastname)) {
        $error = "Invalid last name.";
    } elseif (!filter_var($emailaddress, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (!preg_match('/^[A-Za-z0-9_]{4,20}$/', $username)) {
        $error = "Invalid username.";
    } else {
        $passwordErrors = [];
        if (strlen($password) < 8) $passwordErrors[] = "at least 8 characters";
        if (!preg_match('/[A-Z]/', $password)) $passwordErrors[] = "one uppercase letter";
        if (!preg_match('/[a-z]/', $password)) $passwordErrors[] = "one lowercase letter";
        if (!preg_match('/\d/', $password)) $passwordErrors[] = "one number";

        if (!empty($passwordErrors)) {
            $error = "Password must contain: " . implode(', ', $passwordErrors) . ".";
        }
    }

    if (!$error) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $checkStmt = $conn->prepare("SELECT username, emailaddress FROM users WHERE username = ? OR emailaddress = ?");
        $checkStmt->bind_param("ss", $username, $emailaddress);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $checkStmt->bind_result($existingUsername, $existingEmail);
            $checkStmt->fetch();
            if ($existingUsername === $username) {
                $error = "Username already exists. Please choose another.";
            } elseif ($existingEmail === $emailaddress) {
                $error = "Email address already registered. Please use another.";
            } else {
                $error = "Duplicate entry detected.";
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO users (firstname, middlename, lastname, emailaddress, username, password) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $firstname, $middlename, $lastname, $emailaddress, $username, $hashedPassword);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit();
            } else {
                $error = "Error: " . $stmt->error;
            }

            $stmt->close();
        }

        $checkStmt->close();
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TastyBites - Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .brand-title {
      font-family: 'Great Vibes', cursive;
    }

    .btn-warning {
      background-color: #f09d58;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      transition: background 0.2s ease-in-out;
      font-size: 0.9rem;
    }

    .btn-warning:hover {
      background-color: #e8914a;
    }

    .signup-container {
      flex-grow: 1;
      display: flex;
    }

    .signup-left {
      width: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px;
      background: #fff;
    }

    .signup-right {
      width: 50%;
      background: url('https://api.oneworld.id/uploads/scotland_1893646_1920_cbd620b582.jpg') no-repeat center center/cover;
      position: relative;
      display: flex;
      justify-content: center;
      align-items: center;
      text-align: left;
    }

    .signup-right::before {
      content: "";
      position: absolute;
      inset: 0;
      background: rgba(0, 0, 0, 0.8);
    }

    .signup-text {
      position: relative;
      color: #fff;
      padding: 60px;
      z-index: 10;
      max-width: 700px;
    }

    .signup-text h2 {
      font-family: 'Great Vibes', cursive;
      font-size: 3.5rem;
      margin-bottom: 1rem;
    }

    .signup-text p {
      font-size: 0.875rem;
      font-weight: 300;
    }

    @media (max-width: 768px) {
      .signup-container {
        flex-direction: column;
      }

      .signup-left,
      .signup-right {
        width: 100%;
      }

      .signup-right {
        display: none;
      }
    }
  </style>
</head>

<body>

  <section class="signup-container">

    <div class="signup-left">
      <div class="w-100 max-w-md bg-white shadow-lg rounded-2xl p-4 position-relative z-10">
        <a href="index.php" class="text-decoration-none">
          <h1 class="brand-title text-center text-4xl mb-2 text-dark">TastyBites</h1>
        </a>
        <p class="text-center text-sm mb-3">Create account</p>

        <?php if ($error): ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form id="registerForm" method="POST" novalidate>
          <div class="d-flex gap-2 mb-2">
            <input type="text" class="form-control text-sm py-2" placeholder="First name" name="firstname" required pattern="^[A-Za-z]{2,30}$" value="<?php echo htmlspecialchars($_POST['firstname'] ?? '', ENT_QUOTES); ?>">
            <input type="text" class="form-control text-sm py-2" placeholder="Middle name" name="middlename" pattern="^[A-Za-z]{0,30}$" value="<?php echo htmlspecialchars($_POST['middlename'] ?? '', ENT_QUOTES); ?>">
          </div>
          <div class="d-flex gap-2 mb-2">
            <input type="text" class="form-control text-sm py-2" placeholder="Last name" name="lastname" required pattern="^[A-Za-z]{2,30}$" value="<?php echo htmlspecialchars($_POST['lastname'] ?? '', ENT_QUOTES); ?>">
            <input type="email" class="form-control text-sm py-2" placeholder="Email Address" name="emailaddress" required pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[A-Za-z]{2,}$" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>">
          </div>
          <input type="text" class="form-control text-sm py-2 mb-2" placeholder="Username" name="username" required minlength="4" maxlength="20" pattern="^[A-Za-z0-9_]{4,20}$" value="<?php echo htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES); ?>">
          <input type="password" class="form-control text-sm py-2 mb-2" placeholder="Password" name="password" required minlength="8">
          <input type="password" class="form-control text-sm py-2 mb-3" placeholder="Confirm Password" name="confirmPassword" required>
          <div class="d-flex w-100">
            <a href="login.php" class="btn btn-secondary w-50 me-2 py-2">Back</a>
            <button type="submit" class="btn btn-warning w-50 py-2">Submit</button>
          </div>
        </form>
      </div>
    </div>

    <div class="signup-right">
      <div class="signup-text">
        <h2>An unforgettable symphony of taste.</h2>
        <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>
      </div>
    </div>

  </section>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>