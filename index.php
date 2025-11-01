<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en" style="scroll-behavior: smooth">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tasty Bites | Welcome</title>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="stylesheet" href="assets/media.css">
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet" />
  <link
    href="https://fonts.googleapis.com/css2?family=Felipa&display=swap"
    rel="stylesheet" />
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr"
    crossorigin="anonymous" />
</head>

<body class="position-relative">
  <nav class="p-2 d-flex d-sm-none flex-row align-items-center justify-content-between">
    <h1>Tasty Bites</h1>
    <div class="d-flex d-sm-none flex-row align-items-center gap-3">
      <img src="assets/icons/search-alt-svgrepo-com.svg" alt="Search Icon" class="search" />
      <img src="assets/icons/hamburger-menu-svgrepo-com.svg" alt="Hamburger icon" class="hamburger" />
    </div>
  </nav>

  <nav class="px-5 py-2 d-none d-sm-flex nav-xxl align-items-center justify-content-between position-fixed top-0 z-3 bg-light-subtle">
    <div class="flex-row d-flex align-items-center gap-4 title-div">
      <h1>Tasty Bites</h1>
      <div class="search-div px-2">
        <input type="text" name="search" id="search-bar" placeholder="Find a recipe..." />
        <img src="assets/icons/search-alt-svgrepo-com.svg" alt="Search icons" class="search" />
      </div>
    </div>
    <div class="d-flex align-items-center gap-3">
      <a href="index.php" class="tabs">Home</a>
      <a href="login.php" class="tabs">Login</a>
      <a href="register.php" class="btn btn-warning">Sign Up</a>
    </div>
  </nav>

  <div class="homer" id="home">
    <div class="homer-item-1">
      <div class="homer-inside-item-1">
        <h1 class="homer-h1">Flavor Awaits.</h1>
        <h1 class="homer-h1">Craft Your Story.</h1>
        <p class="homer-para">
          Welcome to a community where every dish tells a tale. Discover thousands of recipes from home cooks around the globe, share your own culinary creations, and find inspiration for your next unforgettable meal. Your culinary adventure starts now.
        </p>
      </div>
      <div class="homer-inside-item-2 carousel slide" id="carouselExampleIndicators">
        <div class="carousel-indicators">
          <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
          <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
          <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner mt-4 rounded-4">
          <div class="carousel-item active">
            <img src="assets/images/hero.png" class="d-block w-100" alt="A delicious pasta dish" />
          </div>
          <div class="carousel-item">
            <img src="assets/images/hero-2.jpg" class="d-block w-100" alt="Freshly baked bread" />
          </div>
          <div class="carousel-item">
            <img src="assets/images/hero-3.jpg" class="d-block w-100" alt="A colorful salad" />
          </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Next</span>
        </button>
      </div>
    </div>
  </div>

  <section class="discover">
    <h1 style="font-family: 'Poppins'" class="fs-3">Trending Recipes</h1>
    <div class="recipes">
      <div class="d-flex flex-column recipe-card">
        <img class="recipe-item" src="assets/images/recipe-1.png" alt="Sushi Platter" />
        <h1 style="font-family: 'Poppins'" class="fs-5 mt-2">Spicy Tuna Roll</h1>
        <p>Master the art of homemade sushi with this simple guide to a classic, fiery favorite.</p>
      </div>
      <div class="d-flex flex-column recipe-card">
        <img class="recipe-item" src="assets/images/recipe-2.png" alt="Pasta Dish" />
        <h1 style="font-family: 'Poppins'" class="fs-5 mt-2">Creamy Tomato Pasta</h1>
        <p>A rich and velvety pasta dish that comes together in under 30 minutes. Perfect for any night of the week.</p>
      </div>
      <div class="d-flex flex-column recipe-card">
        <img class="recipe-item" src="assets/images/recipe-3.png" alt="Fresh Salad" />
        <h1 style="font-family: 'Poppins'" class="fs-5 mt-2">Avocado Summer Salad</h1>
        <p>Vibrant, crisp, and packed with nutrients. This isn't just a salad, it's a celebration of freshness.</p>
      </div>
      <div class="d-flex flex-column recipe-card">
        <img class="recipe-item" src="assets/images/recipe-4.png" alt="Chocolate Cake" />
        <h1 style="font-family: 'Poppins'" class="fs-5 mt-2">Decadent Chocolate Cake</h1>
        <p>Indulge your sweet tooth with a moist, fudgy dessert that's surprisingly easy to bake from scratch.</p>
      </div>
    </div>

    <h1 style="font-family: 'Poppins'; margin-top: 3rem;" class="fs-3">Local Favorites from the Philippines</h1>
    <div class="uploaded mb-xl-5">
      <div class="uploaded-items uploaded-items-1">
        <div class="uploaded-inside-items">
          <h1>Kaldereta</h1>
          <p>A rich, savory tomato-based beef stew, slow-cooked to perfection. This hearty Filipino classic is a true celebration of flavor.</p>
        </div>
      </div>
      <div class="uploaded-items uploaded-items-2">
        <div class="uploaded-inside-items">
          <h1>Adobo</h1>
          <p>The quintessential Filipino comfort food. A perfect balance of soy sauce, vinegar, and garlic makes this dish simply unforgettable.</p>
        </div>
      </div>
      <div class="uploaded-items uploaded-items-3">
        <div class="uploaded-inside-items">
          <h1>Sinigang na Baboy</h1>
          <p>Experience the delightful sour and savory notes of this classic tamarind broth, brimming with tender pork and fresh vegetables.</p>
        </div>
      </div>
    </div>
  </section>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const recipeCards = document.querySelectorAll(".recipe-card");
      recipeCards.forEach((card) => {
        card.addEventListener("click", function() {
          this.classList.add("click-animation");
          setTimeout(() => {
            this.classList.remove("click-animation");
          }, 600);
        });
      });
    });
  </script>
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
    crossorigin="anonymous"></script>
</body>

</html>