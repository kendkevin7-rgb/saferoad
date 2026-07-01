<?php
define('PAGE_TITLE', 'Live Drivers');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$db = Database::getInstance();

$stmt = $db->query("SELECT u.id, u.username, u.full_name, dl.latitude, dl.longitude, dl.speed, dl.heading, dl.recorded_at
    FROM driver_locations dl JOIN users u ON dl.user_id = u.id
    WHERE dl.id IN (SELECT MAX(id) FROM driver_locations GROUP BY user_id)
    ORDER BY dl.recorded_at DESC");
$drivers = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>
<div class="container-fluid">
    <h4 class="fw-bold mb-4"><i class="fas fa-wifi me-2 text-primary"></i>Live Driver Locations</h4>

    <div class="row g-3">
        <div class="col-md-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between">
                    <h5 class="mb-0"><i class="fas fa-map me-2 text-primary"></i>Live Map</h5>
                    <span class="badge bg-success" id="driverCount"><?php echo count($drivers); ?> Online</span>
                </div>
                <div class="card-body p-0">
                    <div id="liveMap" style="height: 600px;"></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-list me-2 text-primary"></i>Online Drivers</h5></div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 600px; overflow-y: auto;" id="driverList">
                        <?php foreach ($drivers as $d): ?>
                        <div class="list-group-item py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars($d['full_name'] ?: $d['username']); ?></strong>
                                    <br><small class="text-muted">Speed: <?php echo $d['speed'] ? number_format($d['speed'], 1) : '0'; ?> km/h</small>
                                </div>
                                <span class="badge bg-<?php echo ($d['speed'] ?? 0) > 0 ? 'success' : 'secondary'; ?> rounded-pill">
                                    <i class="fas fa-circle fa-xs"></i>
                                </span>
                            </div>
                            <small class="text-muted"><?php echo timeAgo($d['recorded_at']); ?></small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = '
<script src="https://maps.googleapis.com/maps/api/js?key=' . GOOGLE_MAPS_API_KEY . '&callback=initLiveMap&libraries=geometry" async defer></script>
<script>
var liveDrivers = ' . json_encode($drivers) . ';
var map, markers = [];

function initLiveMap() {
    var mapEl = document.getElementById("liveMap");
    if (!mapEl) return;
    map = new google.maps.Map(mapEl, {
        center: { lat: -1.9441, lng: 30.0619 },
        zoom: 12,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    });
    updateMarkers(liveDrivers);
    setInterval(fetchDrivers, 30000);
}

function updateMarkers(drivers) {
    markers.forEach(function(m) { m.setMap(null); });
    markers = [];
    var driverList = document.getElementById("driverList");
    if (driverList) driverList.innerHTML = "";
    drivers.forEach(function(d) {
        var lat = parseFloat(d.latitude);
        var lng = parseFloat(d.longitude);
        if (!lat || !lng) return;
        var marker = new google.maps.Marker({
            position: { lat: lat, lng: lng },
            map: map,
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 8,
                fillColor: (d.speed || 0) > 0 ? "#06d6a0" : "#4361ee",
                fillOpacity: 0.9,
                strokeColor: "#fff",
                strokeWeight: 2
            },
            title: d.full_name || d.username
        });
        var info = new google.maps.InfoWindow({
            content: "<div class=\"p-2\"><strong>" + (d.full_name || d.username) + "</strong><br>Speed: " + (d.speed || 0) + " km/h<br>Last: " + d.recorded_at + "</div>"
        });
        marker.addListener("click", function() { info.open(map, marker); });
        markers.push(marker);
        if (driverList) {
            driverList.innerHTML += "<div class=\"list-group-item py-2\"><div class=\"d-flex justify-content-between align-items-center\"><div><strong>" + (d.full_name || d.username) + "</strong><br><small class=\"text-muted\">Speed: " + (d.speed || 0) + " km/h</small></div><span class=\"badge bg-" + ((d.speed || 0) > 0 ? "success" : "secondary") + " rounded-pill\"><i class=\"fas fa-circle fa-xs\"></i></span></div><small class=\"text-muted\">" + d.recorded_at + "</small></div>";
        }
    });
    var countEl = document.getElementById("driverCount");
    if (countEl) countEl.textContent = drivers.length + " Online";
}

function fetchDrivers() {
    fetch(SITE_URL + "api/maps/update-location.php", { method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: "lat=-1.9441&lng=30.0619" })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        fetch(SITE_URL + "admin/live/drivers-json.php")
        .then(function(r) { return r.json(); })
        .then(function(drivers) { updateMarkers(drivers); });
    });
}
</script>
';
include __DIR__ . '/../../includes/footer.php';
?>
