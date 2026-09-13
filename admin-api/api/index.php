<?php

// Fix Vercel path rewrites for Laravel routing
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';

if (isset($_SERVER['HTTP_X_FORWARDED_URI'])) {
    $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_FORWARDED_URI'];
}

// Forward to Laravel entry point
require __DIR__ . '/../public/index.php';