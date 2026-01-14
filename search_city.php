<?php
include 'includes/config.php';

$q = $_GET['q'] ?? '';

$stmt = $conn->prepare("
    SELECT name, province 
    FROM cities 
    WHERE name LIKE ?
    ORDER BY is_popular DESC, name ASC
    LIMIT 10
");

$like = "%$q%";
$stmt->bind_param("s", $like);
$stmt->execute();

$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
