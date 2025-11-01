<?php
session_start();
include 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $emailaddress = trim($_POST['emailaddress']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE emailaddress = ? AND id != ?");
    $checkStmt->bind_param("si", $emailaddress, $user_id);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        $error = "Email address is already in use by another account.";
    }
    $checkStmt->close();

    $password_sql_part = "";
    if (!empty($new_password)) {
        if ($new_password !== $confirm_password) {
            $error = "New passwords do not match.";
        } elseif (strlen($new_password) < 8) {
            $error = "Password must be at least 8 characters long.";
        } else {
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            $password_sql_part = ", password = ?";
        }
    }

    if (empty($error)) {
        $sql = "UPDATE users SET firstname = ?, lastname = ?, emailaddress = ? $password_sql_part WHERE id = ?";
        $updateStmt = $conn->prepare($sql);

        if (!empty($password_sql_part)) {
            $updateStmt->bind_param("ssssi", $firstname, $lastname, $emailaddress, $hashedPassword, $user_id);
        } else {
            $updateStmt->bind_param("sssi", $firstname, $lastname, $emailaddress, $user_id);
        }

        if ($updateStmt->execute()) {
            $success = "Profile updated successfully!";
        } else {
            $error = "Error updating profile. Please try again.";
        }
        $updateStmt->close();
    }
}

// Fetch current user data to pre-fill the form
$stmt = $conn->prepare("SELECT firstname, lastname, emailaddress FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Tasty Bites</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/media.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Felipa&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            padding-top: 70px;
        }

        .hero-bg {
            position: relative;
            background-image: url('https://images.unsplash.com/photo-1505935428862-770b6f24f629?auto=format&fit=crop&w=1920');
            background-size: cover;
            background-position: center;
            color: #fff;
        }

        .hero-bg::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 1;
        }

        .hero-bg .container {
            position: relative;
            z-index: 2;
        }
    </style>
</head>

<body class="position-relative">
    <nav class="navbar navbar-expand-lg bg-light-subtle shadow-sm fixed-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="dashboard.php">
                <h1>Tasty Bites</h1>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="my_recipe.php">My Recipes</a></li>
                    <li class="nav-item"><a class="nav-link" href="add_recipe.php">Add Recipe</a></li>
                </ul>
                <div class="navbar-nav ms-lg-auto mt-2 mt-lg-0">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Hello, <?php echo htmlspecialchars($username, ENT_QUOTES); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item active" href="edit_profile.php">Edit Profile</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </div>
            </div>
        </div>
    </nav>

    <div class="hero-bg">
        <div class="container px-4 py-5 text-center">
            <h1 class="display-4 fw-bold">Account Settings</h1>
            <p class="lead">Keep your personal information and password up to date.</p>
        </div>
    </div>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <h5 class="mb-3">Personal Information</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="firstname" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user_data['firstname']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lastname" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user_data['lastname']); ?>" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="emailaddress" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="emailaddress" name="emailaddress" value="<?php echo htmlspecialchars($user_data['emailaddress']); ?>" required>
                            </div>

                            <hr>

                            <h5 class="mb-3 mt-4">Change Password (Optional)</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Min. 8 characters">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary" style="background-color: #F09D58; border-color: #F09D58;">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>