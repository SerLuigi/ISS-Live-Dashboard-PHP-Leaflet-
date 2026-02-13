<?php
require_once __DIR__ . "/_http.php";

$cacheFile = __DIR__ . "/people_cache.json";
$ttlSeconds = 24 * 60 * 60;

try {
  if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttlSeconds) {
    $raw = file_get_contents($cacheFile);
    $cached = json_decode($raw, true);
    if (is_array($cached)) json_response($cached);
  }

  $url = "https://www.howmanypeopleareinspacerightnow.com/peopleinspace.json";
  $data = http_get_json($url, 8);

  // Questo endpoint di solito ha: number, people:[{name,craft}]
  $payload = [
    "ok" => true,
    "fetched_at" => time(),
    "number" => isset($data["number"]) ? (int)$data["number"] : null,
    "people" => (isset($data["people"]) && is_array($data["people"])) ? $data["people"] : []
  ];

  file_put_contents($cacheFile, json_encode($payload, JSON_UNESCAPED_SLASHES));
  json_response($payload);

} catch (Throwable $e) {
  if (file_exists($cacheFile)) {
    $raw = file_get_contents($cacheFile);
    $cached = json_decode($raw, true);
    if (is_array($cached)) { $cached["stale"]=true; json_response($cached); }
  }
  json_response(["ok"=>false,"error"=>$e->getMessage()], 502);
}
