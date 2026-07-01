$(document).ready(function() {
    initDarkMode();
    initNotifications();
    initSelect2();
    initTooltips();
    initSidebar();
});

function initDarkMode() {
    if (localStorage.getItem('dark_mode') === '1') {
        $('body').addClass('dark-mode');
        $('#toggleDarkMode').html('<i class="fas fa-sun me-2"></i>Light Mode');
    }
    $(document).on('click', '#toggleDarkMode', function(e) {
        e.preventDefault();
        $('body').toggleClass('dark-mode');
        const isDark = $('body').hasClass('dark-mode');
        localStorage.setItem('dark_mode', isDark ? '1' : '0');
        document.cookie = 'dark_mode=' + (isDark ? '1' : '0') + ';path=/';
        $(this).html(isDark ? '<i class="fas fa-sun me-2"></i>Light Mode' : '<i class="fas fa-moon me-2"></i>Dark Mode');
    });
}

function initNotifications() {
    function loadNotifications() {
        $.get(SITE_URL + 'api/auth/notifications.php', function(data) {
            const notifList = $('#notifList');
            notifList.empty();
            if (data.length === 0) {
                notifList.html('<p class="text-muted small px-3">No notifications</p>');
                $('#notifBadge').text('0').hide();
            } else {
                $('#notifBadge').text(data.length).show();
                data.forEach(function(n) {
                    const icon = n.type === 'warning' ? 'exclamation-triangle text-warning' :
                                n.type === 'success' ? 'check-circle text-success' :
                                n.type === 'danger' ? 'times-circle text-danger' : 'info-circle text-info';
                    notifList.append(
                        '<div class="dropdown-item border-bottom py-2"><div class="d-flex align-items-start">' +
                        '<i class="fas fa-' + icon + ' me-2 mt-1"></i>' +
                        '<div><strong>' + n.title + '</strong><br><small>' + n.message + '</small><br>' +
                        '<small class="text-muted">' + timeAgo(n.created_at) + '</small></div></div></div>'
                    );
                });
            }
        }).fail(function() { $('#notifBadge').text('0').hide(); });
    }
    loadNotifications();
    setInterval(loadNotifications, 30000);
}

function initSelect2() {
    $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
}

function initTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(el) { return new bootstrap.Tooltip(el); });
}

function initSidebar() {
    $('#sidebarToggle').on('click', function() { $('.sidebar').toggleClass('show'); });
}

function timeAgo(timestamp) {
    var date = new Date(timestamp.replace(' ', 'T') + 'Z');
    var seconds = Math.floor((new Date() - date) / 1000);
    if (seconds < 60) return 'just now';
    var minutes = Math.floor(seconds / 60);
    if (minutes < 60) return minutes + ' min ago';
    var hours = Math.floor(minutes / 60);
    if (hours < 24) return hours + ' hours ago';
    var days = Math.floor(hours / 24);
    if (days < 7) return days + ' days ago';
    return timestamp.split(' ')[0];
}

function showToast(message, type) {
    type = type || 'info';
    var bg = type === 'error' ? 'bg-danger' : type === 'success' ? 'bg-success' : type === 'warning' ? 'bg-warning' : 'bg-primary';
    var html = '<div class="toast align-items-center text-white ' + bg + ' border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
        '<div class="d-flex"><div class="toast-body">' + message + '</div>' +
        '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>';
    var container = $('#toastContainer');
    if (!container.length) {
        container = $('<div id="toastContainer" class="toast-container position-fixed bottom-0 end-0 p-3"></div>');
        $('body').append(container);
    }
    container.append(html);
    var toast = new bootstrap.Toast(container.find('.toast').last()[0], { delay: 4000 });
    toast.show();
}

function confirmAction(message, callback) {
    if (confirm(message)) { callback(); }
}

function formatDate(dateStr) {
    var d = new Date(dateStr);
    return d.toLocaleDateString('en-IN', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}
