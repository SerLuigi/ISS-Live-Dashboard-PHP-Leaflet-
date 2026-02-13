// assets/js/app.js

const POS_INTERVAL_MS = 5000;
const MAX_TRAIL_POINTS = 240; // 20 minutes @ 5s

const el = (id) => document.getElementById(id);

let map, issMarker, trailLine, predictLine;
let trail = [];
let posCountdown = POS_INTERVAL_MS / 1000;
let countdownTimer = null;

function formatUTC(unixSeconds){
  const d = new Date(unixSeconds * 1000);
  return d.toISOString().replace("T"," ").replace("Z"," UTC");
}

function setOnline(ok){
  el("netPill").textContent = ok ? "ONLINE" : "OFFLINE";
  el("netPill").style.opacity = ok ? "1" : "0.7";
}

function safeNum(v){
  const n = Number(v);
  return Number.isFinite(n) ? n : null;
}

function initMap(){
  map = L.map("map", { worldCopyJump: true }).setView([0, 0], 2);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 6,
    attribution: "&copy; OpenStreetMap contributors"
  }).addTo(map);

  const issIcon = L.icon({
    iconUrl: "https://www.scada24.io/iss/assets/img/iss.png?v=1",
    iconSize: [48, 48],
    iconAnchor: [24, 24],
    className: "iss-img-icon"
  });

  issMarker = L.marker([0, 0], { icon: issIcon }).addTo(map);

  trailLine = L.polyline([], { weight: 3, opacity: 0.85 }).addTo(map);
  predictLine = L.polyline([], { weight: 2, opacity: 0.8, dashArray: "6 8" }).addTo(map);
}

function pushTrail(lat, lon, ts){
  trail.push({ lat, lon, ts });
  if (trail.length > MAX_TRAIL_POINTS) trail.shift();
}

function updateTrailLine(){
  trailLine.setLatLngs(trail.map(p => [p.lat, p.lon]));
}

function setFutureLine(currentLat, currentLon, futurePoints){
  if (Array.isArray(futurePoints) && futurePoints.length){
    const pred = futurePoints
      .map(p => [safeNum(p.latitude), safeNum(p.longitude)])
      .filter(([a,b]) => a !== null && b !== null);

    if (pred.length){
      predictLine.setLatLngs([[currentLat, currentLon], ...pred]);
      return;
    }
  }
  predictLine.setLatLngs([]);
}

async function fetchISS(){
  try{
    const r = await fetch("api/iss.php", { cache: "no-store" });
    const j = await r.json();
    if (!j.ok) throw new Error(j.error || "API error");

    const lat = safeNum(j.latitude);
    const lon = safeNum(j.longitude);
    const ts  = safeNum(j.timestamp);

    if (lat === null || lon === null || ts === null) throw new Error("Invalid ISS payload");

    el("lat").textContent = lat.toFixed(4);
    el("lon").textContent = lon.toFixed(4);
    el("ts").textContent  = formatUTC(ts);

    // extra fields (if present in api/iss.php)
    const alt = safeNum(j.altitude_km);
    const vel = safeNum(j.velocity_kmh);
    const fp  = safeNum(j.footprint_km);
    const vis = (j.visibility ?? "").toString().trim();

    if (el("alt")) el("alt").textContent = (alt === null) ? "—" : `${alt.toFixed(1)} km`;
    if (el("vel")) el("vel").textContent = (vel === null) ? "—" : `${vel.toFixed(0)} km/h`;
    if (el("fp"))  el("fp").textContent  = (fp  === null) ? "—" : `${fp.toFixed(0)} km`;
    if (el("vis")) el("vis").textContent = vis ? vis : "—";

    issMarker.setLatLng([lat, lon]);
    if (trail.length === 0) map.setView([lat, lon], 3, { animate: true });

    pushTrail(lat, lon, ts);
    updateTrailLine();

    // future route from API (real positions)
    setFutureLine(lat, lon, j.future);

    el("posNote").textContent = "Blue line = last positions. Dashed line = next 10 minutes (API-based).";
    setOnline(true);
    el("tickPill").textContent = "Updated";

  } catch(e){
    setOnline(false);
    el("tickPill").textContent = "Update failed";
  }
}

function startCountdown(){
  if (countdownTimer) clearInterval(countdownTimer);
  posCountdown = POS_INTERVAL_MS / 1000;
  el("posCountdown").textContent = posCountdown;

  countdownTimer = setInterval(() => {
    posCountdown--;
    if (posCountdown <= 0) posCountdown = POS_INTERVAL_MS / 1000;
    el("posCountdown").textContent = posCountdown;
  }, 1000);
}

async function fetchPeople(){
  try{
    const r = await fetch("api/people.php", { cache: "no-store" });
    const j = await r.json();
    if (!j.ok) throw new Error(j.error || "API error");

    el("peopleCount").textContent = j.number ?? "—";
    const fetched = j.fetched_at ? formatUTC(j.fetched_at) : "—";
    el("peopleUpdated").textContent = `Last fetch: ${fetched}${j.stale ? " (stale)" : ""}`;

    const crew = [];
    (j.people || []).forEach(p => {
      const name = (p.name || "").trim();
      if (name) crew.push(name);
    });

    el("crewISS").innerHTML = crew.length
      ? crew.map(n => `<li>${escapeHtml(n)}</li>`).join("")
      : "<li>—</li>";

  } catch(e){
    el("peopleUpdated").textContent = "Crew fetch failed";
  }
}

function escapeHtml(s){
  return String(s)
    .replaceAll("&","&amp;")
    .replaceAll("<","&lt;")
    .replaceAll(">","&gt;")
    .replaceAll('"',"&quot;")
    .replaceAll("'","&#039;");
}

// boot
initMap();
fetchISS();
fetchPeople();
startCountdown();

setInterval(fetchISS, POS_INTERVAL_MS);
setInterval(fetchPeople, 24 * 60 * 60 * 1000);