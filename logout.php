<?php
require_once 'config.php';

// 1. Clear session data
$_SESSION = [];

// 2. Kill the session
session_destroy();

// 3. Standard PHP redirect
header("Location: login.php");
exit;