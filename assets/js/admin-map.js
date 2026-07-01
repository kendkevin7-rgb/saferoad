function initAdminMap() {
    var center = { lat: -1.9441, lng: 30.0619 };
    var mapEl = document.getElementById('adminMap') || document.getElementById('liveMap');
    if (!mapEl) return;

    var map = new google.maps.Map(mapEl, {
        center: center,
        zoom: 13,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        mapTypeControl: true,
        fullscreenControl: true,
        streetViewControl: false,
        zoomControl: true
    });

    var trafficLayer = new google.maps.TrafficLayer();
    trafficLayer.setMap(map);

    if (typeof onlineDrivers !== 'undefined' && onlineDrivers.length) {
        onlineDrivers.forEach(function(driver) {
            var lat = parseFloat(driver.latitude);
            var lng = parseFloat(driver.longitude);
            if (!lat || !lng) return;
            var color = (driver.speed || 0) > 0 ? '#06d6a0' : '#4361ee';
            var marker = new google.maps.Marker({
                position: { lat: lat, lng: lng },
                map: map,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 10,
                    fillColor: color,
                    fillOpacity: 0.9,
                    strokeColor: '#fff',
                    strokeWeight: 3
                },
                title: driver.full_name || driver.username
            });
            var info = new google.maps.InfoWindow({
                content: '<div style="padding:8px;"><strong>' + (driver.full_name || driver.username) + '</strong><br>' +
                         'Speed: <b>' + (driver.speed || 0) + '</b> km/h<br>' +
                         '<small>' + (driver.recorded_at || '') + '</small></div>'
            });
            marker.addListener('click', function() { info.open(map, marker); });
        });
    }

    $.get(SITE_URL + 'api/maps/checkpoints.php', function(data) {
        data.forEach(function(cp) {
            var pos = { lat: parseFloat(cp.latitude), lng: parseFloat(cp.longitude) };
            if (!pos.lat || !pos.lng) return;
            var color = cp.type === 'speed_camera' ? '#FFC107' :
                        cp.type === 'red_light_camera' ? '#FF5722' :
                        cp.type === 'traffic_police' ? '#F44336' :
                        cp.type === 'toll_booth' ? '#2196F3' : '#9C27B0';
            var marker = new google.maps.Marker({
                position: pos, map: map,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 8, fillColor: color,
                    fillOpacity: 0.9, strokeColor: '#fff', strokeWeight: 2
                },
                title: cp.checkpoint_name + ' (' + cp.type.replace(/_/g, ' ') + ')'
            });
            var info = new google.maps.InfoWindow({
                content: '<div style="padding:8px;"><strong>' + cp.checkpoint_name + '</strong><br>' +
                         '<span>Type: ' + cp.type.replace(/_/g, ' ') + '</span></div>'
            });
            marker.addListener('click', function() { info.open(map, marker); });
        });
    });

    var legendDiv = document.createElement('div');
    legendDiv.style.cssText = 'background:#fff;padding:8px;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.2);margin:10px;font-size:12px;';
    legendDiv.innerHTML = '<b style="display:block;margin-bottom:4px;">Legend</b>' +
        '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#06d6a0;margin-right:4px;"></span> Moving Driver<br>' +
        '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#4361ee;margin-right:4px;"></span> Idle Driver<br>' +
        '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#FFC107;margin-right:4px;"></span> Speed Camera<br>' +
        '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#F44336;margin-right:4px;"></span> Police Checkpoint<br>' +
        '<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#FF5722;margin-right:4px;"></span> Red Light Camera';
    map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(legendDiv);

    setInterval(function() { location.reload(); }, 60000);
}
