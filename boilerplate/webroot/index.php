<?php
/**
 * ZestPHP Web Application
 * Main entry point
 */

// Debug configuration
$GLOBALS["DEBUG"] = true;
$GLOBALS["DEBUG_MODE"] = "file";
$GLOBALS["DEBUG_MODE"] = "stdout";
$GLOBALS["ERROR_LOG"] = true;

// Define root directory
define("DIR_ROOT", realpath(dirname(__FILE__) . "/../.."));

// Include ZestPHP Web Framework
include DIR_ROOT . "/zest-fw-web/zest-fw-web.php";

// Example route with template rendering
if ($a = route("^/$")) {
    // Render the home template
    display_template('home.twig', [
        'title' => 'Welcome to ZestPHP Web',
        'content' => 'This is a sample ZestPHP Web application.'
    ]);
    exit;
}

// Example API route
if ($a = route("^/api/hello$")) {
    header('Content-Type: application/json');
    echo json_encode([
        'message' => 'Hello from ZestPHP Web API',
        'timestamp' => time()
    ]);
    exit;
}

// Run the API engine for JSON/RAW requests
run_api_engine();

// Return 404 if no route matched
http_response_code(404);
display_template('404.twig', [
    'title' => 'Page Not Found'
]);
exit;
