<?php
header("Content-Type: application/json");

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$kintone_app_id = "50";
$kintone_token = "28LYckktUQmNE9XbPVYJOX34Bs0YZzG6dAZQFLMM";
$kintone_url = "https://nbwfqj2m0ta3.cybozu.com/k/v1/record.json";

$payload = [
    "app" => $kintone_app_id,
    "record" => $data["record"]
];

$headers = [
    "Content-Type: application/json",
    "X-Cybozu-API-Token: $kintone_token"
];

$ch = curl_init($kintone_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

http_response_code($httpCode);
echo $result;
