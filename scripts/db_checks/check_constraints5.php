<?php
require_once 'includes/core/config.php';

$res = $conn->query("SHOW CREATE TABLE transactions");
if ($res) {
    $row = $res->fetch_assoc();
    echo $row['Create Table'] . "\n\n";
}

$res = $conn->query("SHOW CREATE TABLE chat_messages");
if ($res) {
    $row = $res->fetch_assoc();
    echo $row['Create Table'] . "\n\n";
}

$res = $conn->query("SHOW CREATE TABLE caretaker_payouts");
if ($res) {
    $row = $res->fetch_assoc();
    echo $row['Create Table'] . "\n\n";
}

$res = $conn->query("SHOW CREATE TABLE caretaker_earnings");
if ($res) {
    $row = $res->fetch_assoc();
    echo $row['Create Table'] . "\n\n";
}

// Log 5 lines of error log
if(file_exists('C:\\xampp\\php\\logs\\php_error_log')) {
    echo "\n\nRecent PHP Errors:\n";
    system('tail -n 5 C:\xampp\php\logs\php_error_log');
}
?>
