<?php

require 'vendor/autoload.php';

use Dotenv\Dotenv;


$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();


$slackToken = $_ENV['SLACK_TOKEN'] ?? '';
$channelId  = $_ENV['SLACK_CHANNEL_ID'] ?? '';


$sender  = $_POST['sender']  ?? 'unknown@domain.com';
$subject = $_POST['subject'] ?? '(no subject)';
$body    = $_POST['body']    ?? '(no body)';

$message = "*From:* `$sender`\n*Subject:* $subject\n\n$body";

// Post to Slack using Web API
$payload = [
    "channel" => $channelId,
    "text"    => $message
];

$ch = curl_init("https://slack.com/api/chat.postMessage");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $slackToken",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if (isset($result['ok']) && $result['ok'] === true) {
    $threadTs = $result['ts'];

    
    $mapPath = __DIR__ . '/logs/email_map.json'; 

    $map = file_exists($mapPath)
        ? json_decode(file_get_contents($mapPath), true)
        : [];

    $map[] = [
        "thread_ts" => "$threadTs",
        "real_email" => $sender
    ];

    file_put_contents($mapPath, json_encode($map, JSON_PRETTY_PRINT));

    echo "✅ Message posted and mapped.\n";
} else {
    error_log("❌ Failed to post to Slack: " . json_encode($result));
    echo "❌ Failed to post to Slack: " . ($result['error'] ?? 'Unknown error') . "\n";
}
