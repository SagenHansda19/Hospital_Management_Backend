<?php
require_once __DIR__ . '/../config/db.php';

// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");

try {
    // Get and sanitize parameters
    $specialization = isset($_GET['specialization']) ? 
        filter_var($_GET['specialization'], FILTER_SANITIZE_STRING) : null;
    $location = isset($_GET['location']) ? 
        filter_var($_GET['location'], FILTER_SANITIZE_STRING) : null;
    $city = isset($_GET['city']) ? 
        filter_var($_GET['city'], FILTER_SANITIZE_STRING) : null;
    
    // Validate sort parameter
    $allowed_sorts = ['name', 'experience', 'available'];
    $sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sorts) ? 
        $_GET['sort'] : 'name';

    // Base query
    $query = "SELECT id, name, specialization, location, city, 
                     experience, available, photo_url 
              FROM doctors WHERE 1=1";
    $params = [];

    // Add filters
    if ($specialization) {
        $query .= " AND specialization LIKE :specialization";
        $params[':specialization'] = "%$specialization%";
    }
//     if ($location) {
//         $query .= " OR location LIKE :location";
//         $params[':location'] = "%$location%";
//     }
    if ($city) {
        $query .= " AND city LIKE :city";
        $params[':city'] = "%$city%";
    }

    // Add sorting
    $query .= " ORDER BY $sort DESC";

    // Execute query with ASSOC fetch mode to prevent duplicate fields
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process image paths
    foreach ($doctors as &$doctor) {
     // Get original path from database
     $originalPath = $doctor['photo_url'];
     
     // Normalize path (ensure it starts with / but no double slashes)
     $normalizedPath = '/' . ltrim($originalPath, '/');
     // $normalizedPath = str_replace('//', '/', $normalizedPath);
     
     // Construct full path (only prefix if not already present)
     // if (strpos($normalizedPath, '/Hosptal_Management') === false) {
     //     $normalizedPath = '/Hosptal_Management' . $normalizedPath;
     // }
     
     $doctor['photo_url'] = $normalizedPath;
 }
 unset($doctor); // Break the reference

    // Prepare response
    $response = [
        'success' => true,
        'data' => $doctors,
        'count' => count($doctors)
    ];

    // Send JSON response
    echo json_encode($response);

} catch(PDOException $e) {
    // Error handling
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => "Database error",
        'message' => $e->getMessage()
    ]);
}
?>