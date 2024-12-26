<?php
// Include database connection
include('db_config.php');

// Get the type (movie or tv) and ID from the request
$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
header("Access-Control-Allow-Origin: *"); // Allow all origins
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); // Allow specific HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With"); // Allow specific headers
// Initialize response
$response = [
    "details" => false,
    "download_links" => []
];

// Validate the inputs
if (empty($type) || $id === 0) {
    echo json_encode($response);
    exit;
}

try {
    if ($type === "movie") {
        // Fetch movie details
        $stmt = $conn->prepare("SELECT * FROM movies WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $movie = $stmt->fetch(PDO::FETCH_ASSOC);

        // If movie found, fetch its download links
        if ($movie) {
            $stmt_links = $conn->prepare("SELECT * FROM download_links WHERE movie_id = :id");
            $stmt_links->execute(['id' => $id]);
            $download_links = $stmt_links->fetchAll(PDO::FETCH_ASSOC);

            // Set response
            $response['details'] = $movie;
            $response['download_links'] = $download_links;
        }
    } elseif ($type === "tv") {
        // Fetch TV series details
        $stmt = $conn->prepare("SELECT * FROM tv_series WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $tv_series = $stmt->fetch(PDO::FETCH_ASSOC);

        // If TV series found, fetch its download links
        if ($tv_series) {
            $stmt_links = $conn->prepare("SELECT * FROM series_link WHERE series_id = :id");
            $stmt_links->execute(['id' => $id]);
            $download_links = $stmt_links->fetchAll(PDO::FETCH_ASSOC);

            // Set response
            $response['details'] = $tv_series;
            $response['download_links'] = $download_links;
        }
    }
} catch (PDOException $e) {
    error_log("Error fetching details: " . $e->getMessage());
    // Optional: Set error response
    $response['error'] = "An error occurred while fetching details.";
}

// Return the response as JSON
header("Content-Type: application/json");
echo json_encode($response);
?>
