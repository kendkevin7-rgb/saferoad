var map;
var driverMarker;
var destinationMarker;
var directionsRenderer;
var directionsService;
var trafficLayer;
var checkpointMarkers = [];
var alertMarkers = [];
var watchId = null;

function initDashboardMap() {
    var center = { lat: -1.9441, lng: 30.0619 };
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
            center = { lat: pos.coords.latitude, lng: pos.coords.longitude };
            initMap(center);
        }, function() { initMap(center); }, { enableHighAccuracy: true, timeout: 10000 });
    } else { initMap(center); }
}

function initMap(center) {
    var mapEl = document.getElementById('dashboardMap') || document.getElementById('navMap') || document.getElementById('adminMap') || document.getElementById('routeMap');
    if (!mapEl) return;

    map = new google.maps.Map(mapEl, {
        center: center,
        zoom: 14,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        mapTypeControl: true,
        fullscreenControl: true,
        streetViewControl: false,
        zoomControl: true
    });

    trafficLayer = new google.maps.TrafficLayer();
    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer({
        map: map,
        suppressMarkers: false,
        polylineOptions: { strokeColor: '#4361ee', strokeWeight: 5 }
    });

    driverMarker = new google.maps.Marker({
        position: center,
        map: map,
        icon: getDriverIcon(),
        title: 'Your Location',
        animation: google.maps.Animation.DROP
    });

    loadCheckpoints();
    loadAlerts();
    startGPSWatch();

    var legend = buildLegend();
    if (legend) map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(legend);

    if (typeof initDestinationSearch === 'function') initDestinationSearch();
}

function getDriverIcon() {
    return {
        path: google.maps.SymbolPath.CIRCLE,
        scale: 10,
        fillColor: '#4361ee',
        fillOpacity: 1,
        strokeColor: '#fff',
        strokeWeight: 3
    };
}

function getCheckpointIcon(type) {
    var icons = {
        'speed_camera': {
            path: 'M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98s.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.23.09.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zM12 15.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z',
            fillColor: '#FFC107', scale: 0.8
        },
        'traffic_police': {
            path: 'M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z',
            fillColor: '#F44336', scale: 0.8
        },
        'red_light_camera': {
            path: 'M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z',
            fillColor: '#FF5722', scale: 0.8
        },
        'toll_booth': {
            path: 'M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z',
            fillColor: '#2196F3', scale: 0.8
        },
        'border': {
            path: 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z',
            fillColor: '#9C27B0', scale: 0.8
        }
    };
    return icons[type] || icons['traffic_police'];
}

function loadCheckpoints() {
    if (!map) return;
    $.get(SITE_URL + 'api/maps/checkpoints.php', function(data) {
        data.forEach(function(cp) {
            var pos = { lat: parseFloat(cp.latitude), lng: parseFloat(cp.longitude) };
            if (!pos.lat || !pos.lng) return;
            var iconDef = getCheckpointIcon(cp.type);
            var marker = new google.maps.Marker({
                position: pos,
                map: map,
                icon: iconDef,
                title: cp.checkpoint_name,
                animation: google.maps.Animation.DROP,
                label: { text: cp.type === 'speed_camera' ? 'CAM' : cp.type === 'red_light_camera' ? 'RLC' : cp.type === 'traffic_police' ? 'POL' : 'BOOTH', fontSize: '10px', color: '#333', fontWeight: 'bold' }
            });
            var info = new google.maps.InfoWindow({
                content: '<div style="min-width:180px;padding:8px;">' +
                         '<h6 style="margin:0 0 5px;">' + cp.checkpoint_name + '</h6>' +
                         '<span class="badge" style="background:' + iconDef.fillColor + ';color:#fff;">' + cp.type.replace(/_/g, ' ') + '</span>' +
                         (cp.description ? '<p style="margin:5px 0 0;font-size:12px;">' + cp.description + '</p>' : '') +
                         '<p style="margin:3px 0 0;font-size:11px;color:#999;">' + pos.lat.toFixed(4) + ', ' + pos.lng.toFixed(4) + '</p></div>'
            });
            marker.addListener('click', function() { info.open(map, marker); });
            checkpointMarkers.push(marker);
        });
    });
}

function loadAlerts() {
    if (!map) return;
    $.get(SITE_URL + 'api/alerts/active.php', function(data) {
        data.forEach(function(a) {
            var lat = parseFloat(a.latitude);
            var lng = parseFloat(a.longitude);
            if (!lat || !lng) return;
            var color = a.severity === 'critical' ? '#dc3545' :
                        a.severity === 'high' ? '#ef476f' :
                        a.severity === 'medium' ? '#ffd166' : '#06d6a0';
            var icon = {
                path: google.maps.SymbolPath.CIRCLE,
                scale: 10,
                fillColor: color,
                fillOpacity: 0.8,
                strokeColor: '#fff',
                strokeWeight: 3
            };
            var marker = new google.maps.Marker({
                position: { lat: lat, lng: lng },
                map: map,
                icon: icon,
                title: a.title
            });
            var info = new google.maps.InfoWindow({
                content: '<div style="min-width:180px;padding:8px;">' +
                         '<h6 style="margin:0 0 5px;">' + a.title + '</h6>' +
                         '<span class="badge" style="background:' + color + ';color:#fff;">' + a.severity + '</span> <span class="badge bg-secondary">' + a.alert_type.replace(/_/g, ' ') + '</span>' +
                         (a.description ? '<p style="margin:5px 0 0;font-size:12px;">' + a.description + '</p>' : '') +
                         '</div>'
            });
            marker.addListener('click', function() { info.open(map, marker); });
            alertMarkers.push(marker);
        });
    });
}

