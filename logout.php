<?php
require_once 'config/config.php';
require_once 'classes/User.php';

$db = new Database();
$user = new User($db->connect());
$user->logout();

setFlash('success', 'You have been logged out successfully.');
redirect('index.php');
