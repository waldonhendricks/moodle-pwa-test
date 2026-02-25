<?php
// 1. Set CORS Headers (Allow your PWA to call this script)
header("Access-Control-Allow-Origin: *"); // Change '*' to your PWA's domain in production
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. Configuration (Ideally, load these from a .env file or separate secure config)
// Keep this token strictly on the server!
$moodle_api_endpoint = getenv('MOODLE_API_ENDPOINT') ?: 'https://learn.smartstart.org.za/webservice/rest/server.php';
moodle_ws_token     = getenv('MOODLE_WS_TOKEN') ?: 'abc123xyz...'; // The token from Step 5
$moodle_wants_url    = getenv('MOODLE_WANTS_URL') ?: 'https://learn.smartstart.org.za/my?theme=boost';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed. Use POST."]);
    exit();
}

// 3. Read and parse the incoming JSON from the PWA
$json_input = file_get_contents("php://input");
$request_data = json_decode($json_input, true);

$idnumber = $request_data['idnumber'] ?? null;

if (empty($idnumber)) {
    http_response_code(400);
    echo json_encode(["error" => "ID number is required"]);
    exit();
}

// 4. Prepare parameters for the Moodle API call
$post_params = [
    'wstoken'            => $moodle_ws_token,
    'wsfunction'         => 'auth_userkey_request_login_url',
    'moodlewsrestformat' => 'json',
    'user[idnumber]'     => $idnumber, // Matching the idnumber field exactly
    'wantsurl'           => $moodle_wants_url
];

// 5. Make the secure server-to-server HTTP request using cURL
$ch = curl_init($moodle_api_endpoint);

// http_build_query automatically URL-encodes user[idnumber] and wantsurl safely
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

// Optional: If you have SSL verification issues on your local test VM, uncomment this:
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

$response = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

// 6. Handle server/connection errors
if ($response === false) {
    http_response_code(500);
    echo json_encode(["error" => "cURL Error: " . $curl_error]);
    exit();
}

// 7. Parse the Moodle response
$moodle_data = json_decode($response, true);

// Handle Moodle-specific errors (Moodle often returns HTTP 200 even on failure)
if (isset($moodle_data['exception'])) {
    http_response_code(404); // Using 404 to indicate user/resource issue
    echo json_encode([
        "error" => "Moodle API Error: " . $moodle_data['message'],
        "exception_type" => $moodle_data['exception']
    ]);
    exit();
}

// 8. Success: Return the login URL to the PWA frontend
http_response_code(200);
echo json_encode([
    "loginurl" => $moodle_data['loginurl']
]);
?>
