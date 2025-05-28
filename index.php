<?php
header("Content-Type: application/json");

// 忽略 favicon.ico 请求
if ($_SERVER['REQUEST_METHOD'] === 'GET' && strpos($_SERVER['REQUEST_URI'], 'favicon.ico') !== false) {
    http_response_code(204);
    exit;
}

// 只接受 POST 请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

// 读取请求体
$raw = file_get_contents("php://input");
error_log('[Render DEBUG] Raw input: ' . $raw);

// 解析 JSON
$data = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["error" => "Malformed JSON: " . json_last_error_msg()]);
    error_log('[Render ERROR] JSON decode error: ' . json_last_error_msg());
    exit;
}
error_log('[Render DEBUG] Decoded input: ' . print_r($data, true));

// 校验 record 字段
if (!isset($data['record']) || !is_array($data['record'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid record format"]);
    error_log('[Render ERROR] Missing or invalid `record` field.');
    exit;
}

// ======= KINTONE CONFIG (请替换为你的实际信息) =======
$kintone_app_id = "50";  // App ID
$kintone_token = "28LYckktUQmNE9XbPVYJOX34Bs0YZzG6dAZQFLMM";  // API Token
$kintone_url = "https://nbwfqj2m0ta3.cybozu.com/k/v1/record.json";  // 你的 kintone 域名
// =====================================================

// 构建 payload
$payload = [
    "app" => $kintone_app_id,
    "record" => $data["record"]
];

$headers = [
    "Content-Type: application/json",
    "X-Cybozu-API-Token: $kintone_token"
];

// 发出 POST 请求
$ch = curl_init($kintone_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($ch);

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(["error" => curl_error($ch)]);
    error_log('[Render ERROR] curl error: ' . curl_error($ch));
    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 输出 kintone 响应
http_response_code($httpCode);
echo $result;
error_log('[Render DEBUG] Kintone Response: ' . $result);
