<?php

echo "Add to cron schedule: 0 * * * * php /путь/к/вашему/скрипту/cron.php >/dev/null 2>&1";

$postData = http_build_query(['action' => 'parse-xml']);

$currentURL = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$actionUrl = dirname($currentURL) . '/action.php';

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $actionUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

$result = curl_exec($ch);


if ($result === false) {
    //echo 'Error action.php | ' . curl_error($ch) . " | " . $status_code;
    error_log('Error action.php | ' . curl_error($ch) . " | " . $status_code);
} else {
    //echo 'Action.php script executed successfully;
    error_log('Action.php script executed successfully');
}

curl_close($ch);

