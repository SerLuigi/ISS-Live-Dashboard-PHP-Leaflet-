<?php
require_once __DIR__ . "/_http.php";

try {
  $base = "https://api.wheretheiss.at/v1/satellites/25544";

  // LIVE
  $live = http_get_json($base, 8);

  $ts = isset($live["timestamp"]) ? (int)$live["timestamp"] : time();

  // FUTURE (10 timestamps, 1 per minuto)
  $timestamps = [];
  for ($i = 1; $i <= 10; $i++) $timestamps[] = $ts + ($i * 60);
  $urlFuture = $base . "/positions?timestamps=" . implode(",", $timestamps);

  $future = http_get_json($urlFuture, 8);

  $payload = [
    "ok" => true,
    "timestamp"  => $ts,
    "latitude"   => (float)$live["latitude"],
    "longitude"  => (float)$live["longitude"],
    "altitude_km"=> isset($live["altitude"]) ? (float)$live["altitude"] : null,
    "velocity_kmh"=> isset($live["velocity"]) ? (float)$live["velocity"] : null,
    "visibility" => isset($live["visibility"]) ? (string)$live["visibility"] : null,
    "footprint_km"=> isset($live["footprint"]) ? (float)$live["footprint"] : null,
    "units"      => isset($live["units"]) ? (string)$live["units"] : "kilometers",
    "future"     => is_array($future) ? array_map(function($p){
      return [
        "timestamp" => isset($p["timestamp"]) ? (int)$p["timestamp"] : null,
        "latitude"  => isset($p["latitude"]) ? (float)$p["latitude"] : null,
        "longitude" => isset($p["longitude"]) ? (float)$p["longitude"] : null,
      ];
    }, $future) : []
  ];

  json_response($payload);

} catch (Throwable $e) {
  json_response(["ok"=>false,"error"=>$e->getMessage()], 502);
}
