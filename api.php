<?php
// =====================================================
// PAYROLL MANAGEMENT SYSTEM - API. PHP
// =====================================================

ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// =====================================================
// DATABASE CONFIGURATION
// =====================================================
$host = "localhost"; 
$db_name = "u768023141_u123_payroll"; 
$username = "u768023141_u123_admin";
$password = "Raja#184";

// =====================================================
// DATABASE CONNECTION
// =====================================================
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db_name;charset=utf8mb4", 
        $username, 
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode([
        "error" => "Database Connection Failed", 
        "message" => $e->getMessage()
    ]));
}

// =====================================================
// GET ACTION
// =====================================================
$action = isset($_GET['action']) ? trim($_GET['action']) : '';

// Log all requests
error_log("API Request: action=$action, method=" . $_SERVER['REQUEST_METHOD']);

// =====================================================
// ROUTE HANDLER
// =====================================================
switch ($action) {
    
    // =====================================================
    // GET ALL DATA
    // =====================================================
    case 'get_all':   
        try {
            // Get employees
            $employees = $pdo->query("SELECT * FROM employees ORDER BY name ASC")->fetchAll();
            
            // Convert numeric strings to actual numbers
            foreach($employees as &$emp) {
                $numericFields = ['id', 'days', 'hrs', 'ph', 'salary', 'wages', 'bonus', 'hra', 'ot', 'misc', 'rate', 'rate_2', 'days_2', 'pf', 'esic', 'gwlf', 'pt', 'advance', 'food', 'trn', 'rr', 'leave_amt', 'pf_2', 'trn_2'];
                
                foreach($emp as $key => &$val) {
                    if(in_array($key, $numericFields)) {
                        if($val === '' || $val === null) {
                            $val = 0;
                        } else {
                            $val = strpos($val, '.') !== false ? (float)$val : (int)$val;
                        }
                    }
                }
            }
            
            // Get history batches
            $history = [];
            $historyStmt = $pdo->query("SELECT * FROM history ORDER BY id DESC");
            if($historyStmt) {
                foreach($historyStmt->fetchAll() as $h) {
                    $h['data'] = json_decode($h['data'] ?? '[]', true);
                    $history[] = $h;
                }
            }
            
            // Get submissions
            $submissions = [];
            $submissionsStmt = $pdo->query("SELECT * FROM submissions ORDER BY id DESC");
            if($submissionsStmt) {
                foreach($submissionsStmt->fetchAll() as $s) {
                    $s['data'] = json_decode($s['data'] ?? '[]', true);
                    $submissions[] = $s;
                }
            }
            
            // Get settings/config
            $settingsStmt = $pdo->query("SELECT config FROM settings WHERE id = 1");
            $settingsJson = $settingsStmt ?  $settingsStmt->fetchColumn() : null;
            
            $defaultConfig = [
                "appSettings" => [
                    "companyName" => "Parishram Enterprises",
                    "appName" => "Payroll Manager"
                ],
                "users" => [
                    [
                        "username" => "Raja",
                        "password" => "Raja#184",
                        "role" => "Super Admin",
                        "permissions" => [
                            "dashboard" => true,
                            "payroll" => true,
                            "history" => true,
                            "submissions" => true,
                            "settings" => true
                        ]
                    ]
                ]
            ];

            http_response_code(200);
            echo json_encode([
                "employees" => $employees,
                "history" => $history,
                "submissions" => $submissions,
                "config" => $settingsJson ?  json_decode($settingsJson, true) : $defaultConfig,
                "activityLog" => []
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) { 
            error_log("get_all error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["error" => "Failed to retrieve data", "message" => $e->getMessage()]); 
        }
        break;

    // =====================================================
    // SAVE EMPLOYEE
    // =====================================================
    case 'save_employee':   
        try {
            $input = file_get_contents("php://input");
            error_log("save_employee raw input: " . substr($input, 0, 200));
            
            $d = json_decode($input, true);
            
            if (! $d) { 
                throw new Exception("No JSON data received");
            }
            
            if (!isset($d['id'])) {
                throw new Exception("Missing employee ID");
            }
            
            if (!isset($d['name']) || empty(trim($d['name']))) {
                throw new Exception("Employee name is required");
            }
            
            error_log("save_employee data: " . json_encode($d));
            
            // Check if employee exists
            $checkStmt = $pdo->prepare("SELECT id FROM employees WHERE id = ? ");
            $checkStmt->execute([$d['id']]);
            $exists = $checkStmt->fetch() ?  true : false;
            
            // Define allowed fields
            $allowedFields = [
                'name', 'cardNo', 'company', 'contactNo', 
                'salary', 'wages', 'bonus', 'hra', 'ot', 'misc',
                'days', 'hrs', 'ph', 'rate', 'rate_2', 'days_2',
                'pf', 'esic', 'gwlf', 'pt', 'advance', 'food', 'trn', 'rr', 'leave_amt', 'pf_2', 'trn_2',
                'uan', 'esicNo', 'agt', 'accountNumber', 'ifscCode'
            ];
            
            if ($exists) {
                // UPDATE EXISTING EMPLOYEE
                $setClauses = [];
                $values = [];
                
                foreach($allowedFields as $field) {
                    if(array_key_exists($field, $d)) {
                        $setClauses[] = "`$field` = ?";
                        $values[] = $d[$field] !== null ? $d[$field] :  '';
                    }
                }
                
                if(empty($setClauses)) {
                    throw new Exception("No fields to update");
                }
                
                $values[] = $d['id'];
                $sql = "UPDATE employees SET " . implode(", ", $setClauses) . " WHERE id = ?";
                
                error_log("UPDATE SQL: " . $sql);
                error_log("UPDATE VALUES: " .  json_encode($values));
                
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute($values);
                
                if(! $result) {
                    throw new Exception("Failed to update employee");
                }
                
                $message = "Employee updated successfully";
                
            } else {
                // INSERT NEW EMPLOYEE
                $cols = ['id'];
                $placeholders = ['?'];
                $values = [$d['id']];
                
                foreach($allowedFields as $field) {
                    if(array_key_exists($field, $d)) {
                        $cols[] = "`$field`";
                        $placeholders[] = "?";
                        $values[] = $d[$field] !== null ? $d[$field] : '';
                    }
                }
                
                $sql = "INSERT INTO employees (" . implode(", ", $cols) . ") VALUES (" . implode(", ", $placeholders) . ")";
                
                error_log("INSERT SQL: " . $sql);
                error_log("INSERT VALUES: " . json_encode($values));
                
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute($values);
                
                if(!$result) {
                    throw new Exception("Failed to create employee");
                }
                
                $message = "Employee created successfully";
            }
            
            http_response_code(200);
            echo json_encode([
                "status" => "success", 
                "id" => $d['id'],
                "message" => $message
            ]);
            
        } catch (Exception $e) {
            error_log("save_employee error: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
            http_response_code(400);
            echo json_encode([
                "error" => "Failed to save employee",
                "message" => $e->getMessage()
            ]);
        }
        break;

    // =====================================================
    // DELETE EMPLOYEE
    // =====================================================
    case 'delete_employee':  
        try {
            $id = isset($_GET['id']) ? $_GET['id'] : null;
            
            if (!$id) {
                throw new Exception("No employee ID provided");
            }
            
            error_log("Deleting employee ID: $id");
            
            $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if(!$result) {
                throw new Exception("Failed to delete employee");
            }
            
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Employee deleted successfully"
            ]);
        } catch (Exception $e) {
            error_log("delete_employee error: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                "error" => "Failed to delete employee",
                "message" => $e->getMessage()
            ]);
        }
        break;

    // =====================================================
    // SAVE BATCH
    // =====================================================
    case 'save_batch': 
        try {
            $input = file_get_contents("php://input");
            error_log("save_batch input: " . substr($input, 0, 200));
            
            $d = json_decode($input, true);
            
            if(! $d) {
                throw new Exception("No batch data received");
            }
            
            $name = isset($d['name']) ? $d['name'] : 'Batch ' . date('Y-m-d');
            $date = isset($d['date']) ? $d['date'] : date('Y-m-d');
            $batchData = json_encode($d['data'] ?? []);
            
            error_log("Saving batch: name=$name, date=$date, data_size=" . strlen($batchData));
            
            $stmt = $pdo->prepare("INSERT INTO history (name, date, data) VALUES (?, ?, ?)");
            $result = $stmt->execute([$name, $date, $batchData]);
            
            if(!$result) {
                throw new Exception("Failed to save batch");
            }
            
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Batch saved successfully"
            ]);
        } catch (Exception $e) {
            error_log("save_batch error: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                "error" => "Failed to save batch",
                "message" => $e->getMessage()
            ]);
        }
        break;

    // =====================================================
    // DELETE BATCH
    // =====================================================
    case 'delete_batch':
        try {
            $id = isset($_GET['id']) ? $_GET['id'] : null;
            
            if (!$id) {
                throw new Exception("No batch ID provided");
            }
            
            error_log("Deleting batch ID: $id");
            
            $stmt = $pdo->prepare("DELETE FROM history WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if(!$result) {
                throw new Exception("Failed to delete batch");
            }
            
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Batch deleted successfully"
            ]);
        } catch (Exception $e) {
            error_log("delete_batch error: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                "error" => "Failed to delete batch",
                "message" => $e->getMessage()
            ]);
        }
        break;

    // =====================================================
    // SAVE SUBMISSION (APPLICATION FORM)
    // =====================================================
    case 'save_submission':  
        try {
            $input = file_get_contents("php://input");
            error_log("save_submission input: " . substr($input, 0, 200));
            
            $d = json_decode($input, true);
            
            if(! $d) {
                throw new Exception("No submission data received");
            }
            
            $companyName = isset($d['companyName']) ? $d['companyName'] : 'Parishram';
            $date = isset($d['date']) ? $d['date'] : date('Y-m-d');
            $submissionData = json_encode($d['data'] ?? []);
            
            error_log("Saving submission: company=$companyName, date=$date");
            
            if (isset($d['id']) && ! empty($d['id'])) {
                // UPDATE
                $stmt = $pdo->prepare("UPDATE submissions SET companyName = ?, date = ?, data = ? WHERE id = ? ");
                $result = $stmt->execute([$companyName, $date, $submissionData, $d['id']]);
                $message = "Submission updated successfully";
            } else {
                // INSERT
                $stmt = $pdo->prepare("INSERT INTO submissions (companyName, date, data) VALUES (?, ?, ?)");
                $result = $stmt->execute([$companyName, $date, $submissionData]);
                $message = "Submission created successfully";
            }
            
            if(!$result) {
                throw new Exception("Failed to save submission");
            }
            
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => $message
            ]);
        } catch (Exception $e) {
            error_log("save_submission error:  " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                "error" => "Failed to save submission",
                "message" => $e->getMessage()
            ]);
        }
        break;

    // =====================================================
    // DELETE SUBMISSION
    // =====================================================
    case 'delete_submission':  
        try {
            $id = isset($_GET['id']) ? $_GET['id'] : null;
            
            if (!$id) {
                throw new Exception("No submission ID provided");
            }
            
            error_log("Deleting submission ID: $id");
            
            $stmt = $pdo->prepare("DELETE FROM submissions WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if(!$result) {
                throw new Exception("Failed to delete submission");
            }
            
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Submission deleted successfully"
            ]);
        } catch (Exception $e) {
            error_log("delete_submission error: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                "error" => "Failed to delete submission",
                "message" => $e->getMessage()
            ]);
        }
        break;

    // =====================================================
    // UPDATE CONFIG
    // =====================================================
    case 'update_config': 
        try {
            $input = file_get_contents("php://input");
            error_log("update_config input: " . substr($input, 0, 200));
            
            if (! $input) {
                throw new Exception("No config data provided");
            }
            
            // Validate JSON
            $decoded = json_decode($input, true);
            if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON:  " . json_last_error_msg());
            }
            
            $stmt = $pdo->prepare("UPDATE settings SET config = ? WHERE id = 1");
            $result = $stmt->execute([$input]);
            
            if(!$result) {
                throw new Exception("Failed to update config");
            }
            
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Configuration updated successfully"
            ]);
        } catch (Exception $e) {
            error_log("update_config error: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                "error" => "Failed to update config",
                "message" => $e->getMessage()
            ]);
        }
        break;
        
    // =====================================================
    // INVALID ACTION
    // =====================================================
    default:
        http_response_code(400);
        echo json_encode([
            "error" => "Invalid or missing action",
            "receivedAction" => $action,
            "availableActions" => [
                "get_all",
                "save_employee",
                "delete_employee",
                "save_batch",
                "delete_batch",
                "save_submission",
                "delete_submission",
                "update_config"
            ]
        ]);
}

// Close database connection
$pdo = null;
?>