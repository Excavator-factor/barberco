<?php
session_start();
$_SESSION['role'] = 'pelanggan';
$_SESSION['id_user'] = 1;
$_SESSION['username'] = 'test';
require 'dashboard.php';
