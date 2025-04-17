<?php

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use Dotenv\Dotenv;


$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$logDir       = __DIR__ . '/logs';
$rawInputPath = "$logDir/last_slack_raw.json";
$eventDebug   = __DIR__ . '/event_debug.json';
$smtpLogPath  = "$logDir/smtp_log.json";
$mapPath      = "$logDir/email_map.json";
$processedPath= "$logDir/processed_ids.json";


file_put_contents($rawInputPath, file_get_contents("php://input"));


$data = json_decode(file_get_contents("php://input"), true);


if (isset($data['type']) && $data['type'] === 'url_verification') {
    header('Content-Type: text/plain');
    echo $data['challenge'];
    exit;
}


$processed = file_exists($processedPath)
    ? json_decode(file_get_contents($processedPath), true)
    : [];

$eventId = $data['event_id'] ?? null;
if ($eventId && in_array($eventId, $processed, true)) {
    exit;
}
if ($eventId) {
    $processed[] = $eventId;
    file_put_contents($processedPath, json_encode($processed, JSON_PRETTY_PRINT));
}


$event = $data['event'] ?? [];


$botUserId = $_ENV['SLACK_BOT_USER_ID'] ?? '';
if (isset($event['user']) && $event['user'] === $botUserId) {
    file_put_contents($smtpLogPath, "🚫 Ignored bot's own message\n", FILE_APPEND);
    exit("Ignored bot event.");
}


file_put_contents($eventDebug, json_encode($event, JSON_PRETTY_PRINT));

// Handle thread reply
if (
    isset($event['type'], $event['thread_ts'], $event['ts']) &&
    $event['type'] === 'message' &&
    $event['ts'] !== $event['thread_ts']
) {
    header("HTTP/1.1 200 OK");
    echo json_encode(['ok' => true]);
    flush();

    
    $threadTs  = $event['thread_ts'];
    $replyText = $event['text'] ?? '';
    $channel   = $event['channel'] ?? '';

    
    if (!file_exists($mapPath)) {
        file_put_contents($smtpLogPath, "❌ email_map.json not found at $mapPath\n", FILE_APPEND);
        exit("Missing email map");
    }

    $map = json_decode(file_get_contents($mapPath), true);
    $toEmail = null;
    foreach ($map as $entry) {
        if ($entry['thread_ts'] === $threadTs) {
            $toEmail = $entry['real_email'];
            break;
        }
    }
    if (!$toEmail) {
        file_put_contents($smtpLogPath, "❌ No email found for thread_ts: $threadTs\n", FILE_APPEND);
        exit("No email mapped.");
    }

    file_put_contents($smtpLogPath, "📬 Sending to $toEmail\n", FILE_APPEND);

    // Send email 
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'];
        $mail->Password   = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = $_ENV['SMTP_SECURE'];
        $mail->Port       = $_ENV['SMTP_PORT'];;

        $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
        $mail->addAddress($toEmail);
        $mail->Subject = 'Reply from Support';
        $mail->Body    = $replyText;

        $mail->send();
        file_put_contents($smtpLogPath, "✅ Email sent to $toEmail\n", FILE_APPEND);
        exit;

    } catch (Exception $e) {
        file_put_contents($smtpLogPath, "❌ Email failed: " . $mail->ErrorInfo . "\n", FILE_APPEND);
        exit;
    }
}

echo "✅ Event received, but not a reply.";
