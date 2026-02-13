<?php
// index.php

// Cache-busting automatico per Aruba: cambia querystring quando il file cambia
$css_v = @filemtime(__DIR__ . "/assets/css/style.css") ?: time();
$js_v  = @filemtime(__DIR__ . "/assets/js/app.js") ?: time();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ISS Live Dashboard</title>

  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
  <link rel="stylesheet" href="assets/css/style.css?v=<?=$css_v?>" />
</head>

<body>
  <header class="topbar">
    <div class="brand">
      <div class="dot"></div>
      <div>
        <div class="title">ISS Live Dashboard</div>
        <div class="subtitle">Real-time position (5s) • Crew list (daily)</div>
      </div>
    </div>

    <div class="status">
      <div class="pill" id="netPill">ONLINE</div>
      <div class="pill pill-soft" id="tickPill">Updating…</div>
    </div>
  </header>

  <main class="grid">
    <section class="card map-card">
      <div class="card-head">
        <h2>World Map</h2>
        <div class="hint">Blue trail = last positions • Dashed = next 10 minutes (API-based)</div>
      </div>
      <div id="map"></div>
    </section>

    <section class="card">
      <div class="card-head">
        <h2>ISS Position</h2>
        <div class="hint">Auto refresh: <span id="posCountdown">5</span>s</div>
      </div>

      <div class="kv">
        <div class="k">Latitude</div><div class="v" id="lat">—</div>
        <div class="k">Longitude</div><div class="v" id="lon">—</div>
        <div class="k">Timestamp (UTC)</div><div class="v" id="ts">—</div>

        <div class="k">Altitude</div><div class="v" id="alt">—</div>
        <div class="k">Velocity</div><div class="v" id="vel">—</div>
        <div class="k">Visibility</div><div class="v" id="vis">—</div>
        <div class="k">Footprint</div><div class="v" id="fp">—</div>

        <div class="k">Source</div><div class="v mono">api/iss.php</div>
      </div>

      <div class="footer-note" id="posNote">Waiting for first fix…</div>
    </section>

    <section class="card">
      <div class="card-head">
        <h2>People in Space</h2>
        <div class="hint">Refresh: once per day</div>
      </div>

      <div class="crew-meta">
        <div class="big" id="peopleCount">—</div>
        <div class="small">total in space right now</div>
        <div class="small muted" id="peopleUpdated">—</div>
      </div>

      <div class="crew-lists">
        <div class="crew-block">
          <div class="crew-title">People On ISS</div>
          <ul id="crewISS" class="crew"></ul>
        </div>
      </div>

      <div class="footer-note mono">Cached server-side for 24h</div>
    </section>
  </main>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
          integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <script src="assets/js/app.js?v=<?=$js_v?>"></script>
    <footer class="footer">
    Live Tracking ISS - by Luigi Serafino
  </footer>

</body>
</html>
