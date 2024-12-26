<?php
// Include the database connection
include('db_config.php');
header("Access-Control-Allow-Origin: *"); // Allow all origins
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); // Allow specific HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With"); // Allow specific headers
// Fetch all categories
$categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// Initialize arrays for movies and TV shows
$movies_by_category = [];
$tv_shows_by_category = [];

foreach ($categories as $category) {
    $category_id = $category['id'];

    // Fetch movies belonging to the category
    $stmt = $conn->prepare("SELECT * FROM movies WHERE category_id = :category_id");
    $stmt->execute(['category_id' => $category_id]);
    $movies_by_category[$category['name']] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch TV shows belonging to the category
    $stmt_tv = $conn->prepare("SELECT * FROM tv_series WHERE category_id = :category_id");
    $stmt_tv->execute(['category_id' => $category_id]);
    $tv_shows_by_category[$category['name']] = $stmt_tv->fetchAll(PDO::FETCH_ASSOC);
}

// Combine categories with their respective movies and TV shows
$data = [];
foreach ($categories as $category) {
    $data[] = [
        'category_name' => $category['name'],
        'poster_path' => $category['poster_path'],
        'movies' => $movies_by_category[$category['name']] ?? [],
        'tv_shows' => $tv_shows_by_category[$category['name']] ?? []
    ];
}

// Return the data as JSON
header("Content-Type: application/json");
echo json_encode($data);
?>
