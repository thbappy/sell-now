<?php
/**
 * Router for PHP Built-in Server
 * This file routes all requests to index.php
 */

$file = __DIR__ . $_SERVER["REQUEST_URI"];

// If the requested file or directory exists and is not a directory, serve it
if (is_file($file)) {
    return false;
}

// Otherwise, route to index.php
require __DIR__ . '/index.php';
