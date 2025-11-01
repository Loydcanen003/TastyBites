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
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// --- SEARCH AND FILTER LOGIC for 'My Recipes' ---
$searchQuery = trim($_GET['search_query'] ?? '');
$filterType = $_GET['type'] ?? 'all';

// Base SQL query ALWAYS filters by the logged-in user for security
$sql = "SELECT * FROM recipes WHERE uploaded_by = ?";
$params = [$username];
$types = 's';

if (!empty($searchQuery)) {
  $sql .= " AND recipe_name LIKE ?";
  $params[] = "%" . $searchQuery . "%";
  $types .= 's';
}
if ($filterType !== 'all') {
  $sql .= " AND recipe_type = ?";
  $params[] = $filterType;
  $types .= 's';
}
$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
// --- END OF NEW LOGIC ---

$recipeTypes = ['Breakfast', 'Lunch', 'Dinner', 'Dessert', 'Snack', 'Appetizer', 'Drinks'];
?>
<!DOCTYPE html>
<html lang="en" style="scroll-behavior: smooth">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tasty Bites | My Recipes</title>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="stylesheet" href="assets/media.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Felipa&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      padding-top: 70px;
    }

    .recipe-card {
      cursor: pointer;
      transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .recipe-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
          <li class="nav-item"><a class="nav-link active" href="my_recipe.php">My Recipes</a></li>
          <li class="nav-item"><a class="nav-link" href="add_recipe.php">Add Recipe</a></li>
        </ul>
        <form class="d-flex mx-auto my-2 my-lg-0" role="search" method="GET" action="my_recipe.php">
          <input class="form-control me-2" type="search" name="search_query" placeholder="Search my recipes..." aria-label="Search" value="<?php echo htmlspecialchars($searchQuery); ?>">
          <button class="btn btn-outline-secondary" type="submit">Search</button>
        </form>

        <div class="navbar-nav ms-lg-3">
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
      <div class="carousel-indicators">
        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
      </div>
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img src="https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=1920&q=80" class="d-block w-100" alt="Colorful salad bowl" style="max-height: 400px; object-fit: cover;">
          <div class="carousel-caption d-none d-md-block">
            <h1 class="display-4 fw-bold">Your Creations, Your Flavors.</h1>
            <p class="lead my-3">Manage your uploaded recipes, make edits, and keep your personal cookbook organized.</p>
          </div>
        </div>
        <div class="carousel-item">
          <img src="https://images.unsplash.com/photo-1464349153735-7db50ed83c84?auto=format&fit=crop&w=1920&q=80" class="d-block w-100" alt="Pancakes with berries" style="max-height: 400px; object-fit: cover;">
          <div class="carousel-caption d-none d-md-block">
            <h1 class="display-4 fw-bold">Rediscover Your Favorites.</h1>
            <p class="lead my-3">Easily find the recipes you love to make again and again.</p>
          </div>
        </div>
        <div class="carousel-item">
          <img src="https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?auto=format&fit=crop&w=1920&q=80" class="d-block w-100" alt="Freshly baked pizza" style="max-height: 400px; object-fit: cover;">
          <div class="carousel-caption d-none d-md-block">
            <h1 class="display-4 fw-bold">Plan Your Next Meal.</h1>
            <p class="lead my-3">Keep all your culinary ideas in one place, ready for the kitchen.</p>
          </div>
        </div>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
      <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
    </div>
  </div>

  <section class="py-5 px-3 px-lg-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
      <h3 class="mb-3 mb-md-0">My Uploaded Recipes</h3>
      <form method="GET" class="d-flex align-items-center gap-2">
        <input type="hidden" name="search_query" value="<?php echo htmlspecialchars($searchQuery); ?>">
        <select class="form-select w-auto" name="type" onchange="this.form.submit()">
          <option value="all" <?php if ($filterType == 'all') echo 'selected'; ?>>All Types</option>
          <?php foreach ($recipeTypes as $type): ?>
            <option value="<?php echo $type; ?>" <?php if ($filterType == $type) echo 'selected'; ?>><?php echo $type; ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
    <div class="row g-4">
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="col-12 col-md-6">
            <div class="card recipe-card h-100 shadow-sm" data-bs-toggle="modal" data-bs-target="#recipeModal<?php echo $row['id']; ?>" style="cursor:pointer;">
              <img src="<?php echo htmlspecialchars($row['image']); ?>" class="card-img-top" alt="Recipe Image" style="height: 250px; object-fit: cover;">
              <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($row['recipe_name']); ?></h5>
                <span class="badge bg-info mb-2"><?php echo htmlspecialchars($row['recipe_type']); ?></span>
                <p class="card-text text-secondary small mb-0"><?php echo htmlspecialchars(mb_strimwidth($row['description'], 0, 120, '...')); ?></p>
              </div>
            </div>
          </div>
          <div class="modal fade" id="recipeModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title"><?php echo htmlspecialchars($row['recipe_name']); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <img src="<?php echo htmlspecialchars($row['image']); ?>" class="img-fluid rounded mb-4" alt="Recipe Image">
                  <p><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                  <hr>
                  <p><strong>Ingredients:</strong><br><?php echo nl2br(htmlspecialchars($row['ingredients'] ?? 'Not provided')); ?></p>
                  <hr>
                  <p><strong>Instructions:</strong><br><?php echo nl2br(htmlspecialchars($row['instructions'] ?? 'Not provided')); ?></p>
                </div>
                <div class="modal-footer">
                  <a href="delete_recipe.php?id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this recipe?');">Delete</a>
                  <a href="edit_recipe.php?id=<?php echo $row['id']; ?>" class="btn btn-warning">Edit Recipe</a>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-muted col-12">You haven't uploaded any recipes matching your criteria.</p>
      <?php endif; ?>
    </div>
  </section>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>