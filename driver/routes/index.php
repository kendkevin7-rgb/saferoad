<?php
define('PAGE_TITLE', 'Smart Routes');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();

$db = Database::getInstance();
$userId = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT * FROM destinations WHERE user_id = ? ORDER BY is_favorite DESC, name ASC");
$stmt->execute([$userId]);
$destinations = $stmt->fetchAll();

$stmt = $db->prepare("SELECT * FROM driving_history WHERE user_id = ? ORDER BY started_at DESC LIMIT 10");
$stmt->execute([$userId]);
$history = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-route me-2 text-primary"></i>Smart Route Planning</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDestModal"><i class="fas fa-plus me-2"></i>Add Destination</button>
    </div>

    <div class="row g-3">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-map me-2 text-primary"></i>Route Map</h5>
                </div>
                <div class="card-body p-0">
                    <div id="routeMap" style="height: 450px;"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-star me-2 text-primary"></i>Your Destinations</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (empty($destinations)): ?>
                        <div class="list-group-item text-muted">No saved destinations</div>
                        <?php else: ?>
                        <?php foreach ($destinations as $d): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo htmlspecialchars($d['name']); ?></strong>
                                <?php if ($d['is_favorite']): ?><i class="fas fa-star text-warning ms-1"></i><?php endif; ?>
                                <br><small class="text-muted"><?php echo htmlspecialchars($d['address'] ?? ''); ?></small>
                            </div>
                            <button class="btn btn-sm btn-outline-primary" onclick="routeToDest(<?php echo $d['latitude']; ?>, <?php echo $d['longitude']; ?>)">
                                <i class="fas fa-route"></i>
                            </button>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-history me-2 text-primary"></i>Recent Trips</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 200px; overflow-y: auto;">
                        <?php if (empty($history)): ?>
                        <div class="list-group-item text-muted">No trips yet</div>
                        <?php else: ?>
                        <?php foreach ($history as $h): ?>
                        <div class="list-group-item py-2">
                            <div class="d-flex justify-content-between">
                                <small><strong><?php echo formatDistance($h['distance_km']); ?></strong></small>
                                <small class="text-muted"><?php echo formatDuration($h['duration_minutes']); ?></small>
                            </div>
                            <small class="text-muted"><?php echo date('M d, H:i', strtotime($h['started_at'])); ?></small>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addDestModal"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5>Add Destination</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="<?php echo SITE_URL; ?>api/maps/save-destination.php">
        <div class="modal-body">
            <div class="mb-3"><label>Name</label><input type="text" name="name" class="form-control" required placeholder="Home, Office, etc."></div>
            <div class="row">
                <div class="col-md-6 mb-3"><label>Latitude</label><input type="text" name="latitude" class="form-control" step="any" required></div>
                <div class="col-md-6 mb-3"><label>Longitude</label><input type="text" name="longitude" class="form-control" step="any" required></div>
            </div>
            <div class="mb-3"><label>Address</label><textarea name="address" class="form-control" rows="2"></textarea></div>
            <div class="form-check"><input type="checkbox" name="is_favorite" class="form-check-input" value="1"><label class="form-check-label">Set as favorite</label></div>
        </div>
        <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>

<?php
$extraScripts = '
<script src="https://maps.googleapis.com/maps/api/js?key=' . GOOGLE_MAPS_API_KEY . '&callback=initRouteMap&libraries=places,geometry" async defer></script>
<script>
var SITE_URL = "' . SITE_URL . '";

function initRouteMap() {
    var mapEl = document.getElementById("routeMap");
    if (!mapEl) return;
    var map = new google.maps.Map(mapEl, {
        center: { lat: -1.9441, lng: 30.0619 },
        zoom: 12
    });
    window.routeMap = map;
}

function routeToDest(lat, lng) {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
            var origin = new google.maps.LatLng(pos.coords.latitude, pos.coords.longitude);
            var dest = new google.maps.LatLng(lat, lng);
            var service = new google.maps.DirectionsService();
            var renderer = new google.maps.DirectionsRenderer({ map: window.routeMap });
            service.route({
                origin: origin,
                destination: dest,
                travelMode: "DRIVING",
                provideRouteAlternatives: true
            }, function(resp, status) {
                if (status === "OK") renderer.setDirections(resp);
                else alert("Route not found");
            });
        });
    }
}
</script>
';
include __DIR__ . '/../../includes/footer.php';
?>
