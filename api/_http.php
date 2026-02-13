<?php
// api/_http.php

function http_get_json(string $url, int $timeoutSeconds = 8): array {

  if (!function_exists('curl_init')) {
    throw new Exception("cURL not available on this hosting");
  }

  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_CONNECTTIMEOUT => $timeoutSeconds,
    CURLOPT_TIMEOUT => $timeoutSeconds,
    CURLOPT_USERAGENT => "iss-dashboard/1.0",
    CURLOPT_HTTPHEADER => ["Accept: application/json"],
    // HTTPS ok
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
  ]);

  $raw = curl_exec($ch);
  $err = curl_error($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($raw === false) throw new Exception("cURL failed: " . ($err ?: "unknown"));
  if ($code >= 400) throw new Exception("HTTP status $code");

  $data = json_decode($raw, true);
  if (!is_array($data)) throw new Exception("Invalid JSON");

  return $data;
}

function json_response(array $payload, int $status = 200): void {
  http_response_code($status);
  header("Content-Type: application/json; charset=utf-8");
  header("Cache-Control: no-store");
  echo json_encode($payload, JSON_UNESCAPED_SLASHES);
  exit;
}
