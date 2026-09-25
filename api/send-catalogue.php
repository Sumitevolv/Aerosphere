<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$config = require __DIR__ . '/config.php';
$apiKey = trim((string)($config['resend_api_key'] ?? ''));
$from   = trim((string)($config['from_email'] ?? ''));
$notify = trim((string)($config['notify_email'] ?? ''));

if ($apiKey === '' || strpos($apiKey, 'PASTE_') === 0 || $from === '' || strpos($from, 'PASTE_') !== false) {
    http_response_code(500);
    echo json_encode(['error' => 'Email delivery is not configured yet.']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '', true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request data.']);
    exit;
}

$name     = trim((string)($data['name'] ?? ''));
$company  = trim((string)($data['company'] ?? ''));
$email    = trim((string)($data['email'] ?? ''));
$category = trim((string)($data['category'] ?? ''));
$message  = trim((string)($data['message'] ?? ''));

if ($name === '' || $email === '' || $message === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Name, email and requirement are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Please enter a valid email address.']);
    exit;
}

$pdfPath = dirname(__DIR__) . '/aerosphere_complete_catalogue.pdf';
if (!is_file($pdfPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Catalogue file is missing on the server.']);
    exit;
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$html = '<div style="font-family:Arial,sans-serif;color:#0B2036;line-height:1.6">'
      . '<h2 style="margin:0 0 16px">Aerosphere Catalogue</h2>'
      . '<p>Dear ' . e($name) . ',</p>'
      . '<p>Thank you for your enquiry. Please find the complete Aerosphere aerosol solutions catalogue attached.</p>'
      . '<p><strong>Category:</strong> ' . e($category) . '<br>'
      . '<strong>Company:</strong> ' . e($company) . '<br>'
      . '<strong>Requirement:</strong> ' . nl2br(e($message)) . '</p>'
      . '<p>Regards,<br><strong>Aerosphere</strong></p>'
      . '</div>';

$attachment = base64_encode(file_get_contents($pdfPath));
$payload = [
    'from' => $from,
    'to' => [$email],
    'subject' => 'Aerosphere - Complete Product Catalogue',
    'html' => $html,
    'attachments' => [[
        'filename' => 'Aerosphere_Complete_Catalogue.pdf',
        'content' => $attachment,
    ]],
];

if ($notify !== '') {
    $payload['cc'] = [$notify];
}

$ch = curl_init('https://api.resend.com/emails');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 45,
]);

$response = curl_exec($ch);
$curlError = curl_error($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false || $curlError !== '') {
    error_log('Aerosphere Resend cURL error: ' . $curlError);
    http_response_code(502);
    echo json_encode(['error' => 'Unable to contact the email service.']);
    exit;
}

$result = json_decode($response, true);
if ($status < 200 || $status >= 300) {
    error_log('Aerosphere Resend error: ' . $response);
    http_response_code(502);
    echo json_encode(['error' => $result['message'] ?? 'Email provider rejected the request.']);
    exit;
}

echo json_encode(['ok' => true, 'id' => $result['id'] ?? null]);
