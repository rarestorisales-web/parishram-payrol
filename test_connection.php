<?php
// Connection Test File
header("Content-Type: application/json");

$host = "localhost";
$db_name = "u768023141_u123_payroll"; 
$username = "u768023141_u123_admin";
$password = "Raja#184";

$result = [
    "connection" => "Testing...",
    "tables" => [],
    "sample_employees" => []
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $result["connection"] = "✅ Connected Successfully";
    
    // Check tables
    $tables = ['employees', 'history', 'submissions', 'settings'];
    foreach($tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        $exists = $stmt->fetch();
        $result["tables"][$table] = $exists ? "✅ EXISTS" : "❌ MISSING";
    }
    
    // Get sample employees
    $stmt = $pdo->query("SELECT id, name, cardNo, company, salary FROM employees LIMIT 5");
    $result["sample_employees"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result["total_employees"] = $pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn();
    
} catch (PDOException $e) {
    $result["connection"] = "❌ Connection Failed: " . $e->getMessage();
    $result["error"] = true;
}

echo json_encode($result, JSON_PRETTY_PRINT);
?>
