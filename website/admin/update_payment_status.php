<?php
require_once 'includes/db_connect.php';

$id = intval($_POST['id']);
$status = $_POST['status'];

$result = $conn->query("UPDATE payments SET status='$status' WHERE id=$id");

echo json_encode(['success' => $result]);