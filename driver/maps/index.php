<?php
define('PAGE_TITLE', 'Navigation Map');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>
<div class="container-fluid">
    <div class="row g-3">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-map me-2 text-primary"></i>Live Navigation</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary" onclick="toggleTraffic()">
                            <i class="fas fa-car me-1"></i> Traffic
                        </button>
                        <button class="btn btn-sm btn-outline-primary" onclick="centerOnMe()">
                            <i class="fas fa-crosshairs me-1"></i> My Location
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="navMap" style="height: 550px;"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-search me-2 text-primary"></i>Search Destination</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <input type="text" id="destinationInput" class="form-control" placeholder="Enter destination...">
                        <div id="destinationSuggestions" class="list-group mt-1" style="display:none; position:absolute; z-index:1000;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Or select a saved destination:</label>
                        <select id="savedDestinations" class="form-select">
                            <option value="">-- Choose --</option>
                            <?php
                            $db = Database::getInstance();
                            $dests = $db->prepare("SELECT id, name, latitude, longitude FROM destinations WHERE user_id = ?");
                            $dests->execute([$_SESSION['user_id']]);
                            foreach ($dests->fetchAll() as $d):
                            ?>
                            <option value="<?php echo $d['latitude']; ?>,<?php echo $d['longitude']; ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-primary w-100 mb-3" onclick="navigateToDestination()">
                        <i class="fas fa-route me-2"></i>Navigate
                    </button>
                    <div id="routeInfo"></div>
                    <div class="mt-3">
                        <h6>Speed Monitor</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold text-primary" id="navSpeed">0.0</span>
                                <small>km/h</small>
                            </div>
                            <div>
                                <span class="text-muted">Limit: </span>
                                <span class="fw-bold" id="navSpeedLimit">--</span>
                            </div>
                        </div>
                        <div class="progress mt-1" style="height: 6px;">
                            <div id="navSpeedBar" class="progress-bar bg-success" style="width: 0%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = '
<script src="https://maps.googleapis.com/maps/api/js?key=' . GOOGLE_MAPS_API_KEY . '&callback=initDashboardMap&libraries=places,geometry" async defer></script>
<script src="' . SITE_URL . 'assets/js/maps.js"></script>
<script src="' . SITE_URL . 'assets/js/gps.js"></script>
<script>
var SITE_URL = "' . SITE_URL . '";

function initDestinationSearch() {
    var input = document.getElementById("destinationInput");
    if (!input) return;
    var autocomplete = new google.maps.places.Autocomplete(input, { types: ["geocode"] });
    autocomplete.addListener("place_changed", function() {
        var place = autocomplete.getPlace();
        if (place.geometry) {
            navigateToCoords(place.geometry.location.lat(), place.geometry.location.lng());
        }
    });
}

function navigateToDestination() {
    var sel = document.getElementById("savedDestinations");
    if (sel && sel.value) {
        var parts = sel.value.split(",");
        navigateToCoords(parseFloat(parts[0]), parseFloat(parts[1]));
    }
}

function navigateToCoords(lat, lng) {
    if (!driverMarker) return;
    var origin = driverMarker.getPosition();
    var dest = new google.maps.LatLng(lat, lng);
    destinationMarker = new google.maps.Marker({
        position: dest,
        map: map,
        icon: "https://maps.google.com/mapfiles/ms/icons/red-dot.png",
        title: "Destination"
    });
    calculateRoute(origin, dest);
}

function centerOnMe() {
    if (driverMarker && map) {
        map.setCenter(driverMarker.getPosition());
        map.setZoom(15);
    }
}
</script>
';
include __DIR__ . '/../../includes/footer.php';
?>
