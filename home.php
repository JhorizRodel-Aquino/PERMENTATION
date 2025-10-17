<?php
// --- Database connection and data fetch ---
$conn = mysqli_connect("127.0.0.1", "root", "", "permdb");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch last 7 records for chart (latest first)
$query = "SELECT temp, hum, ph, lc, datetimelog FROM datalogs ORDER BY datetimelog DESC LIMIT 7";
$result = mysqli_query($conn, $query);

$chartLabels = [];
$tempData = [];
$phData = [];
$humData = [];
$denData = [];
$paramRow = null;
$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}
$rows = array_reverse($rows); // Oldest first for chart
foreach ($rows as $row) {
    $chartLabels[] = date('D', strtotime($row['datetimelog']));
    $tempData[] = floatval($row['temp']);
    $phData[] = floatval($row['ph']);
    $humData[] = floatval($row['hum']);
    $denData[] = floatval($row['lc']);
}
$paramRow = end($rows); // Latest row for Chamber OHN

// Fetch manual averages for Manual OHN
$dateNow = date("Y-m-d H:i:s", strtotime('-1 Hours'));
$mn = "SELECT AVG(temp) as manTmp, AVG(hum) as hmMan, AVG(ph) as manp, AVG(lc) as manlc 
       FROM datalogs WHERE datetimelog > '$dateNow'";
$mnRST = mysqli_query($conn, $mn);
$rw1 = mysqli_fetch_assoc($mnRST);

$manTmp = isset($rw1['manTmp']) ? round($rw1['manTmp'], 2) : 0;
$hmMan = isset($rw1['hmMan']) ? round($rw1['hmMan'], 2) : 0;
$manp = isset($rw1['manp']) ? round($rw1['manp'], 2) : 0;
$manlc = isset($rw1['manlc']) ? round($rw1['manlc'], 3) : 0;

