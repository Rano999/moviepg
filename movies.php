<?php
include 'db_config.php';

header("Access-Control-Allow-Origin: *"); // Allow all origins
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); // Allow specific HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

$type = $_GET['type'] ?? 'movies'; // Default to movies
$page = $_GET['page'] ?? 1;
$limit = 20; // Number of items per page
$offset = ($page - 1) * $limit;

// Validate type
if ($type === 'movie') {
    $stmt = $conn->prepare("SELECT * FROM movies LIMIT :limit OFFSET :offset");
} elseif ($type === 'tv') {
    $stmt = $conn->prepare("SELECT * FROM tv_series LIMIT :limit OFFSET :offset");
} else {
    echo json_encode(['error' => 'Invalid type']);
    exit;
}

$stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
$stmt->execute();

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['results' => $results]);
?>
