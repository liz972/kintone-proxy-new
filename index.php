<?php
header("Content-Type: application/json");

// ✅ 忽略 favicon.ico 请求
if ($_SERVER['REQUEST_METHOD'] === 'GET' && strpos($_SERVER['REQUEST_URI'], 'favicon.ico') !== false) {
    http_response_code(204);
    exit;
}

// ✅ 只允许 POST 请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

// ✅ 读取并解析 JSON 请求体
$raw = file_get_contents("php://input");
error_log('[Render DEBUG] Raw input: ' . $raw);

$data = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["error" => "Malformed JSON: " . json_last_error_msg()]);
    error_log('[Render ERROR] JSON decode error: ' . json_last_error_msg());
    exit;
}
error_log('[Render DEBUG] Decoded input: ' . print_r($data, true));

// ✅ 校验数据结构
if (!isset($data['record']) || !is_array($data['record'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid record format"]);
    error_log('[Render ERROR] Missing or invalid `record` field.');
    exit;
}

// ✅ Kintone 接入配置（请替换为你自己的）
$kintone_app_id = "50";  // App ID
$kintone_token = "28LYckktUQmNE9XbPVYJOX34Bs0YZzG6dAZQFLMM";  // API Token
$kintone_url = "https://nbwfqj2m0ta3.cybozu.com/k/v1/record.json";

// ✅ 构建发送给 kintone 的 JSON payload
$payload = [
    "app" => $kintone_app_id,
    "record" => $data["record"]
];

// ✅ 设置请求头
$headers = [
    "Content-Type: application/json",
    "X-Cybozu-API-Token: $kintone_token"
];

// ✅ 发出 CURL 请求
$ch = curl_init($kintone_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($ch);

// ❗ CURL 错误处理
if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(["error" => curl_error($ch)]);
    error_log('[Render ERROR] curl error: ' . curl_error($ch));
    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// ✅ 正常返回
http_response_code($httpCode);
echo $result;
error_log('[Render DEBUG] Kintone Response: ' . $result);
