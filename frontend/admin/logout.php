<?php

require_once __DIR__ . '/../../backend/bootstrap.php';

use ConnectMyUni\Middleware\AuthMiddleware;

// Logout user
AuthMiddleware::logout();

// Redirect to login page
header('Location: login.php');
exit;
