<?php

// --- Database connection ---
$conn = mysqli_connect("127.0.0.1", "root", "", "permdb");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch all plant analysis data, grouped by crop and week
$sql = "SELECT crop, week, chlorophyll_manual, chlorophyll_chamber, leafcount_manual, leafcount_chamber, survivability_manual, survivability_chamber
        FROM plant_analysis
        ORDER BY crop, week";
$result = mysqli_query($conn, $sql);

// Prepare arrays for each crop and metric
$crops = ['Lettuce', 'Mustasa', 'Pechay'];
$weeks = [];
$data = [];
foreach ($crops as $crop) {
    $data[$crop] = [
        'chlorophyll_manual' => [],
        'chlorophyll_chamber' => [],
        'leafcount_manual' => [],
        'leafcount_chamber' => [],
        'survivability_manual' => [],
        'survivability_chamber' => []
    ];
}

while ($row = mysqli_fetch_assoc($result)) {
    $crop = $row['crop'];
    $week = "Week " . $row['week'];
    if (!in_array($week, $weeks)) $weeks[] = $week;
    $data[$crop]['chlorophyll_manual'][] = floatval($row['chlorophyll_manual']);
    $data[$crop]['chlorophyll_chamber'][] = floatval($row['chlorophyll_chamber']);
    $data[$crop]['leafcount_manual'][] = floatval($row['leafcount_manual']);
    $data[$crop]['leafcount_chamber'][] = floatval($row['leafcount_chamber']);
    $data[$crop]['survivability_manual'][] = floatval($row['survivability_manual']);
    $data[$crop]['survivability_chamber'][] = floatval($row['survivability_chamber']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OHN Monitoring System</title>
    <link rel="stylesheet" href="styles.css">
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
        }
        .sidebar-icon {
            width: 32px;
            height: 32px;
            opacity: 0.8;
            margin-bottom: 10px;
            cursor: pointer;
            transition: opacity 0.2s;
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
        .analysis-section {
            flex: 2;
        }
        .analysis-title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 18px;
            letter-spacing: 1px;
        }
        .analysis-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-gap: 18px;
        }
        .chart-card {
            background: #232323;
            border-radius: 12px;
            padding: 18px 10px 10px 10px;
            box-shadow: 0 2px 10px #0006;
            min-height: 180px;
        }
        .chart-title {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 8px;
            text-align: center;
        }
        .parameter-controls {
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
        .controls-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 30px;
            letter-spacing: 1px;
        }
        .control-btns {
            display: flex;
            flex-direction: column;
            gap: 30px;
            align-items: center;
        }
        .circle-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: box-shadow 0.2s;
            box-shadow: 0 2px 8px #0004;
        }
        .circle-btn.power-on { background: #2196f3; color: #fff; }
        .circle-btn.power-off { background: #e74c3c; color: #fff; }
        .circle-btn.up { background: #2ecc40; color: #fff; }
        .circle-btn.down { background: #2ecc40; color: #fff; transform: rotate(180deg);}
        .circle-btn.temp { background: #f1c40f; color: #fff; }
        .control-label {
            font-size: 15px;
            margin-bottom: 18px;
            text-align: center;
        }
        @media (max-width: 1200px) {
            .analysis-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 900px) {
            .main-layout { flex-direction: column; }
            .sidebar { flex-direction: row; height: 70px; width: 100vw; padding: 0; gap: 20px;}
            .content-area { flex-direction: column; padding: 10px; gap: 10px;}
            .parameter-controls { min-width: unset; width: 100%; margin-top: 20px;}
            .analysis-section { width: 100%; }
        }
    </style>
    <!-- Chart.js CDN for demo -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OHN Monitoring System</title>
    <link rel="stylesheet" href="styles.css">
    <!-- ...existing style and links... -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* ...existing CSS... */
    </style>
</head>
<body>
    <div class="header">
        <div style="display:flex;align-items:center;">
            <img src="img/temp.png" alt="Logo">
            <span class="header-title">ORIENTAL HERBAL NUTRIENT (OHN) MONITORING SYSTEM</span>
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
            <div class="analysis-section">
                <div class="analysis-title">COMPARATIVE ANALYSIS</div>
                <div class="analysis-grid">
                    <div class="chart-card">
                        <div class="chart-title">Lettuce Chlorophyll Index</div>
                        <canvas id="chart1"></canvas>
                    </div>
                    <div class="chart-card">
                        <div class="chart-title">Mustasa Chlorophyll Index</div>
                        <canvas id="chart2"></canvas>
                    </div>
                    <div class="chart-card">
                        <div class="chart-title">Pechay Chlorophyll Index</div>
                        <canvas id="chart3"></canvas>
                    </div>
                    <div class="chart-card">
                        <div class="chart-title">Lettuce Leaf Count</div>
                        <canvas id="chart4"></canvas>
                    </div>
                    <div class="chart-card">
                        <div class="chart-title">Mustasa Leaf Count</div>
                        <canvas id="chart5"></canvas>
                    </div>
                    <div class="chart-card">
                        <div class="chart-title">Pechay Leaf Count</div>
                        <canvas id="chart6"></canvas>
                    </div>
                    <div class="chart-card">
                        <div class="chart-title">Lettuce Survivability Rate</div>
                        <canvas id="chart7"></canvas>
                    </div>
                    <div class="chart-card">
                        <div class="chart-title">Mustasa Survivability Rate</div>
                        <canvas id="chart8"></canvas>
                    </div>
                    <div class="chart-card">
                        <div class="chart-title">Pechay Survivability Rate</div>
                        <canvas id="chart9"></canvas>
                    </div>
                </div>
            </div>
            <div class="parameter-controls">
                <div class="controls-title">PARAMETER CONTROLS</div>
                <div class="control-btns">
                    <div>
                        <button class="circle-btn power-on"><span>&#128263;</span></button>
                        <div class="control-label">System Power</div>
                    </div>
                    <div>
                        <button class="circle-btn up">&#9650;</button>
                        <button class="circle-btn down">&#9650;</button>
                        <div class="control-label">Mixing Intervals</div>
                    </div>
                    <div>
                        <button class="circle-btn temp">&#9889;</button>
                        <div class="control-label">Temp Controller<br>Threshold</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Data from PHP
        const weeks = <?php echo json_encode($weeks); ?>;

        // Lettuce
        const lettuceChlManual = <?php echo json_encode($data['Lettuce']['chlorophyll_manual']); ?>;
        const lettuceChlChamber = <?php echo json_encode($data['Lettuce']['chlorophyll_chamber']); ?>;
        const lettuceLeafManual = <?php echo json_encode($data['Lettuce']['leafcount_manual']); ?>;
        const lettuceLeafChamber = <?php echo json_encode($data['Lettuce']['leafcount_chamber']); ?>;
        const lettuceSurvManual = <?php echo json_encode($data['Lettuce']['survivability_manual']); ?>;
        const lettuceSurvChamber = <?php echo json_encode($data['Lettuce']['survivability_chamber']); ?>;

        // Mustasa
        const mustasaChlManual = <?php echo json_encode($data['Mustasa']['chlorophyll_manual']); ?>;
        const mustasaChlChamber = <?php echo json_encode($data['Mustasa']['chlorophyll_chamber']); ?>;
        const mustasaLeafManual = <?php echo json_encode($data['Mustasa']['leafcount_manual']); ?>;
        const mustasaLeafChamber = <?php echo json_encode($data['Mustasa']['leafcount_chamber']); ?>;
        const mustasaSurvManual = <?php echo json_encode($data['Mustasa']['survivability_manual']); ?>;
        const mustasaSurvChamber = <?php echo json_encode($data['Mustasa']['survivability_chamber']); ?>;

        // Pechay
        const pechayChlManual = <?php echo json_encode($data['Pechay']['chlorophyll_manual']); ?>;
        const pechayChlChamber = <?php echo json_encode($data['Pechay']['chlorophyll_chamber']); ?>;
        const pechayLeafManual = <?php echo json_encode($data['Pechay']['leafcount_manual']); ?>;
        const pechayLeafChamber = <?php echo json_encode($data['Pechay']['leafcount_chamber']); ?>;
        const pechaySurvManual = <?php echo json_encode($data['Pechay']['survivability_manual']); ?>;
        const pechaySurvChamber = <?php echo json_encode($data['Pechay']['survivability_chamber']); ?>;

        // Chart rendering
        new Chart(document.getElementById('chart1').getContext('2d'), {
            type: 'line',
            data: {
                labels: weeks,
                datasets: [
                    { label: 'Manual OHN', data: lettuceChlManual, borderColor: '#f1c40f', fill: false },
                    { label: 'Chamber OHN', data: lettuceChlChamber, borderColor: '#2196f3', fill: false }
                ]
            },
            options: { plugins: { legend: { labels: { color: '#fff' } } }, scales: { x: { ticks: { color: '#fff' } }, y: { ticks: { color: '#fff' }, beginAtZero: true } } }
        });
        new Chart(document.getElementById('chart2').getContext('2d'), {
            type: 'line',
            data: {
                labels: weeks,
                datasets: [
                    { label: 'Manual OHN', data: mustasaChlManual, borderColor: '#f1c40f', fill: false },
                    { label: 'Chamber OHN', data: mustasaChlChamber, borderColor: '#2196f3', fill: false }
                ]
            },
            options: { plugins: { legend: { labels: { color: '#fff' } } }, scales: { x: { ticks: { color: '#fff' } }, y: { ticks: { color: '#fff' }, beginAtZero: true } } }
        });
        new Chart(document.getElementById('chart3').getContext('2d'), {
            type: 'line',
            data: {
                labels: weeks,
                datasets: [
                    { label: 'Manual OHN', data: pechayChlManual, borderColor: '#f1c40f', fill: false },
                    { label: 'Chamber OHN', data: pechayChlChamber, borderColor: '#2196f3', fill: false }
                ]
            },
            options: { plugins: { legend: { labels: { color: '#fff' } } }, scales: { x: { ticks: { color: '#fff' } }, y: { ticks: { color: '#fff' }, beginAtZero: true } } }
        });
        new Chart(document.getElementById('chart4').getContext('2d'), {
            type: 'line',
            data: {
                labels: weeks,
                datasets: [
                    { label: 'Manual OHN', data: lettuceLeafManual, borderColor: '#f1c40f', fill: false },
                    { label: 'Chamber OHN', data: lettuceLeafChamber, borderColor: '#2196f3', fill: false }
                ]
            },
            options: { plugins: { legend: { labels: { color: '#fff' } } }, scales: { x: { ticks: { color: '#fff' } }, y: { ticks: { color: '#fff' }, beginAtZero: true } } }
        });
        new Chart(document.getElementById('chart5').getContext('2d'), {
            type: 'line',
            data: {
                labels: weeks,
                datasets: [
                    { label: 'Manual OHN', data: mustasaLeafManual, borderColor: '#f1c40f', fill: false },
                    { label: 'Chamber OHN', data: mustasaLeafChamber, borderColor: '#2196f3', fill: false }
                ]
            },
            options: { plugins: { legend: { labels: { color: '#fff' } } }, scales: { x: { ticks: { color: '#fff' } }, y: { ticks: { color: '#fff' }, beginAtZero: true } } }
        });
        new Chart(document.getElementById('chart6').getContext('2d'), {
            type: 'line',
            data: {
                labels: weeks,
                datasets: [
                    { label: 'Manual OHN', data: pechayLeafManual, borderColor: '#f1c40f', fill: false },
                    { label: 'Chamber OHN', data: pechayLeafChamber, borderColor: '#2196f3', fill: false }
                ]
            },
            options: { plugins: { legend: { labels: { color: '#fff' } } }, scales: { x: { ticks: { color: '#fff' } }, y: { ticks: { color: '#fff' }, beginAtZero: true } } }
        });
        new Chart(document.getElementById('chart7').getContext('2d'), {
            type: 'line',
            data: {
                labels: weeks,
                datasets: [
                    { label: 'Manual OHN', data: lettuceSurvManual, borderColor: '#f1c40f', fill: false },
                    { label: 'Chamber OHN', data: lettuceSurvChamber, borderColor: '#2196f3', fill: false }
                ]
            },
            options: { plugins: { legend: { labels: { color: '#fff' } } }, scales: { x: { ticks: { color: '#fff' } }, y: { ticks: { color: '#fff' }, beginAtZero: true } } }
        });
        new Chart(document.getElementById('chart8').getContext('2d'), {
            type: 'line',
            data: {
                labels: weeks,
                datasets: [
                    { label: 'Manual OHN', data: mustasaSurvManual, borderColor: '#f1c40f', fill: false },
                    { label: 'Chamber OHN', data: mustasaSurvChamber, borderColor: '#2196f3', fill: false }
                ]
            },
            options: { plugins: { legend: { labels: { color: '#fff' } } }, scales: { x: { ticks: { color: '#fff' } }, y: { ticks: { color: '#fff' }, beginAtZero: true } } }
        });
        new Chart(document.getElementById('chart9').getContext('2d'), {
            type: 'line',
            data: {
                labels: weeks,
                datasets: [
                    { label: 'Manual OHN', data: pechaySurvManual, borderColor: '#f1c40f', fill: false },
                    { label: 'Chamber OHN', data: pechaySurvChamber, borderColor: '#2196f3', fill: false }
                ]
            },
            options: { plugins: { legend: { labels: { color: '#fff' } } }, scales: { x: { ticks: { color: '#fff' } }, y: { ticks: { color: '#fff' }, beginAtZero: true } } }
        });
    </script>
</body>
</html>