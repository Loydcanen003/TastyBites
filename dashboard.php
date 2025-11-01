<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// DB connection & other PHP logic...
$host = "localhost";
$user = "root";
$pass = "";
$db   = "tastybytesdb";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Favorite Toggle Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_favorite'])) {
  $recipeId = intval($_POST['recipe_id']);
  $checkStmt = $conn->prepare("SELECT id FROM user_favorites WHERE user_id = ? AND recipe_id = ?");
  $checkStmt->bind_param("ii", $user_id, $recipeId);
  $checkStmt->execute();
  $result = $checkStmt->get_result();
  if ($result->num_rows > 0) {
    $deleteStmt = $conn->prepare("DELETE FROM user_favorites WHERE user_id = ? AND recipe_id = ?");
    $deleteStmt->bind_param("ii", $user_id, $recipeId);
    $deleteStmt->execute();
    $deleteStmt->close();
  } else {
    $insertStmt = $conn->prepare("INSERT INTO user_favorites (user_id, recipe_id) VALUES (?, ?)");
    $insertStmt->bind_param("ii", $user_id, $recipeId);
    $insertStmt->execute();
    $insertStmt->close();
  }
  $checkStmt->close();
  $queryString = http_build_query(['search_query' => $_GET['search_query'] ?? '', 'type' => $_GET['type'] ?? 'all']);
  header("Location: dashboard.php?" . $queryString);
  exit();
}

// Fetch user's favorite IDs
$favorite_ids = [];
$favIdStmt = $conn->prepare("SELECT recipe_id FROM user_favorites WHERE user_id = ?");
$favIdStmt->bind_param("i", $user_id);
$favIdStmt->execute();
$favIdResult = $favIdStmt->get_result();
while ($fav_row = $favIdResult->fetch_assoc()) {
  $favorite_ids[] = $fav_row['recipe_id'];
}
$favIdStmt->close();

// Public search and filter logic
$searchQuery = trim($_GET['search_query'] ?? '');
$filterType = $_GET['type'] ?? 'all';
$sql = "SELECT * FROM recipes";
$params = [];
$types = '';
$whereClauses = [];
if (!empty($searchQuery)) {
  $whereClauses[] = "recipe_name LIKE ?";
  $params[] = "%" . $searchQuery . "%";
  $types .= 's';
}
if ($filterType !== 'all') {
  $whereClauses[] = "recipe_type = ?";
  $params[] = $filterType;
  $types .= 's';
}
if (!empty($whereClauses)) {
  $sql .= " WHERE " . implode(" AND ", $whereClauses);
}
$sql .= " ORDER BY id DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$recipes = $stmt->get_result();

// Fetch "My Favorite Recipes"
$fav_stmt = $conn->prepare("SELECT r.* FROM recipes r JOIN user_favorites uf ON r.id = uf.recipe_id WHERE uf.user_id = ? ORDER BY r.id DESC");
$fav_stmt->bind_param("i", $user_id);
$fav_stmt->execute();
$favorites = $fav_stmt->get_result();

