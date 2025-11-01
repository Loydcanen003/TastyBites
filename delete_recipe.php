<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$recipe_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($recipe_id > 0) {
    // Security check: Prepare a statement to delete the recipe ONLY if the ID and username match
    $stmt = $conn->prepare("DELETE FROM recipes WHERE id = ? AND uploaded_by = ?");
    $stmt->bind_param("is", $recipe_id, $username);
    $stmt->execute();
    $stmt->close();
}

// Redirect back to the my_recipe page
header("Location: my_recipe.php?status=deleted");
exit();