// Chamber OHN (latest row)
$chTemp = isset($paramRow['temp']) ? round($paramRow['temp'], 2) : 0;
$chPh   = isset($paramRow['ph']) ? round($paramRow['ph'], 2) : 0;
$chHum  = isset($paramRow['hum']) ? round($paramRow['hum'], 2) : 0;
$chDen  = isset($paramRow['lc']) ? round($paramRow['lc'], 3) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OHN Monitoring System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            background: #181818;
            color: #fff;
            font-family: Arial, sans-serif;
        }
        .header {
            background: linear-gradient(to right, #0d0d0ddb, #c9af04 90%);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            height: 70px;
        }
        .header img {
            height: 50px;
            margin-right: 15px;
        }
        .header-title {
            font-size: 26px;
            font-weight: bold;
            letter-spacing: 1px;
        }/* Add this to your <style> section or CSS file */

.device-controls {
    display: flex;
    flex-direction: column;
    gap: 18px;
    margin-top: 20px;
    width: 100%;
    align-items: flex-start;
}

.control-group {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 10px;
}

.control-label {
    font-size: 16px;
    font-weight: bold;
    min-width: 60px;
    color: #f1c40f;
    letter-spacing: 1px;
}

.btn {
    padding: 7px 18px;
    border: none;
    border-radius: 6px;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
    margin-left: 5px;
    transition: background 0.2s, color 0.2s, box-shadow 0.2s;
    box-shadow: 0 2px 8px #0003;
}

.btn-on {
    background: #2ecc40;
    color: #fff;
}

.btn-off {
    background: #e74c3c;
    color: #fff;
}

.btn:active {
    box-shadow: 0 1px 3px #0006 inset;
}
        .header-date {
            font-size: 18px;
        }
        .main-layout {
            display: flex;
            height: calc(100vh - 70px);
        }
        .sidebar {
            width: 70px;
            background: #232323;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 30px;
            gap: 30px;
            flex-shrink: 0;
            box-sizing: border-box;
        }

        .sidebar-icon {
            width: 32px;
            height: 32px;
            opacity: 0.8;
            margin-bottom: 10px;
            cursor: pointer;
            transition: opacity 0.2s;
            max-width: 100%;
        }

        .sidebar-icon:hover {
            opacity: 1;
        }
        .content-area {
            flex: 1;
            display: flex;
            flex-direction: row;
            padding: 30px 30px 0 30px;
            gap: 30px;
        }
        .dashboard-section {
            flex: 2;
        }
        .dashboard-title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 18px;
            letter-spacing: 1px;
        }
        .overview-card {
            background: #232323;
            border-radius: 12px;
            padding: 18px 18px 10px 18px;
            box-shadow: 0 2px 10px #0006;
            margin-bottom: 18px;
        }
        .overview-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .overview-header .legend {
            display: flex;
            gap: 18px;
            font-size: 14px;
        }
        .legend-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 6px;
        }
        .see-all {
            font-size: 13px;
            color: #aaa;
            cursor: pointer;
        }
        .fermentation-card {
            background: #232323;
            border-radius: 12px;
            padding: 18px 18px 10px 18px;
            box-shadow: 0 2px 10px #0006;
            margin-bottom: 18px;
        }
        .fermentation-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .fermentation-list {
            font-size: 15px;
            color: #eee;
            margin: 0;
            padding-left: 0;
            list-style: none;
        }
        .fermentation-list li {
            margin-bottom: 6px;
        }
        .parameters-section {
            flex: 1;
            background: #232323;
            border-radius: 12px;
            padding: 25px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 260px;
            box-shadow: 0 2px 10px #0006;
        }
        .parameters-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 30px;
            letter-spacing: 1px;
        }
        .param-cards {
            display: flex;
            gap: 18px;
            width: 100%;
            justify-content: center;
        }
        .param-card {
            background: #181818;
            border-radius: 12px;
            padding: 12px 10px 10px 10px;
            box-shadow: 0 2px 10px #0004;
            width: 140px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 18px;
        }
        .param-card h4 {
            margin: 0 0 10px 0;
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .param-row {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
        }
        .param-box {
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 6px;
            width: 100%;
            justify-content: flex-start;
        }
        .param-temp { background: #e74c3c; color: #fff; }
        .param-ph   { background: #f1c40f; color: #222; }
        .param-hum  { background: #2196f3; color: #fff; }
        .param-den  { background: #2ecc40; color: #fff; }
        .param-icon {
            font-size: 22px;
            margin-right: 8px;
        }
        @media (max-width: 1200px) {
            .param-cards { flex-direction: column; align-items: center; }
            .dashboard-section, .parameters-section { width: 100%; }
        }
        @media (max-width: 900px) {
            .main-layout { flex-direction: column; }
            .sidebar { flex-direction: row; height: 70px; width: 100vw; padding: 0; gap: 20px;}
            .content-area { flex-direction: column; padding: 10px; gap: 10px;}
            .parameters-section { min-width: unset; width: 100%; margin-top: 20px;}
            .dashboard-section { width: 100%; }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="header">
        <div style="display:flex;align-items:center;">
            <img src="img/temp.png" alt="Logo">
            <span class="header-title">ORIENTAL HERBAL NUTRIENT OHN MONITORING SYSTEM</span>
        </div>
        <span class="header-date"><?php echo date("F d, Y"); ?></span>
    </div>
    <div class="main-layout">
        <div class="sidebar">
            <a href="home.php"><i class="fas fa-home sidebar-icon"></i></a>
            <a href="analysis.php"><i class="fas fa-chart-line sidebar-icon"></i></a>
            <a href="predictions.php"><i class="fas fa-chart-bar sidebar-icon"></i></a>
            <a href="newplant.php"><i class="fas fa-gear sidebar-icon"></i></a>
        </div>
        <div class="content-area">
            <div class="dashboard-section">
                <div class="dashboard-title">DASHBOARD</div>
                <div class="overview-card">
                    <div class="overview-header">
                        <span>Overview</span>
                        <div class="legend">
                            <span><span class="legend-dot" style="background:#e74c3c"></span>Temp</span>
                            <span><span class="legend-dot" style="background:#f1c40f"></span>pH Level</span>
                            <span><span class="legend-dot" style="background:#2196f3"></span>Humidity</span>
                            <span><span class="legend-dot" style="background:#2ecc40"></span>Density</span>
                        </div>
                        <span class="see-all">See All &gt;</span>
                    </div>
                    <canvas id="mainChart" height="80"></canvas>
                </div>
                <div class="fermentation-card">
                    <div class="fermentation-header">
                        <span>Fermentation History</span>
                        <span class="see-all">See All &gt;</span>
                    </div>
                    <ul class="fermentation-list">
                        <li>02/27/2025 - No Issue</li>
                        <li>02/28/2025 - No Issue</li>
                        <li>03/01/2025 - Vinegar In</li>
                        <li>03/02/2025 - No Issue</li>
                        <li>03/03/2025 - No Issue</li>
                    </ul>
                </div>
            </div>

            <div class="parameters-section">
                <div class="parameters-title">PARAMETERS</div>
                <div class="param-cards">
                    <div class="param-card">
                        <h4>Manual OHN</h4>
                        <div class="param-row">
                            <div class="param-box param-temp"><i class="fas fa-thermometer-half param-icon"></i><?php echo $manTmp; ?> °C</div>
                            <div class="param-box param-ph"><i class="fas fa-vial param-icon"></i><?php echo $manp; ?></div>
                            <div class="param-box param-hum"><i class="fas fa-tint param-icon"></i><?php echo $hmMan; ?>%</div>
                            <div class="param-box param-den"><i class="fas fa-leaf param-icon"></i><?php echo $manlc; ?> G/ML</div>
                        </div>
                    </div>
                    <div class="param-card">
                        <h4>Chamber OHN</h4>
                        <div class="param-row">
                            <div class="param-box param-temp"><i class="fas fa-thermometer-half param-icon"></i><?php echo $chTemp; ?> °C</div>
                            <div class="param-box param-ph"><i class="fas fa-vial param-icon"></i><?php echo $chPh; ?></div>
                            <div class="param-box param-hum"><i class="fas fa-tint param-icon"></i><?php echo $chHum; ?>%</div>
                            <div class="param-box param-den"><i class="fas fa-leaf param-icon"></i><?php echo $chDen; ?> G/ML</div>
                        </div>
                    </div>
                </div>
                <div class="device-controls" style="margin-top:20px;">
                    <div class="control-group">
                        <span class="control-label">LED:</span>
                        <?php
                        $sqlLed = "SELECT led FROM control LIMIT 1";
                        $queryLed = mysqli_query($conn, $sqlLed);
                        $rwLed = mysqli_fetch_assoc($queryLed);
                        if ($rwLed['led'] == 1) {
                            ?>
                            <a href="control.php?led=0">
                                <button class="btn btn-off">LED OFF</button>
                            </a>
                        <?php } else { ?>
                            <a href="control.php?led=1">
                                <button class="btn btn-on">LED ON</button>
                            </a>
                        <?php } ?>
                    </div>
                    <div class="control-group">
                        <span class="control-label">Motor:</span>
                        <?php
                        $msql = "SELECT motor FROM control LIMIT 1";
                        $querym = mysqli_query($conn, $msql);
                        $rwm = mysqli_fetch_assoc($querym);
                        if ($rwm['motor'] == 1) {
                            ?>
                            <a href="control.php?motor=0">
                                <button class="btn btn-off">Motor OFF</button>
                            </a>
                        <?php } else { ?>
                            <a href="control.php?motor=1">
                                <button class="btn btn-on">Motor ON</button>
                            </a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Chart data from PHP
        const days = <?php echo json_encode($chartLabels); ?>;
        const tempData = <?php echo json_encode($tempData); ?>;
        const phData = <?php echo json_encode($phData); ?>;
        const humData = <?php echo json_encode($humData); ?>;
        const denData = <?php echo json_encode($denData); ?>;

        new Chart(document.getElementById('mainChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: days,
                datasets: [
                    { label: 'Temp', data: tempData, borderColor: '#e74c3c', backgroundColor: 'transparent', fill: false },
                    { label: 'pH Level', data: phData, borderColor: '#f1c40f', backgroundColor: 'transparent', fill: false },
                    { label: 'Humidity', data: humData, borderColor: '#2196f3', backgroundColor: 'transparent', fill: false },
                    { label: 'Density', data: denData, borderColor: '#2ecc40', backgroundColor: 'transparent', fill: false }
                ]
            },
            options: {
                plugins: { legend: { labels: { color: '#fff' } } },
                scales: {
                    x: { ticks: { color: '#fff' } },
                    y: { ticks: { color: '#fff' }, beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>