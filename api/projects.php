<?php
// api/projects.php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost"); // Restrict to specific domain
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");
require '../config/db.php';

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized. Please log in."]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_SESSION['user_id'];

//  function for error responses
function sendError($code, $message) {
    http_response_code($code);
    echo json_encode(["error" => $message]);
    exit;
}

// function for validation
function validateProjectInput($input) {
    if (!isset($input['name']) || empty(trim($input['name']))) {
        return "Project name is required";
    }
    if (!isset($input['start_date']) || empty(trim($input['start_date']))) {
        return "Start date is required";
    }
    if (!strtotime($input['start_date'])) {
        return "Invalid date format";
    }
    return null;
}

// GET: Retrieve all projects
if ($method == 'GET') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM projects ORDER BY id DESC");
        $stmt->execute();
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($projects);
    } catch (Exception $e) {
        sendError(500, "Failed to retrieve projects");
    }
}

// POST: Create a new project
elseif ($method == 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    // Validate input
    $validation_error = validateProjectInput($input);
    if ($validation_error) {
        sendError(400, $validation_error);
    }

    try {
        $name = trim($input['name']);
        $description = isset($input['description']) ? trim($input['description']) : null;
        $start_date = trim($input['start_date']);
        $status = isset($input['status']) ? trim($input['status']) : 'pending';
        
        // Validate status
        $valid_statuses = ['pending', 'active', 'completed', 'cancelled'];
        if (!in_array($status, $valid_statuses)) {
            $status = 'pending';
        }
        
        $stmt = $pdo->prepare("INSERT INTO projects (name, description, start_date, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $description, $start_date, $status]);
        
        echo json_encode([
            "message" => "Project created successfully",
            "id" => $pdo->lastInsertId()
        ]);
    } catch (Exception $e) {
        sendError(500, "Failed to create project");
    }
}

// 3. PUT: Update a project
elseif ($method == 'PUT') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($input['id'])) {
        sendError(400, "Project ID is required");
    }
    
    try {
        // Build dynamic update query
        $updates = [];
        $params = [];
        
        if (isset($input['name'])) {
            $updates[] = "name = ?";
            $params[] = trim($input['name']);
        }
        if (isset($input['description'])) {
            $updates[] = "description = ?";
            $params[] = trim($input['description']);
        }
        if (isset($input['start_date'])) {
            $updates[] = "start_date = ?";
            $params[] = trim($input['start_date']);
        }
        if (isset($input['status'])) {
            $valid_statuses = ['pending', 'active', 'completed', 'cancelled'];
            if (in_array($input['status'], $valid_statuses)) {
                $updates[] = "status = ?";
                $params[] = trim($input['status']);
            }
        }
        
        if (empty($updates)) {
            sendError(400, "No valid fields to update");
        }
        
        $params[] = $input['id'];
        $query = "UPDATE projects SET " . implode(", ", $updates) . " WHERE id = ?";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        
        if ($stmt->rowCount() === 0) {
            sendError(404, "Project not found");
        }
        
        echo json_encode(["message" => "Project updated successfully"]);
    } catch (Exception $e) {
        sendError(500, "Failed to update project");
    }
}

// 4. DELETE: Delete a project
elseif ($method == 'DELETE') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($input['id'])) {
        sendError(400, "Project ID is required");
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$input['id']]);
        
        if ($stmt->rowCount() === 0) {
            sendError(404, "Project not found");
        }
        
        echo json_encode(["message" => "Project deleted successfully"]);
    } catch (Exception $e) {
        sendError(500, "Failed to delete project");
    }
}

else {
    sendError(405, "Method not allowed");
}
?>