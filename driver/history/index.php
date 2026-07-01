<?php
define('PAGE_TITLE', 'Driving History');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();

$db = Database::getInstance();
$userId = $_SESSION['user_id'];

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$stmt = $db->prepare("SELECT COUNT(*) FROM driving_history WHERE user_id = ?");
$stmt->execute([$userId]);
$total = $stmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$stmt = $db->prepare("SELECT * FROM driving_history WHERE user_id = ? ORDER BY started_at DESC LIMIT ? OFFSET ?");
$stmt->execute([$userId, $perPage, $offset]);
$history = $stmt->fetchAll();

$stmt = $db->prepare("SELECT DATE(created_at) as date, COUNT(*) as count, AVG(speed) as avg_speed, MAX(speed) as max_speed FROM warning_logs WHERE user_id = ? GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 30");
$stmt->execute([$userId]);
$warningStats = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>
<div class="container-fluid">
    <h4 class="fw-bold mb-4"><i class="fas fa-history me-2 text-primary"></i>Driving History</h4>

    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Trip History</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($history)): ?>
                    <div class="p-4 text-center text-muted"><i class="fas fa-road fa-3x mb-3"></i><p>No trip history yet. Start driving to record your trips!</p></div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Date</th><th>Distance</th><th>Avg Speed</th><th>Max Speed</th><th>Duration</th><th>Warnings</th></tr></thead>
                            <tbody>
                                <?php foreach ($history as $h): ?>
                                <tr>
                                    <td><?php echo date('M d, H:i', strtotime($h['started_at'])); ?></td>
                                    <td><strong><?php echo formatDistance($h['distance_km']); ?></strong></td>
                                    <td><?php echo $h['avg_speed'] ? number_format($h['avg_speed'], 1) . ' km/h' : '--'; ?></td>
                                    <td><span class="text-danger"><?php echo $h['max_speed'] ? number_format($h['max_speed'], 1) . ' km/h' : '--'; ?></span></td>
                                    <td><?php echo $h['duration_minutes'] ? formatDuration($h['duration_minutes']) : '--'; ?></td>
                                    <td><span class="badge bg-<?php echo $h['warnings_count'] > 0 ? 'warning' : 'success'; ?>"><?php echo $h['warnings_count']; ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($totalPages > 1): ?>
                    <div class="card-footer bg-white">
                        <nav><ul class="pagination pagination-sm mb-0 justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                        </ul></nav>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Warning Stats</h5>
                </div>
                <div class="card-body">
                    <canvas id="warningChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$chartLabels = array_reverse(array_column($warningStats, 'date'));
$chartData = array_reverse(array_column($warningStats, 'count'));
$extraScripts = '
<script>
var warningLabels = ' . json_encode($chartLabels) . ';
var warningData = ' . json_encode($chartData) . ';
$(document).ready(function() {
    var ctx = document.getElementById("warningChart");
    if (ctx) {
        new Chart(ctx, {
            type: "bar",
            data: {
                labels: warningLabels.map(function(d) { return d.substring(5); }),
                datasets: [{
                    label: "Warnings",
                    data: warningData,
                    backgroundColor: "rgba(239,71,111,0.7)",
                    borderColor: "#ef476f",
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { display: false } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
';
include __DIR__ . '/../../includes/footer.php';
?>
