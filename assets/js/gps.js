var currentSpeed = 0;
var speedLimit = 0;
var lastWarningTime = 0;
var warningCooldown = 30000;

function updateSpeedDisplay(speed) {
    currentSpeed = speed;
    var display = $('#currentSpeed');
    var bar = $('#speedBar');
    var limitDisplay = $('#speedLimitDisplay');

    display.text(speed ? speed.toFixed(1) : '0.0');

    if (speedLimit > 0) {
        var pct = Math.min((speed / speedLimit) * 100, 100);
        bar.css('width', pct + '%');
        bar.removeClass('bg-success bg-warning bg-danger');

        if (speed <= speedLimit * 0.8) {
            bar.addClass('bg-success');
            display.css('color', '#06d6a0');
        } else if (speed <= speedLimit) {
            bar.addClass('bg-warning');
            display.css('color', '#ffd166');
        } else {
            bar.addClass('bg-danger');
            display.css('color', '#ef476f');
            triggerSpeedWarning(speed, speedLimit);
        }

        limitDisplay.html('Limit: <strong>' + speedLimit + ' km/h</strong>');
    }
}

function setSpeedLimit(limit, roadName) {
    speedLimit = limit;
    if (roadName) {
        $('#roadNameDisplay').html('Road: <strong>' + roadName + '</strong>');
    }
}

function checkSpeedViolation(speed, limit, roadName) {
    setSpeedLimit(limit, roadName);
    updateSpeedDisplay(speed);

    if (speed > limit) {
        $.post(SITE_URL + 'api/alerts/log-warning.php', {
            warning_type: 'speeding',
            message: 'Speed limit exceeded! Current: ' + speed.toFixed(1) + ' km/h, Limit: ' + limit + ' km/h',
            speed: speed,
            speed_limit: limit,
            latitude: driverMarker ? driverMarker.getPosition().lat() : 0,
            longitude: driverMarker ? driverMarker.getPosition().lng() : 0
        });
    }
}

function triggerSpeedWarning(speed, limit) {
    var now = Date.now();
    if (now - lastWarningTime < warningCooldown) return;
    lastWarningTime = now;

    var msg = 'Warning! You are exceeding the speed limit of ' + limit + ' km/h. Your current speed is ' + speed.toFixed(1) + ' km/h. Please slow down.';

    if ('speechSynthesis' in window) {
        var utterance = new SpeechSynthesisUtterance(msg);
        utterance.lang = 'en-US';
        utterance.rate = 0.9;
        utterance.pitch = 1;
        speechSynthesis.speak(utterance);
    }

    if (Notification.permission === 'granted') {
        new Notification('Speed Warning!', {
            body: msg,
            icon: '/SafeRoadAI/assets/images/warning-icon.png'
        });
    }

    showToast(msg, 'warning');
}

function processAlerts(alerts, lat, lng) {
    if (!alerts || !alerts.length) return;

    alerts.forEach(function(alert) {
        var dist = getDistanceFromLatLngInM(
            lat, lng,
            parseFloat(alert.latitude), parseFloat(alert.longitude)
        );

        if (dist <= (alert.radius_meters || 500)) {
            showAlertNotification(alert, dist);
        }
    });
}

function showAlertNotification(alert, distance) {
    var msg = alert.title + ': ' + alert.description + ' (' + Math.round(distance) + 'm ahead)';

    if ('speechSynthesis' in window) {
        var utterance = new SpeechSynthesisUtterance(msg);
        utterance.lang = 'en-US';
        utterance.rate = 0.9;
        speechSynthesis.speak(utterance);
    }

    if (Notification.permission === 'granted') {
        new Notification(alert.title, {
            body: alert.description,
            icon: '/SafeRoadAI/assets/images/alert-icon.png'
        });
    }

    showToast(msg, alert.severity === 'high' || alert.severity === 'critical' ? 'warning' : 'info');
}

function requestNotificationPermission() {
    if (!('Notification' in window)) return;
    if (Notification.permission === 'default') {
        Notification.requestPermission();
    }
}

$(document).ready(function() {
    requestNotificationPermission();
});

function getDistanceFromLatLngInM(lat1, lng1, lat2, lng2) {
    var R = 6371000;
    var dLat = toRad(lat2 - lat1);
    var dLng = toRad(lng2 - lng1);
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
            Math.sin(dLng / 2) * Math.sin(dLng / 2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

function toRad(deg) { return deg * (Math.PI / 180); }
