<?php
require_once __DIR__ . '/../config/db.php';

$job_id        = $_POST['job_id'];
$client_name   = $_POST['client_name'];
$contract_type = $_POST['contract_type'];
$start_date    = $_POST['start_date'];
$end_date      = $_POST['end_date'];
$description   = $_POST['description'];

$stmt = $conn->prepare("INSERT INTO contracts (job_id, client_name, contract_type, start_date, end_date, description) 
VALUES (?, ?, ?, ?, ?, ?)");

$stmt->bind_param("ssssss", $job_id, $client_name, $contract_type, $start_date, $end_date, $description);

if ($stmt->execute()) {
    header("Location: contract-list.php?success=1");
    exit();
} else {
    echo "Error: " . $stmt->error;
}
