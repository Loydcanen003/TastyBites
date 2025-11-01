<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// DB connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "tastybytesdb";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission for ADDING a recipe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_recipe'])) {
    $recipeName   = $_POST['recipeName'];
    $description  = $_POST['description'];
    $recipeType   = $_POST['recipeType'];
    $ingredients  = $_POST['ingredients'];
    $instructions = $_POST['instructions'];
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    $fileName = time() . "_" . basename($_FILES["recipeImage"]["name"]);
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["recipeImage"]["tmp_name"], $targetFilePath)) {
        $sql = "INSERT INTO recipes (recipe_name, description, recipe_type, ingredients, instructions, image, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $recipeName, $description, $recipeType, $ingredients, $instructions, $targetFilePath, $username);
        if ($stmt->execute()) {
            header("Location: my_recipe.php?status=success");
            exit();
        }
        $stmt->close();
    }
}

// Fetch only the current user's most recent recipes
$sql_recent = "SELECT * FROM recipes WHERE uploaded_by = ? ORDER BY id DESC LIMIT 3";
$stmt_recent = $conn->prepare($sql_recent);
$stmt_recent->bind_param("s", $username);
$stmt_recent->execute();
$recipes = $stmt_recent->get_result();

$recipeTypes = ['Breakfast', 'Lunch', 'Dinner', 'Dessert', 'Snack', 'Appetizer', 'Drinks'];
?>
<!DOCTYPE html>
<html lang="en" style="scroll-behavior: smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tasty Bites | Add Recipe</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/media.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Felipa&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            padding-top: 70px;
        }

        .carousel-caption {
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: .5rem;
            padding: 1.5rem;
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
                    <li class="nav-item"><a class="nav-link active" href="add_recipe.php">Add Recipe</a></li>
                </ul>

                <div class="navbar-nav ms-lg-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Hello, <?php echo htmlspecialchars($username, ENT_QUOTES); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="edit_profile.php">Edit Profile</a></li>
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

    <div class="container-fluid px-0">
        <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="assets/images/hero-2.jpg" class="d-block w-100" alt="Hero 1" style="max-height: 400px; object-fit: cover;">
                    <div class="carousel-caption d-none d-md-block">
                        <h1 class="display-4 fw-bold">Share Your Passion.</h1>
                        <p class="lead my-3">Upload your favorite recipes and build your personal digital cookbook.</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="assets/images/hero-3.jpg" class="d-block w-100" alt="Hero 2" style="max-height: 400px; object-fit: cover;">
                    <div class="carousel-caption d-none d-md-block">
                        <h1 class="display-4 fw-bold">Inspire Others.</h1>
                        <p class="lead my-3">Your unique flavors could become someone else's new favorite dish.</p>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
        </div>
    </div>

    <section class="py-5 bg-light px-3 px-lg-5">
        <div class="container-fluid">
            <h3 class="mb-4">Add Your Recipe</h3>
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-4 mb-3"><label class="form-label">Upload Image</label><input type="file" class="form-control" name="recipeImage" accept="image/*" required></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Recipe Name</label><input type="text" class="form-control" name="recipeName" required></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Recipe Type</label><select class="form-select" name="recipeType" required>
                                    <option value="" disabled selected>Select a type...</option><?php foreach ($recipeTypes as $type): ?><option value="<?php echo $type; ?>"><?php echo $type; ?></option><?php endforeach; ?>
                                </select></div>
                        </div>
                        <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2" required></textarea></div>
                        <div class="mb-3"><label class="form-label">Ingredients (one per line)</label><textarea class="form-control" name="ingredients" rows="5" required></textarea></div>
                        <div class="mb-3"><label class="form-label">Instructions</label><textarea class="form-control" name="instructions" rows="7" required></textarea></div>
                        <button type="submit" name="submit_recipe" class="btn" style="background-color: #F09D58; border-color: #F09D58; color: #fff;">Submit Recipe</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 px-3 px-lg-5">
        <h3 class="mb-4">Recently Added by You</h3>
        <div class="row g-4">
            <?php if ($recipes->num_rows > 0): ?>
                <?php while ($row = $recipes->fetch_assoc()): ?>
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="card h-100 shadow-sm">
                            <img src="<?php echo htmlspecialchars($row['image']); ?>" class="card-img-top" alt="Recipe Image" style="height: 220px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['recipe_name']); ?></h5>
                                <span class="badge bg-secondary mb-2"><?php echo htmlspecialchars($row['recipe_type']); ?></span>
                                <p class="card-text small"><?php echo htmlspecialchars(mb_strimwidth($row['description'], 0, 100, '...')); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted">You haven't added any recipes yet.</p>
            <?php endif; ?>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>