function buildLegend() {
    var div = document.createElement('div');
    div.style.cssText = 'background:#fff;padding:10px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.2);margin:10px;font-size:12px;max-width:200px;';
    div.innerHTML = '<b style="display:block;margin-bottom:6px;">Map Legend</b>' +
        '<label style="display:flex;align-items:center;margin:3px 0;cursor:pointer;"><input type="checkbox" checked onchange="toggleLayer(\'checkpoints\',this.checked)" style="margin-right:5px;">' +
        '<span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#FFC107;margin-right:5px;"></span> Speed Cameras</label>' +
        '<label style="display:flex;align-items:center;margin:3px 0;cursor:pointer;"><input type="checkbox" checked onchange="toggleLayer(\'checkpoints\',this.checked)" style="margin-right:5px;">' +
        '<span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#F44336;margin-right:5px;"></span> Police Checkpoints</label>' +
        '<label style="display:flex;align-items:center;margin:3px 0;cursor:pointer;"><input type="checkbox" checked onchange="toggleLayer(\'checkpoints\',this.checked)" style="margin-right:5px;">' +
        '<span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#FF5722;margin-right:5px;"></span> Red Light Cameras</label>' +
        '<label style="display:flex;align-items:center;margin:3px 0;cursor:pointer;"><input type="checkbox" checked onchange="toggleLayer(\'checkpoints\',this.checked)" style="margin-right:5px;">' +
        '<span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#2196F3;margin-right:5px;"></span> Toll Booths</label>' +
        '<label style="display:flex;align-items:center;margin:3px 0;cursor:pointer;"><input type="checkbox" checked onchange="toggleLayer(\'alerts\',this.checked)" style="margin-right:5px;">' +
        '<span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#ef476f;margin-right:5px;"></span> Alerts</label>' +
        '<label style="margin:5px 0 0;display:flex;align-items:center;"><input type="checkbox" checked onchange="toggleTraffic()" style="margin-right:5px;">' +
        '<span style="color:#666;">Traffic Layer</span></label>';
    return div;
}

function toggleLayer(type, show) {
    var markers = type === 'checkpoints' ? checkpointMarkers : alertMarkers;
    markers.forEach(function(m) { m.setVisible(show); });
}

function clearMarkers() {
    checkpointMarkers.forEach(function(m) { m.setMap(null); });
    alertMarkers.forEach(function(m) { m.setMap(null); });
    checkpointMarkers = [];
    alertMarkers = [];
}

function toggleTraffic() {
    if (trafficLayer) {
        trafficLayer.setMap(trafficLayer.getMap() ? null : map);
    }
}

function calculateRoute(origin, destination) {
    if (!directionsService || !directionsRenderer) return;
    directionsService.route({
        origin: origin,
        destination: destination,
        travelMode: google.maps.TravelMode.DRIVING,
        provideRouteAlternatives: true,
        avoidTolls: false,
        avoidHighways: false
    }, function(response, status) {
        if (status === google.maps.DirectionsStatus.OK) {
            directionsRenderer.setDirections(response);
            var route = response.routes[0].legs[0];
            $('#routeInfo').html(
                '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>' +
                'Distance: ' + route.distance.text + ' | ' +
                'Duration: ' + route.duration.text +
                '</div>'
            );
        } else {
            $('#routeInfo').html(
                '<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>Route not found</div>'
            );
        }
    });
}

function startGPSWatch() {
    if (!navigator.geolocation) return;
    if (watchId) navigator.geolocation.clearWatch(watchId);

    watchId = navigator.geolocation.watchPosition(function(pos) {
        var lat = pos.coords.latitude;
        var lng = pos.coords.longitude;
        var speed = pos.coords.speed !== null ? pos.coords.speed * 3.6 : 0;
        var heading = pos.coords.heading || 0;

        updateDriverPosition(lat, lng, speed, heading);
        updateSpeedDisplay(speed);

        $.ajax({
            url: SITE_URL + 'api/maps/update-location.php',
            method: 'POST',
            data: {
                latitude: lat,
                longitude: lng,
                speed: speed,
                heading: heading,
                accuracy: pos.coords.accuracy || 0
            },
            success: function(response) {
                if (response.speed_limit) {
                    checkSpeedViolation(speed, response.speed_limit, response.road_name);
                }
                if (response.nearby_alerts) {
                    processAlerts(response.nearby_alerts, lat, lng);
                }
            }
        });
    }, function(err) {
        console.warn('GPS error:', err.message);
    }, { enableHighAccuracy: true, maximumAge: 3000, timeout: 10000 });
}
