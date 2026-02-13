This project is a small, self-contained web dashboard that tracks the International Space Station in near real time and shows the most useful “ops-style” information at a glance. When you open the page you get a world map with the ISS moving live, a blue trail showing where it has been, and a dashed path that represents the next ~10 minutes based on real predicted positions. On the side you also see the current latitude/longitude and UTC timestamp, plus a few key values that make the tracking feel “real”: altitude, velocity, visibility (daylight/night) and the current footprint.

The dashboard is split into a simple front-end and two tiny PHP endpoints. The front-end is plain HTML/CSS + vanilla JavaScript, with Leaflet used to render the map tiles and manage the marker/lines. Every 5 seconds the JavaScript calls `api/iss.php`, updates the fields, moves the ISS icon, appends the new point to the trail, and refreshes the dashed “future track” line. The ISS icon is loaded from `assets/img/iss.png`, so swapping it with your own graphic is trivial.

Data comes from public endpoints. Live ISS position and telemetry are fetched from wheretheiss.at (developer docs: [https://wheretheiss.at/w/developer](https://wheretheiss.at/w/developer)). The crew list is fetched by `api/people.php` from [https://www.howmanypeopleareinspacerightnow.com/peopleinspace.json](https://www.howmanypeopleareinspacerightnow.com/peopleinspace.json) and cached server-side for 24 hours to keep the page fast and avoid unnecessary repeated calls. If the remote crew endpoint is temporarily unavailable, the dashboard can still show the last cached crew data.

Here’s how it works in practice:

* Open the dashboard and the map centers itself on the first valid fix.
* The marker updates every 5 seconds and the blue line keeps a short history of past positions.
* The dashed line shows the next ~10 minutes using API-based future positions, not a rough extrapolation.

In short, you can upload this folder to any shared hosting that supports PHP, open `index.php`, and you immediately have a clean blue “mission-style” dashboard that keeps updating on its own: it tracks where the ISS is now, where it has been, and where it’s going next, together with the key telemetry values that explain what you’re looking at.

## ISS icon (important)
The ISS marker icon is loaded via an absolute URL. After downloading the project, update it to match your own domain/path in `assets/js/app.js`:

```js
const issIcon = L.icon({
  iconUrl: "https://www.yoursite/iss/assets/img/iss.png?v=1",
  iconSize: [48, 48],
  iconAnchor: [24, 24],
  className: "iss-img-icon"
});
