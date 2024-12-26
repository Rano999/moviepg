<?php
// Include database configuration
include('db_config.php');
header("Access-Control-Allow-Origin: *"); // Allow all origins
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); // Allow specific HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
// Get search query and page number from the request
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Results per page
$resultsPerPage = 10;
$offset = ($page - 1) * $resultsPerPage;

// Initialize the response
$response = [];

if (!empty($searchQuery)) {
    try {
        // Fetch movies matching the search query
        $stmt = $conn->prepare("
            SELECT id, name, thumbnail_path, 'movie' AS media_type
            FROM movies
            WHERE name LIKE :searchQuery
            LIMIT :offset, :limit
        ");
        $stmt->bindValue(':searchQuery', '%' . $searchQuery . '%', PDO::PARAM_STR);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $resultsPerPage, PDO::PARAM_INT);
        $stmt->execute();
        $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch TV shows matching the search query
        $stmt = $conn->prepare("
            SELECT id, name, thumbnail_path, 'tv' AS media_type
            FROM tv_series
            WHERE name LIKE :searchQuery
            LIMIT :offset, :limit
        ");
        $stmt->bindValue(':searchQuery', '%' . $searchQuery . '%', PDO::PARAM_STR);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $resultsPerPage, PDO::PARAM_INT);
        $stmt->execute();
        $tvShows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Combine movies and TV shows into a single response
        $response = array_merge($movies, $tvShows);

    } catch (PDOException $e) {
        // Log the error and send an empty response
        error_log("Error fetching search results: " . $e->getMessage());
    }
}

// Return the response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
