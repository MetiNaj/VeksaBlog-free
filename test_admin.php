<?php
// test_admin.php
session_start();
require 'db.php';
echo "Testing admin functionality...<br>";
try {
    $links = $db->query("SELECT * FROM links")->fetchAll(PDO::FETCH_ASSOC);
    echo "Links in database:<br><pre>";
    print_r($links);
    echo "</pre>";
    $users = $db->query("SELECT username FROM users")->fetchAll(PDO::FETCH_ASSOC);
    echo "Users in database:<br><pre>";
    print_r($users);
    echo "</pre>";
    echo "Session status: " . (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] ? "Logged in" : "Not logged in");
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>