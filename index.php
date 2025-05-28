<?php
header("Content-Type: application/json");

// 获取原始请求体
$raw = file_get_contents("php://input");

// 调试输出原始 JSON
file_put_contents("php://stderr", "[Render DEBUG] Raw input: $raw\n");

// 解码 JSON
$data = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["error" => "Malformed JSON: " . json_last_error_msg()]);
    exit;
}

// 校验是否包含 record 字段
if (!isset($data["record"]) || !is_array($data["record"])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid record format"]);
    exit;
}

// 构建 kintone 请求
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

// 发送请求给 kintone
$ch = curl_init($kintone_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(["error" => curl_error($ch)]);
    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 返回结果给前端（WordPress）
http_response_code($httpCode);
echo $result;
