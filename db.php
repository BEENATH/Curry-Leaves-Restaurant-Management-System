<?php
/**
 * copyright all recieved BEE.LK
 * GITHUB - https://github.com/BEENATH
 * LinkedIn - www.linkedin.com/in/beenathmansika
 */
$host = "localhost";
$user = "root";
$password = "";
$dbname = "restaurant_db";


date_default_timezone_set("Asia/Kolkata");

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$now = new DateTime();
$mins = $now->getOffset() / 60;
$sgn = ($mins < 0 ? -1 : 1);
$mins = abs($mins);
$hrs = floor($mins / 60);
$mins -= $hrs * 60;
$offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);
$conn->query("SET time_zone='$offset'");



function getReservations($status = null) {
    global $conn;
    $sql = "SELECT r.*, 
                   CONCAT(r.first_name, ' ', r.last_name) as full_name,
                   DATE_FORMAT(r.reservation_date, '%Y-%m-%d') as formatted_date,
                   TIME_FORMAT(r.reservation_time, '%h:%i %p') as formatted_time,
                   COUNT(rt.reservation_table_id) as table_count
            FROM reservations r
            LEFT JOIN reservation_tables rt ON r.reservation_id = rt.reservation_id";
    
    if ($status) {
        $sql .= " WHERE r.status = ?";
    }
    
    $sql .= " GROUP BY r.reservation_id ORDER BY r.reservation_date DESC, r.reservation_time DESC";
    
    $stmt = $conn->prepare($sql);
    if ($status) {
        $stmt->bind_param("s", $status);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getContacts($status = null) {
    global $conn;
    $sql = "SELECT * FROM contacts";
    
    if ($status) {
        $sql .= " WHERE status = ?";
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($sql);
    if ($status) {
        $stmt->bind_param("s", $status);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getUnreadContactsCount() {
    global $conn;
    $sql = "SELECT COUNT(*) as count FROM contacts WHERE status = 'unread'";
    $result = $conn->query($sql);
    return $result->fetch_assoc()['count'];
}

function getPendingReservationsCount() {
    global $conn;
    $sql = "SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'";
    $result = $conn->query($sql);
    return $result->fetch_assoc()['count'];
}
?>