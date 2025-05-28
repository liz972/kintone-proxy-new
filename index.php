<?php
header("Content-Type: application/json");

// 获取原始 JSON 请求体
$raw = file_get_contents("php://input");
error_log('[Render DEBUG] Raw input: ' . $raw);

// 尝试解析 JSON
$data = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["error" => "Malformed JSON: " . json_last_error_msg()]);
    error_log('[Render ERROR] JSON decode error: ' . json_last_error_msg());
    exit;
}

error_log('[Render DEBUG] Decoded input: ' . print_r($data, true));

// 校验格式是否包含 record
if (!isset($data['record']) || !is_array($data['record'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid record format"]);
    error_log('[Render ERROR] Missing or invalid `record` field.');
    exit;
}

// Kintone 接入配置
$kintone_app_id = "50";
$kintone_token = "28LYckktUQmNE9XbPVYJOX34Bs0YZzG6dAZQFLMM";
$kintone_url = "https://nbwfqj2m0ta3.cybozu.com/k/v1/record.json";

// 构建 payload
$payload = [
    "app" => $kintone_app_id,
    "record" => $data["record"]
];

// 设置 headers
$headers = [
    "Content-Type: application/json",
    "X-Cybozu-API-Token: $kintone_token"
];

// 向 kintone 发起 POST 请求
$ch = curl_init($kintone_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);

// 错误处理
if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(["error" => curl_error($ch)]);
    error_log('[Render ERROR] curl error: ' . curl_error($ch));
    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 返回 kintone 响应内容
http_response_code($httpCode);
echo $result;
error_log('[Render DEBUG] Kintone Response: ' . $result);
