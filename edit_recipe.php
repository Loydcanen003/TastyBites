<?php
session_start();
include 'config.php';
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];
$recipe_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = '';
$success = '';

// Define the available recipe types
$recipeTypes = ['Breakfast', 'Lunch', 'Dinner', 'Dessert', 'Snack', 'Appetizer', 'Drinks'];

// Fetch the existing recipe data
$stmt = $conn->prepare("SELECT * FROM recipes WHERE id = ? AND uploaded_by = ?");
$stmt->bind_param("is", $recipe_id, $username);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Recipe not found or you don't have permission to edit it.");
}
$recipe = $result->fetch_assoc();
$stmt->close();

// Handle form submission for updating the recipe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipe_name = trim($_POST['recipe_name']);
    $description = trim($_POST['description']);
    $recipe_type = trim($_POST['recipe_type']); // Get the new recipe type
    $ingredients = trim($_POST['ingredients']);
    $instructions = trim($_POST['instructions']);
    $current_image = $recipe['image'];
    $new_image_path = $current_image;

    // Handle image upload
    if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] == 0) {
        $target_dir = "uploads/";
        $image_name = basename($_FILES["new_image"]["name"]);
        $target_file = $target_dir . time() . '_' . $image_name;
        if (move_uploaded_file($_FILES["new_image"]["tmp_name"], $target_file)) {
            $new_image_path = $target_file;
            if ($current_image && file_exists($current_image) && strpos($current_image, 'default') === false) {
                unlink($current_image);
            }
        } else {
            $error = "Sorry, there was an error uploading your file.";
        }
    }

    if (empty($error)) {
        // MODIFIED: Update the recipe in the database, now including recipe_type
        $update_stmt = $conn->prepare("UPDATE recipes SET recipe_name = ?, description = ?, recipe_type = ?, ingredients = ?, instructions = ?, image = ? WHERE id = ? AND uploaded_by = ?");
        $update_stmt->bind_param("ssssssis", $recipe_name, $description, $recipe_type, $ingredients, $instructions, $new_image_path, $recipe_id, $username);

        if ($update_stmt->execute()) {
            header("Location: my_recipe.php?status=updated");
            exit();
        } else {
            $error = "Error updating recipe: " . $conn->error;
        }
        $update_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recipe - Tasty Bites</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/media.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Felipa&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body class="position-relative">
    <nav class="px-5 py-2 d-flex nav-xxl align-items-center justify-content-between position-fixed top-0 z-3 bg-light-subtle w-100">
        <div class="flex-row d-flex align-items-center gap-5 w-75">
            <div class="flex-row d-flex align-items-center gap-4 title-div">
                <h1>Tasty Bites</h1>
            </div>
            <div class="flex-row d-flex align-items-center div-tabs gap-5">
                <a href="dashboard.php" class="tabs">Home</a>
                <a href="my_recipe.php" class="tabs active">My Recipe</a>
                <a href="add_recipe.php" class="tabs">Add Recipe</a>
            </div>
        </div>
        <div class="px-5 d-flex align-items-center gap-3">
            <span class="tabs text-nowrap">Hello, <?php echo htmlspecialchars($username, ENT_QUOTES); ?></span>
            <a href="logout.php" class="btn btn-sm btn-outline-secondary">Logout</a>
        </div>
    </nav>

    <div class="container py-5" style="margin-top: 80px;">
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h2 class="mb-0">Edit Recipe</h2>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="recipe_name" class="form-label">Recipe Name</label>
                                    <input type="text" class="form-control" id="recipe_name" name="recipe_name" value="<?php echo htmlspecialchars($recipe['recipe_name']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="recipe_type" class="form-label">Recipe Type</label>
                                    <select class="form-select" id="recipe_type" name="recipe_type" required>
                                        <?php foreach ($recipeTypes as $type): ?>
                                            <option value="<?php echo $type; ?>" <?php if ($recipe['recipe_type'] == $type) echo 'selected'; ?>>
                                                <?php echo $type; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($recipe['description']); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="ingredients" class="form-label">Ingredients</label>
                                <textarea class="form-control" id="ingredients" name="ingredients" rows="5" required><?php echo htmlspecialchars($recipe['ingredients']); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="instructions" class="form-label">Instructions</label>
                                <textarea class="form-control" id="instructions" name="instructions" rows="8" required><?php echo htmlspecialchars($recipe['instructions']); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Current Image</label>
                                <div><img src="<?php echo htmlspecialchars($recipe['image']); ?>" alt="Current Image" style="max-width: 200px; border-radius: 8px;"></div>
                            </div>

                            <div class="mb-4">
                                <label for="new_image" class="form-label">Upload New Image (Optional)</label>
                                <input class="form-control" type="file" id="new_image" name="new_image" accept="image/*">
                            </div>

                            <hr>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="my_recipe.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary" style="background-color: #F09D58; border-color: #F09D58; color: #fff;">Save Changes</button>
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