$recipeTypes = ['Breakfast', 'Lunch', 'Dinner', 'Dessert', 'Snack', 'Appetizer', 'Drinks'];
?>
<!DOCTYPE html>
<html lang="en" style="scroll-behavior: smooth">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tasty Bites | Dashboard</title>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="stylesheet" href="assets/media.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Felipa&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    body {
      padding-top: 70px;
    }

    .favorite-btn {
      border: none;
      background: none;
      cursor: pointer;
      font-size: 1.3rem;
      color: #ccc;
      transition: color 0.2s;
    }

    .favorite-btn.filled {
      color: #dc3545;
    }

    .recipe-card {
      cursor: pointer;
      transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .recipe-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .hero-bg {
      position: relative;
      background-image: url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1920');
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

<body>

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
          <li class="nav-item"><a class="nav-link active" href="dashboard.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="my_recipe.php">My Recipes</a></li>
          <li class="nav-item"><a class="nav-link" href="add_recipe.php">Add Recipe</a></li>
        </ul>
        <form class="d-flex mx-auto my-2 my-lg-0" role="search" method="GET" action="dashboard.php">
          <input class="form-control me-2" type="search" name="search_query" placeholder="Search recipes..." aria-label="Search" value="<?php echo htmlspecialchars($searchQuery); ?>">
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

  <div class="hero-bg">
    <div class="container px-4 py-5">
      <div class="row align-items-center g-5 py-5">
        <div class="col-lg-6">
          <h1 class="display-4 fw-bold lh-1 mb-3">Your Favorite Food.</h1>
          <h1 class="display-4 fw-bold lh-1 mb-3">Make it Good.</h1>
          <p class="lead">Discover, cook, and share recipes with the Tasty Bites community. Find inspiration for your next meal and connect with food lovers from around the world.</p>
        </div>
        <div class="col-lg-6">
          <div id="carouselExampleIndicators" class="carousel slide rounded-4 shadow-lg" data-bs-ride="carousel">
            <div class="carousel-inner rounded-4">
              <div class="carousel-item active"><img src="assets/images/hero.png" class="d-block w-100" alt="Hero 1"></div>
              <div class="carousel-item"><img src="assets/images/hero-2.jpg" class="d-block w-100" alt="Hero 2"></div>
              <div class="carousel-item"><img src="assets/images/hero-3.jpg" class="d-block w-100" alt="Hero 3"></div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
          </div>
        </div>
      </div>
    </div>
  </div>


  <section class="discover py-5 px-3 px-lg-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
      <h3 class="mb-3 mb-md-0">Discover New Recipes</h3>
      <form method="GET" class="d-flex align-items-center gap-2">
        <input type="hidden" name="search_query" value="<?php echo htmlspecialchars($searchQuery); ?>">
        <select class="form-select w-auto" name="type" onchange="this.form.submit()">
          <option value="all">All Types</option>
          <?php foreach ($recipeTypes as $type): ?>
            <option value="<?php echo $type; ?>" <?php if ($filterType == $type) echo 'selected'; ?>><?php echo $type; ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>

    <div class="row g-4">
      <?php if ($recipes->num_rows > 0): ?>
        <?php while ($row = $recipes->fetch_assoc()): ?>
          <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card recipe-card h-100">
              <div data-bs-toggle="modal" data-bs-target="#recipeModal<?php echo $row['id']; ?>" style="height:100%;">
                <img src="<?php echo htmlspecialchars($row['image']); ?>" class="card-img-top" alt="Recipe Image" style="height:200px; object-fit:cover;">
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title"><?php echo htmlspecialchars($row['recipe_name']); ?></h5>
                  <span class="badge bg-info mb-2 align-self-start"><?php echo htmlspecialchars($row['recipe_type']); ?></span>
                  <p class="card-text small text-muted flex-grow-1"><?php echo htmlspecialchars(mb_strimwidth($row['description'], 0, 80, '...')); ?></p>
                  <small class="text-muted mt-2">By: <?php echo htmlspecialchars($row['uploaded_by']); ?></small>
                </div>
              </div>
              <div class="card-footer bg-transparent border-0 text-end pb-3">
                <form method="POST" action="dashboard.php?<?php echo http_build_query($_GET); ?>" onclick="event.stopPropagation();">
                  <input type="hidden" name="recipe_id" value="<?php echo $row['id']; ?>">
                  <button type="submit" name="toggle_favorite" class="favorite-btn <?php echo in_array($row['id'], $favorite_ids) ? 'filled' : ''; ?>"><i class="fa-solid fa-heart"></i></button>
                </form>
              </div>
            </div>
          </div>
          <div class="modal fade" id="recipeModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title"><?php echo htmlspecialchars($row['recipe_name']); ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <img src="<?php echo htmlspecialchars($row['image']); ?>" class="img-fluid rounded mb-3" alt="Recipe Image">
                  <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                  <hr>
                  <p><strong>Ingredients:</strong><br><?php echo nl2br(htmlspecialchars($row['ingredients'] ?? 'Not provided.')); ?></p>
                  <hr>
                  <p><strong>Instructions:</strong><br><?php echo nl2br(htmlspecialchars($row['instructions'] ?? 'Not provided.')); ?></p>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-muted col-12">No recipes found matching your criteria.</p>
      <?php endif; ?>
    </div>
  </section>

  <section class="favorites py-5 bg-light px-3 px-lg-5">
    <h3 class="mb-4">My Favorite Recipes</h3>
    <div class="row g-4">
      <?php if ($favorites->num_rows > 0): ?>
        <?php while ($fav = $favorites->fetch_assoc()): ?>
          <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card recipe-card h-100 border-warning" data-bs-toggle="modal" data-bs-target="#recipeModal<?php echo $fav['id']; ?>">
              <img src="<?php echo htmlspecialchars($fav['image']); ?>" class="card-img-top" alt="Recipe Image" style="height:200px; object-fit:cover;">
              <div class="card-body">
                <h5><?php echo htmlspecialchars($fav['recipe_name']); ?></h5>
                <span class="badge bg-info mb-2"><?php echo htmlspecialchars($fav['recipe_type']); ?></span>
                <p class="small text-muted"><?php echo htmlspecialchars(mb_strimwidth($fav['description'], 0, 80, '...')); ?></p>
                <small class="text-muted mt-auto">By: <?php echo htmlspecialchars($fav['uploaded_by']); ?></small>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="px-3">You have no favorite recipes yet. Click the heart icon on any recipe to add it here!</p>
      <?php endif; ?>
    </div>
  </section>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>