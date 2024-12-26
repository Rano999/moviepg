<?php
include 'db_config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

$type = $_GET['type'] ?? 'movie'; // Default to 'movie'
$limit = 50; // Limit per fetch

// Predefined genre sections
$sections = [
    "actionAdventure" => ["Action", "Adventure"],
    "thrillerDrama" => ["Thriller", "Drama"],
    "comedySciFi" => ["Comedy", "Sci-Fi"],
    "horrorMystery" => ["Horror", "Mystery"],
    "dramaRomance" => ["Drama", "Romance"],
];

$response = [];

try {
    if ($type === 'movie') {
        $stmt = $conn->prepare("SELECT * FROM movies LIMIT :limit");
    } elseif ($type === 'tv') {
        $stmt = $conn->prepare("SELECT * FROM tv_series LIMIT :limit");
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid type']);
        exit;
    }

    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();

    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialize response sections
    foreach ($sections as $key => $genres) {
        $response[$key] = [];
    }

    // Group items into sections
    foreach ($items as $item) {
        if (empty($item['genres'])) continue; // Skip items with no genres

        $itemGenres = array_map('trim', explode(',', $item['genres'])); // Split genres
        foreach ($sections as $key => $sectionGenres) {
            if (array_intersect($itemGenres, $sectionGenres)) {
                $response[$key][] = $item; // Add item to the matching section
            }
        }
    }

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
?>
