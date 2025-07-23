<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

	global $con;

	// Database configuration for InfinityFree
	$hostname = 'sql105.infinityfree.com'; 	// Your InfinityFree database host
	$user = 'if0_38842939'; 		// Your InfinityFree database username
	$password = 'NamiHarshu866'; 	// Your InfinityFree database password
	$dbname = 'if0_38842939_club_db'; 	// Your InfinityFree database name

	// Create connection with error handling
	$con = new mysqli($hostname, $user, $password, $dbname);

	if ($con->connect_error) {
	    // Log error but don't show details to user
	    error_log("Database connection failed: " . $con->connect_error);
	    die("Connection failed: " . $con->connect_error);
	}

	// Set charset to ensure proper encoding
	$con->set_charset("utf8mb4");