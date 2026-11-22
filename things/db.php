<?php
$host = 'localhost';
$databaseName = 'porsche_store';
$databaseUser = 'root';
$databasePassword = '';

$database = new PDO("mysql:host=$host;dbname=$databaseName;charset=utf8mb4", $databaseUser, $databasePassword);
$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$database->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

function safe($text)
{
    return htmlspecialchars((string) $text);
}