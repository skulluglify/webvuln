<?php

if (PHP_VERSION_ID < 80200) {

    // warning!
    header('HTTP/1.1 500 Internal Server Error');
    $message = 'require php_version above php8.2';
    echo $message;

    // trigger error message!
    trigger_error(message: $message, error_level: E_USER_ERROR);
}

require_once __DIR__ . '/middlewares/example.php';
