$(document).ready(function() {
    loadActiveAlerts();
    setInterval(loadActiveAlerts, 60000);
});

function loadActiveAlerts() {
    var container = $('#alertsContainer');
    if (!container.length) return;

    $.get(SITE_URL + 'api/alerts/active.php', function(data) {
        container.empty();
        if (data.length === 0) {
            container.html('<div class="col-12"><p class="text-muted">No active alerts in your area</p></div>');
            return;
        }
        data.forEach(function(alert) {
            var iconMap = {
                'accident': 'car-crash',
                'road_closure': 'road',
                'construction': 'hard-hat',
                'flood': 'water',
                'hazard': 'skull-crossbones',
                'weather': 'cloud-rain',
                'traffic_jam': 'traffic-light',
                'school_zone': 'school',
                'hospital_zone': 'hospital',
                'dangerous_curve': 'road',
                'checkpoint': 'shield-alt'
            };
            var icon = iconMap[alert.alert_type] || 'exclamation-triangle';
            var severityClass = alert.severity === 'critical' ? 'danger' : alert.severity;
            container.append(
                '<div class="col-md-4 col-lg-3">' +
                '<div class="alert-card p-3 border rounded ' + alert.severity + '" data-id="' + alert.id + '">' +
                '<div class="d-flex align-items-center mb-2">' +
                '<i class="fas fa-' + icon + ' me-2 fa-lg text-' + severityClass + '"></i>' +
                '<small class="fw-bold">' + escapeHtml(alert.title) + '</small>' +
                '<span class="badge bg-' + severityClass + ' ms-auto">' + alert.severity + '</span>' +
                '</div>' +
                '<small class="text-muted d-block">' + escapeHtml(alert.description || '').substring(0, 80) + '...</small>' +
                '<small class="text-muted d-block mt-1">' + timeAgo(alert.created_at) + '</small>' +
                '</div></div>'
            );
        });
    }).fail(function() {
        container.html('<div class="col-12"><p class="text-muted">Failed to load alerts</p></div>');
    });
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}
