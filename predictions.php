<?php
// --- Database connection ---
$conn = mysqli_connect("127.0.0.1", "root", "", "permdb");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OHN Monitoring System - Growth Predictions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Base styles for all screen sizes */
        body {
            margin: 0;
            background: #181818;
            color: #fff;
            font-family: Arial, sans-serif;
            overflow-x: hidden;
            width: 100%;
        }

        .header {
            background: linear-gradient(to right, #0d0d0ddb, #c9af04 90%);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            height: 70px;
            flex-wrap: wrap;
            width: 100%;
            box-sizing: border-box;
        }

        .header img {
            height: 50px;
            margin-right: 15px;
            max-width: 100%;
        }

        .header-title {
            font-size: 26px;
            font-weight: bold;
            letter-spacing: 1px;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .header-date {
            font-size: 18px;
            white-space: nowrap;
        }

        .main-layout {
            display: flex;
            min-height: calc(100vh - 70px);
            width: 100%;
            box-sizing: border-box;
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

        /* Prediction-specific styles */
        .prediction-content {
            flex: 1;
            padding: 30px;
            background: #181818;
            overflow-x: hidden;
            width: 100%;
            box-sizing: border-box;
            max-width: 100%;
        }

        .prediction-header {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 20px;
            letter-spacing: 1px;
            color: #c9af04;
            width: 100%;
            box-sizing: border-box;
        }

        /* Plant Tabs */
        .plant-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            width: 100%;
            box-sizing: border-box;
        }

        .plant-tab {
            background: #232323;
            padding: 15px 25px;
            border-radius: 8px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            font-weight: 600;
            color: #c9af04;
            flex: 1;
            min-width: 120px;
            text-align: center;
            box-sizing: border-box;
            max-width: 100%;
        }

        .plant-tab:hover {
            background: #2a2a2a;
            border-color: #c9af04;
        }

        .plant-tab.active {
            background: #c9af04;
            color: #181818;
            border-color: #c9af04;
        }

        .plant-content {
            display: none;
            width: 100%;
            box-sizing: border-box;
        }

        .plant-content.active {
            display: block;
        }

        /* Metrics Grid */
        .metrics-grid {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            width: 100%;
            box-sizing: border-box;
            max-width: 100%;
        }

        .metric-card {
            background: #232323;
            padding: 20px;
            border-radius: 10px;
            border-left: 5px solid #c9af04;
            box-shadow: 0 3px 10px rgba(0,0,0,0.3);
            flex: 1 1 300px;
            min-width: 0;
            max-width: 100%;
            box-sizing: border-box;
            overflow: hidden;
        }

        .metric-card h3 {
            color: #c9af04;
            margin-bottom: 15px;
            font-size: 1.2em;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .metric-card p {
            margin: 8px 0;
            color: #ccc;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .metric-card .value {
            font-weight: bold;
            color: #c9af04;
        }

        /* Charts Container */
        .charts-container {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            width: 100%;
            flex-wrap: wrap;
            box-sizing: border-box;
            max-width: 100%;
        }

        .chart-wrapper {
            background: #232323;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.3);
            flex: 1 1 350px;
            min-width: 0;
            max-width: 100%;
            box-sizing: border-box;
        }

        .chart-title {
            font-size: 1.2em;
            color: #c9af04;
            margin-bottom: 15px;
            text-align: center;
            font-weight: 600;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chart-container {
            height: 300px;
            width: 100%;
            min-height: 250px;
            position: relative;
            box-sizing: border-box;
        }

        /* Ensure Chart.js respects container */
        .chart-container canvas {
            max-width: 100% !important;
            height: auto !important;
        }

        .legend {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
            flex-wrap: wrap;
            width: 100%;
            box-sizing: border-box;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8em;
            color: #ccc;
            max-width: 100%;
            overflow: hidden;
        }

        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 3px;
            flex-shrink: 0;
        }

        /* Table */
        .table-container {
            background: #232323;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.3);
            overflow-x: auto;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            -webkit-overflow-scrolling: touch;
        }

        .table-title {
            font-size: 1.4em;
            color: #c9af04;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
            width: 100%;
            box-sizing: border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #232323;
            table-layout: auto;
            min-width: 800px;
            max-width: 100%;
            box-sizing: border-box;
        }

        th {
            background: linear-gradient(135deg, #c9af04, #b39b00);
            color: #181818;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            white-space: nowrap;
            box-sizing: border-box;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #444;
            color: #ccc;
            white-space: nowrap;
            box-sizing: border-box;
        }

        tr:nth-child(even) {
            background: #2a2a2a;
        }

        tr:hover {
            background: #333;
        }

        .actual {
            border-left: 4px solid #c9af04;
        }

        .predicted {
            border-left: 4px solid #e67e22;
        }

        .spray-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .spray-manual {
            background: #c9af04;
            color: #181818;
        }

        .spray-chamber {
            background: #666;
            color: #fff;
        }

        .loading {
            text-align: center;
            padding: 60px;
            font-size: 1.2em;
            color: #c9af04;
            width: 100%;
            box-sizing: border-box;
        }

        .error {
            background: #442222;
            color: #ff6b6b;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 4px solid #ff6b6b;
            width: 100%;
            box-sizing: border-box;
        }

        /* ===== RESPONSIVE BREAKPOINTS ===== */

        /* Large Desktop & Split Screens (1400px - 1920px) */
        @media (max-width: 1600px) {
            .header-title {
                font-size: 22px;
            }
            
            .header-date {
                font-size: 16px;
            }
            
            .prediction-content {
                padding: 25px;
            }
            
            .metric-card {
                flex: 1 1 280px;
            }
            
            .chart-wrapper {
                flex: 1 1 320px;
            }
            
            .chart-container {
                height: 280px;
            }
        }

        /* Medium Desktop & Split Screens (1200px - 1399px) */
        @media (max-width: 1400px) {
            .header {
                padding: 0 20px;
                height: 60px;
            }
            
            .header-title {
                font-size: 20px;
            }
            
            .header img {
                height: 40px;
            }
            
            .prediction-content {
                padding: 20px;
            }
            
            .prediction-header {
                font-size: 20px;
            }
            
            .charts-container {
                gap: 15px;
            }
            
            .metric-card {
                flex: 1 1 250px;
                padding: 15px;
            }
            
            .chart-wrapper {
                flex: 1 1 300px;
                padding: 15px;
            }
            
            .chart-container {
                height: 250px;
            }
            
            .plant-tab {
                padding: 12px 20px;
                min-width: 100px;
            }
        }

        /* Small Desktop & Large Tablets (992px - 1199px) */
        @media (max-width: 1200px) {
            .header-title {
                font-size: 18px;
            }
            
            .header-date {
                font-size: 14px;
            }
            
            .main-layout {
                flex-direction: row;
            }
            
            .sidebar {
                width: 60px;
                padding-top: 20px;
                gap: 25px;
            }
            
            .prediction-content {
                padding: 15px;
            }
            
            .charts-container {
                flex-direction: column;
            }
            
            .metric-card {
                flex: 1 1 calc(50% - 15px);
            }
            
            .chart-wrapper {
                flex: 1 1 100%;
                min-width: 100%;
            }
            
            .chart-container {
                height: 300px;
            }
            
            .metrics-grid {
                gap: 15px;
            }
            
            .table-container {
                padding: 20px;
            }
            
            table {
                min-width: 700px;
            }
        }

        /* Tablets & Small Split Screens (768px - 991px) */
        @media (max-width: 991px) {
            .header {
                flex-direction: column;
                height: auto;
                padding: 15px 20px;
                gap: 10px;
                text-align: center;
            }
            
            .header-title {
                font-size: 18px;
                order: 1;
            }
            
            .header-date {
                order: 2;
                font-size: 14px;
            }
            
            .header img {
                margin-right: 10px;
            }
            
            .main-layout {
                flex-direction: column;
                min-height: auto;
            }
            
            /* SIDEBAR FIX: Centered icons in mobile */
            .sidebar {
                flex-direction: row;
                width: 100%;
                height: 60px;
                padding: 0;
                justify-content: center;
                gap: 30px;
                align-items: center; /* Center icons vertically */
            }
            
            .sidebar-icon {
                margin-bottom: 0; /* Remove bottom margin in horizontal layout */
                margin: 0; /* Reset all margins */
            }
            
            .prediction-content {
                padding: 15px;
            }
            
            .prediction-header {
                font-size: 18px;
                text-align: center;
            }
            
            .plant-tabs {
                justify-content: center;
            }
            
            .plant-tab {
                flex: none;
                min-width: 100px;
                padding: 10px 15px;
            }
            
            .metrics-grid {
                gap: 12px;
            }
            
            .metric-card {
                flex: 1 1 100%;
                min-width: 100%;
            }
            
            .chart-container {
                height: 250px;
            }
            
            .table-container {
                padding: 15px;
                margin: 0 -15px;
                width: calc(100% + 30px);
                border-radius: 0;
            }
            
            table {
                min-width: 650px;
            }
        }

        /* Large Phones (576px - 767px) */
        @media (max-width: 767px) {
            .header {
                padding: 12px 15px;
            }
            
            .header-title {
                font-size: 16px;
            }
            
            .header img {
                height: 35px;
            }
            
            /* SIDEBAR FIX: Centered icons */
            .sidebar {
                height: 50px;
                gap: 25px;
                align-items: center; /* Center icons vertically */
            }
            
            .sidebar-icon {
                width: 28px;
                height: 28px;
                margin: 0; /* Ensure no margins */
            }
            
            .prediction-content {
                padding: 12px;
            }
            
            .prediction-header {
                font-size: 16px;
                margin-bottom: 15px;
            }
            
            .plant-tabs {
                gap: 8px;
            }
            
            .plant-tab {
                padding: 8px 12px;
                min-width: 80px;
                font-size: 0.9em;
            }
            
            .charts-container {
                gap: 12px;
                margin-bottom: 20px;
            }
            
            .metric-card {
                padding: 12px;
            }
            
            .chart-wrapper {
                padding: 12px;
            }
            
            .chart-title {
                font-size: 1.1em;
                margin-bottom: 12px;
            }
            
            .chart-container {
                height: 220px;
            }
            
            .metrics-grid {
                margin-bottom: 20px;
            }
            
            .metric-card h3 {
                font-size: 1.1em;
            }
            
            .table-container {
                padding: 12px;
                margin: 0 -12px;
                width: calc(100% + 24px);
            }
            
            table {
                min-width: 600px;
            }
            
            th, td {
                padding: 10px 8px;
                font-size: 0.9em;
            }
        }

        /* Small Phones (480px - 575px) */
        @media (max-width: 575px) {
            .header {
                padding: 10px 12px;
            }
            
            .header-title {
                font-size: 14px;
            }
            
            .header-date {
                font-size: 12px;
            }
            
            .header img {
                height: 30px;
                margin-right: 8px;
            }
            
            /* SIDEBAR FIX: Centered icons */
            .sidebar {
                height: 45px;
                gap: 20px;
                align-items: center; /* Center icons vertically */
            }
            
            .sidebar-icon {
                width: 24px;
                height: 24px;
                margin: 0; /* Ensure no margins */
            }
            
            .prediction-content {
                padding: 10px;
            }
            
            .prediction-header {
                font-size: 14px;
                margin-bottom: 12px;
            }
            
            .plant-tabs {
                gap: 6px;
                margin-bottom: 15px;
            }
            
            .plant-tab {
                padding: 6px 10px;
                min-width: 70px;
                font-size: 0.8em;
            }
            
            .charts-container {
                gap: 10px;
                margin-bottom: 15px;
            }
            
            .metric-card {
                padding: 10px;
            }
            
            .chart-wrapper {
                padding: 10px;
            }
            
            .chart-title {
                font-size: 1em;
                margin-bottom: 10px;
            }
            
            .chart-container {
                height: 200px;
            }
            
            .legend {
                gap: 10px;
                margin-top: 10px;
            }
            
            .legend-item {
                font-size: 0.75em;
            }
            
            .metrics-grid {
                gap: 10px;
                margin-bottom: 15px;
            }
            
            .metric-card h3 {
                font-size: 1em;
                margin-bottom: 10px;
            }
            
            .metric-card p {
                font-size: 0.9em;
                margin: 6px 0;
            }
            
            .table-container {
                padding: 10px;
                margin: 0 -10px;
                width: calc(100% + 20px);
            }
            
            table {
                min-width: 550px;
            }
            
            th, td {
                padding: 8px 6px;
                font-size: 0.85em;
            }
            
            .spray-badge {
                padding: 3px 8px;
                font-size: 0.8em;
            }
        }

        /* Extra Small Phones (under 480px) */
        @media (max-width: 479px) {
            .header-title {
                font-size: 13px;
            }
            
            .header-date {
                font-size: 11px;
            }
            
            /* SIDEBAR FIX: Centered icons */
            .sidebar {
                gap: 15px;
                align-items: center; /* Center icons vertically */
            }
            
            .sidebar-icon {
                width: 20px;
                height: 20px;
                margin: 0; /* Ensure no margins */
            }
            
            .plant-tab {
                min-width: 60px;
                padding: 5px 8px;
                font-size: 0.75em;
            }
            
            .chart-container {
                height: 180px;
            }
            
            table {
                min-width: 500px;
            }
            
            th, td {
                padding: 6px 4px;
                font-size: 0.8em;
            }
            
            td:nth-child(6), th:nth-child(6) {
                display: none;
            }
        }

        /* Ultra Small Phones (under 360px) */
        @media (max-width: 359px) {
            .header-title {
                font-size: 12px;
            }
            
            /* SIDEBAR FIX: Centered icons */
            .sidebar {
                gap: 15px;
                align-items: center; /* Center icons vertically */
            }
            
            .sidebar-icon {
                width: 20px;
                height: 20px;
                margin: 0; /* Ensure no margins */
            }
            
            .plant-tab {
                min-width: 50px;
                padding: 4px 6px;
                font-size: 0.7em;
            }
            
            .chart-container {
                height: 160px;
            }
            
            table {
                min-width: 450px;
            }
            
            td:nth-child(5), th:nth-child(5),
            td:nth-child(6), th:nth-child(6) {
                display: none;
            }
        }

        /* Touch device optimizations */
        @media (hover: none) and (pointer: coarse) {
            tr:hover {
                background: inherit;
            }
            
            .table-container {
                -webkit-overflow-scrolling: touch;
            }
            
            .plant-tab, .sidebar-icon {
                min-height: 44px;
                display: flex;
                align-items: center; /* Center icon content vertically */
                justify-content: center; /* Center icon content horizontally */
            }
        }

        /* High DPI screens */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .chart-container {
                height: 350px;
            }
        }
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
            <a href="predictions.php"><i class="fas fa-chart-bar sidebar-icon active"></i></a>
            <a href="newplant.php"><i class="fas fa-gear sidebar-icon"></i></a>
        </div>
        
        <div class="prediction-content">
            <div class="prediction-header">PLANT GROWTH PREDICTIONS</div>
            
            <!-- Plant Selection Tabs -->
            <div class="plant-tabs">
                <div class="plant-tab active" data-plant="plant1">🌿 Lettuce</div>
                <div class="plant-tab" data-plant="plant2">🌿 Mustasa</div>
                <div class="plant-tab" data-plant="plant3">🌿 Pechay</div>
            </div>
            
            <div id="loading" class="loading">
                <div>Loading predictions and generating charts...</div>
            </div>
            
            <!-- Plant 1 Content -->
            <div id="plant1-content" class="plant-content active">
                <div class="metrics-grid" id="plant1-modelInfo"></div>
                <div class="charts-container">
                    <div class="chart-wrapper">
                        <div class="chart-title">📊 Leaf Count Over Time</div>
                        <div class="chart-container">
                            <canvas id="plant1-leafCountChart"></canvas>
                        </div>
                        <div class="legend" id="plant1-leafcountLegend"></div>
                    </div>
                    <div class="chart-wrapper">
                        <div class="chart-title">💚 Survivability Rate (%)</div>
                        <div class="chart-container">
                            <canvas id="plant1-survivabilityChart"></canvas>
                        </div>
                        <div class="legend" id="plant1-survivabilityLegend"></div>
                    </div>
                    <div class="chart-wrapper">
                        <div class="chart-title">🍃 Chlorophyll Content</div>
                        <div class="chart-container">
                            <canvas id="plant1-chlorophyllChart"></canvas>
                        </div>
                        <div class="legend" id="plant1-chlorophyllLegend"></div>
                    </div>
                </div>
                <div class="table-container">
                    <div class="table-title">Detailed Predictions Table - Lettuce</div>
                    <div id="plant1-predictionsTable"></div>
                </div>
            </div>
            
            <!-- Plant 2 Content -->
            <div id="plant2-content" class="plant-content">
                <div class="metrics-grid" id="plant2-modelInfo"></div>
                <div class="charts-container">
                    <div class="chart-wrapper">
                        <div class="chart-title">📊 Leaf Count Over Time</div>
                        <div class="chart-container">
                            <canvas id="plant2-leafCountChart"></canvas>
                        </div>
                        <div class="legend" id="plant2-leafcountLegend"></div>
                    </div>
                    <div class="chart-wrapper">
                        <div class="chart-title">💚 Survivability Rate (%)</div>
                        <div class="chart-container">
                            <canvas id="plant2-survivabilityChart"></canvas>
                        </div>
                        <div class="legend" id="plant2-survivabilityLegend"></div>
                    </div>
                    <div class="chart-wrapper">
                        <div class="chart-title">🍃 Chlorophyll Content</div>
                        <div class="chart-container">
                            <canvas id="plant2-chlorophyllChart"></canvas>
                        </div>
                        <div class="legend" id="plant2-chlorophyllLegend"></div>
                    </div>
                </div>
                <div class="table-container">
                    <div class="table-title">Detailed Predictions Table - Mustasa</div>
                    <div id="plant2-predictionsTable"></div>
                </div>
            </div>
            
            <!-- Plant 3 Content -->
            <div id="plant3-content" class="plant-content">
                <div class="metrics-grid" id="plant3-modelInfo"></div>
                <div class="charts-container">
                    <div class="chart-wrapper">
                        <div class="chart-title">📊 Leaf Count Over Time</div>
                        <div class="chart-container">
                            <canvas id="plant3-leafCountChart"></canvas>
                        </div>
                        <div class="legend" id="plant3-leafcountLegend"></div>
                    </div>
                    <div class="chart-wrapper">
                        <div class="chart-title">💚 Survivability Rate (%)</div>
                        <div class="chart-container">
                            <canvas id="plant3-survivabilityChart"></canvas>
                        </div>
                        <div class="legend" id="plant3-survivabilityLegend"></div>
                    </div>
                    <div class="chart-wrapper">
                        <div class="chart-title">🍃 Chlorophyll Content</div>
                        <div class="chart-container">
                            <canvas id="plant3-chlorophyllChart"></canvas>
                        </div>
                        <div class="legend" id="plant3-chlorophyllLegend"></div>
                    </div>
                </div>
                <div class="table-container">
                    <div class="table-title">Detailed Predictions Table - Pechay</div>
                    <div id="plant3-predictionsTable"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Plant data configuration
        const plantConfigs = {
            plant1: { 
                dataUrl: 'predictions_lettuce.php',
                name: 'Lettuce'
            },
            plant2: { 
                dataUrl: 'predictions_mustasa.php',
                name: 'Mustasa'
            },
            plant3: { 
                dataUrl: 'predictions_pechay.php',
                name: 'Pechay'
            }
        };

        let currentPlant = 'plant1';
        let plantData = {};

        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, setting up plant tabs...');
            setupPlantTabs();
            loadPlantData('plant1');
        });

        function setupPlantTabs() {
            const tabs = document.querySelectorAll('.plant-tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const plantId = this.getAttribute('data-plant');
                    
                    // Update active tab
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Update active content
                    document.querySelectorAll('.plant-content').forEach(content => {
                        content.classList.remove('active');
                    });
                    document.getElementById(plantId + '-content').classList.add('active');
                    
                    // Load data if not already loaded
                    if (!plantData[plantId]) {
                        loadPlantData(plantId);
                    } else {
                        // Data already loaded, just display it
                        displayPlantData(plantId, plantData[plantId]);
                    }
                    
                    currentPlant = plantId;
                });
            });
        }

        function loadPlantData(plantId) {
            const config = plantConfigs[plantId];
            document.getElementById('loading').style.display = 'block';
            
            fetch(config.dataUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log(`Data loaded successfully for ${plantId}:`, data);
                    
                    if (!data.success) {
                        throw new Error('API returned error: ' + (data.error || 'Unknown error'));
                    }
                    
                    // Store data
                    plantData[plantId] = data;
                    
                    // Display data
                    displayPlantData(plantId, data);
                    
                    document.getElementById('loading').style.display = 'none';
                })
                .catch(error => {
                    console.error(`Error loading data for ${plantId}:`, error);
                    document.getElementById('loading').innerHTML = 
                        `<div class="error">Error loading data for ${config.name}: ${error.message}</div>`;
                });
        }

        function displayPlantData(plantId, data) {
            displayModelInfo(plantId, data.predictions.models_info);
            createCharts(plantId, data.chart_data);
            displayPredictionsTable(plantId, data.predictions);
        }

        function displayModelInfo(plantId, modelsInfo) {
            const container = document.getElementById(plantId + '-modelInfo');
            
            if (!modelsInfo) {
                container.innerHTML = '<div class="error">No model information available</div>';
                return;
            }
            
            // Get the plant data to access actual Week 3 values
            const plantPredictions = plantData[plantId]?.predictions?.predictions;
            
            let html = '';
            
            for (const [metric, info] of Object.entries(modelsInfo)) {
                const metricName = metric.replace(/([A-Z])/g, ' $1').trim();
                const manualValid = info.manual.valid;
                const chamberValid = info.chamber.valid;
                
                const manualSlope = manualValid ? info.manual.slope.toFixed(3) : 'N/A';
                const chamberSlope = chamberValid ? info.chamber.slope.toFixed(3) : 'N/A';
                
                const manualDataPoints = info.manual.data_points || 0;
                const chamberDataPoints = info.chamber.data_points || 0;
                
                // Calculate performance comparison
                let performanceText = 'N/A';
                let advantageText = 'N/A';
                let advantageColor = '#c9af04';
                
                if (manualValid && chamberValid && plantPredictions && plantPredictions[metric]) {
                    // Find actual Week 3 values from predictions data
                    const manualWeek3Data = plantPredictions[metric].find(
                        item => item.week === 3 && item.spray_type === 'Manual' && item.is_actual
                    );
                    const chamberWeek3Data = plantPredictions[metric].find(
                        item => item.week === 3 && item.spray_type === 'Chamber' && item.is_actual
                    );
                    
                if (manualValid && chamberValid && plantPredictions && plantPredictions[metric]) {
                    // Find FINAL WEEK 6 predicted values for comparison
                    const manualWeek6Data = plantPredictions[metric].find(
                        item => item.week === 6 && item.spray_type === 'Manual' && !item.is_actual
                    );
                    const chamberWeek6Data = plantPredictions[metric].find(
                        item => item.week === 6 && item.spray_type === 'Chamber' && !item.is_actual
                    );
                    
                    // Fallback to Week 3 actual data if Week 6 predictions not available
                    const manualWeek3Data = plantPredictions[metric].find(
                        item => item.week === 3 && item.spray_type === 'Manual' && item.is_actual
                    );
                    const chamberWeek3Data = plantPredictions[metric].find(
                        item => item.week === 3 && item.spray_type === 'Chamber' && item.is_actual
                    );

                    // Use Week 6 predictions if available, otherwise use Week 3 actual data
                    const manualFinalData = manualWeek6Data || manualWeek3Data;
                    const chamberFinalData = chamberWeek6Data || chamberWeek3Data;

                    if (manualFinalData && chamberFinalData) {
                        const manualFinal = manualFinalData.value.toFixed(metric === 'Chlorophyll' ? 3 : 1);
                        const chamberFinal = chamberFinalData.value.toFixed(metric === 'Chlorophyll' ? 3 : 1);
                        
                        const dataType = manualWeek6Data ? 'Final Week 6 Prediction' : 'Week 3 Actual';
                        performanceText = `${dataType}: Manual ${manualFinal} vs Chamber ${chamberFinal}`;
                        
                        // Calculate advantage based on FINAL results
                        const manualVal = manualFinalData.value;
                        const chamberVal = chamberFinalData.value;
                        
                        if (metric === 'Survivability' || metric === 'Chlorophyll' || metric === 'LeafCount') {
                            const difference = Math.abs(chamberVal - manualVal);
                            
                            // Determine which method has better final results
                            if (chamberVal > manualVal) {
                                const advantage = ((chamberVal - manualVal) / manualVal) * 100;
                                advantageText = `Chamber gives ${advantage.toFixed(1)}% better final results`;
                                advantageColor = '#27ae60';
                            } else if (manualVal > chamberVal) {
                                const advantage = ((manualVal - chamberVal) / chamberVal) * 100;
                                advantageText = `Manual gives ${advantage.toFixed(1)}% better final results`;
                                advantageColor = '#e67e22';
                            } else {
                                advantageText = 'Both methods give equal final results';
                                advantageColor = '#c9af04';
                            }
                        }
                    }
                }
                }
                
                html += `
                    <div class="metric-card">
                        <h3>${metricName} Analysis</h3>
                        <p><span class="value">Manual Spray:</span> ${manualSlope} units/week</p>
                        <p><span class="value">Chamber Spray:</span> ${chamberSlope} units/week</p>
                        <p style="font-size: 0.9em; color: #ccc;">${performanceText}</p>
                        <p style="color: ${advantageColor}; font-weight: 600; font-size: 1em;">
                            ${advantageText}
                        </p>
                        <p style="color: #999; font-size: 0.8em;">
                            📊 Data points: Manual (${manualDataPoints}), Chamber (${chamberDataPoints})
                            ${metric === 'Chlorophyll' ? '<br>🌱 Chlorophyll measured from Week 2' : ''}
                        </p>
                    </div>
                `;
            }
            
            container.innerHTML = html;
        }
        function createCharts(plantId, chartData) {
            console.log(`Creating charts for ${plantId} with data:`, chartData);
            
            if (!chartData) {
                console.error('No chart data available');
                return;
            }

            const chartConfigs = {
                LeafCount: {
                    title: 'Leaf Count',
                    yLabel: 'Number of Leaves',
                    colorManual: '#c9af04',    // OHN Gold for Manual
                    colorChamber: '#666666'    // Gray for Chamber
                },
                Survivability: {
                    title: 'Survivability',
                    yLabel: 'Survival Rate (%)',
                    colorManual: '#e6c44c',    // Lighter Gold
                    colorChamber: '#888888'    // Medium Gray
                },
                Chlorophyll: {
                    title: 'Chlorophyll',
                    yLabel: 'Chlorophyll Content',
                    colorManual: '#f1d97a',    // Light Gold
                    colorChamber: '#aaaaaa'    // Light Gray
                }
            };

            for (const [metric, config] of Object.entries(chartConfigs)) {
                createChart(plantId, metric, config, chartData[metric]);
            }
        }

        function createChart(plantId, metric, config, data) {
            const canvasId = plantId + '-' + metric.charAt(0).toLowerCase() + metric.slice(1) + 'Chart';
            const canvasElement = document.getElementById(canvasId);
            
            if (!canvasElement) {
                console.error('Canvas element not found:', canvasId);
                return;
            }

            const ctx = canvasElement.getContext('2d');
            
            // Destroy existing chart if it exists
            if (canvasElement.chart) {
                canvasElement.chart.destroy();
            }

            canvasElement.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.weeks.map(w => `Week ${w}`),
                    datasets: [
                        {
                            label: 'Manual Spray (Actual)',
                            data: data.manual_actual.map((val, idx) => {
                                // For chlorophyll, only show actual data from week 2 onwards
                                if (metric === 'Chlorophyll' && idx === 0) return null;
                                return idx <= 2 ? val : null;
                            }),
                            borderColor: config.colorManual,
                            backgroundColor: config.colorManual,
                            borderWidth: 3,
                            pointRadius: data.manual_actual.map((val, idx) => {
                                // For chlorophyll, no point for week 1
                                if (metric === 'Chlorophyll' && idx === 0) return 0;
                                return val === null ? 0 : (idx <= 2 ? 6 : 0);
                            }),
                            pointHoverRadius: data.manual_actual.map((val, idx) => {
                                if (metric === 'Chlorophyll' && idx === 0) return 0;
                                return val === null ? 0 : (idx <= 2 ? 8 : 0);
                            }),
                            showLine: true,
                            tension: 0.2,
                            spanGaps: false
                        },
                        {
                            label: 'Chamber Spray (Actual)',
                            data: data.chamber_actual.map((val, idx) => {
                                // For chlorophyll, only show actual data from week 2 onwards
                                if (metric === 'Chlorophyll' && idx === 0) return null;
                                return idx <= 2 ? val : null;
                            }),
                            borderColor: config.colorChamber,
                            backgroundColor: config.colorChamber,
                            borderWidth: 3,
                            pointRadius: data.chamber_actual.map((val, idx) => {
                                if (metric === 'Chlorophyll' && idx === 0) return 0;
                                return val === null ? 0 : (idx <= 2 ? 6 : 0);
                            }),
                            pointHoverRadius: data.chamber_actual.map((val, idx) => {
                                if (metric === 'Chlorophyll' && idx === 0) return 0;
                                return val === null ? 0 : (idx <= 2 ? 8 : 0);
                            }),
                            showLine: true,
                            tension: 0.2,
                            spanGaps: false
                        },
                        {
                            label: 'Manual Spray (Predicted)',
                            data: data.manual.map((val, idx) => {
                                // For ALL metrics: show predictions from week 3 onwards
                                if (idx >= 2) { // Week 3, 4, 5, 6
                                    return val;
                                }
                                // For chlorophyll: show estimated week 1 AND connect to week 2
                                if (metric === 'Chlorophyll' && idx <= 1) { // Week 1 and 2
                                    return val;
                                }
                                // For other metrics: only show from week 3
                                return null;
                            }),
                            borderColor: config.colorManual,
                            backgroundColor: config.colorManual,
                            borderWidth: 2,
                            borderDash: [5, 5], // Broken/dashed line for ALL prediction data
                            pointRadius: data.manual.map((val, idx) => {
                                if (idx >= 2 && val !== null) return 4; // Weeks 3-6
                                if (metric === 'Chlorophyll' && idx <= 1 && val !== null) return 4; // Chlorophyll weeks 1-2
                                return 0;
                            }),
                            pointHoverRadius: data.manual.map((val, idx) => {
                                if (idx >= 2 && val !== null) return 6;
                                if (metric === 'Chlorophyll' && idx <= 1 && val !== null) return 6;
                                return 0;
                            }),
                            tension: 0.3,
                            spanGaps: false
                        },
                        {
                            label: 'Chamber Spray (Predicted)',
                            data: data.chamber.map((val, idx) => {
                                // For ALL metrics: show predictions from week 3 onwards
                                if (idx >= 2) { // Week 3, 4, 5, 6
                                    return val;
                                }
                                // For chlorophyll: show estimated week 1 AND connect to week 2
                                if (metric === 'Chlorophyll' && idx <= 1) { // Week 1 and 2
                                    return val;
                                }
                                // For other metrics: only show from week 3
                                return null;
                            }),
                            borderColor: config.colorChamber,
                            backgroundColor: config.colorChamber,
                            borderWidth: 2,
                            borderDash: [5, 5], // Broken/dashed line for ALL prediction data
                            pointRadius: data.chamber.map((val, idx) => {
                                if (idx >= 2 && val !== null) return 4; // Weeks 3-6
                                if (metric === 'Chlorophyll' && idx <= 1 && val !== null) return 4; // Chlorophyll weeks 1-2
                                return 0;
                            }),
                            pointHoverRadius: data.chamber.map((val, idx) => {
                                if (idx >= 2 && val !== null) return 6;
                                if (metric === 'Chlorophyll' && idx <= 1 && val !== null) return 6;
                                return 0;
                            }),
                            tension: 0.3,
                            spanGaps: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            cornerRadius: 6,
                            filter: function(tooltipItem) {
                                return tooltipItem.raw !== null;
                            },
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        const weekIndex = context.dataIndex;
                                        const isPredicted = weekIndex >= 3 || 
                                            (metric === 'Chlorophyll' && weekIndex <= 1) ||
                                            context.dataset.label.includes('Predicted');
                                        
                                        label += context.parsed.y.toFixed(metric === 'Chlorophyll' ? 3 : 1);
                                        if (isPredicted) {
                                            label += ' (predicted)';
                                        }
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: 'rgba(255, 255, 255, 0.1)' },
                            ticks: { color: '#ccc' },
                            title: {
                                display: true,
                                text: 'Week',
                                color: '#c9af04',
                                font: { weight: 'bold' }
                            }
                        },
                        y: {
                            grid: { color: 'rgba(255, 255, 255, 0.1)' },
                            ticks: { color: '#ccc' },
                            title: {
                                display: true,
                                text: config.yLabel,
                                color: '#c9af04',
                                font: { weight: 'bold' }
                            },
                            beginAtZero: true
                        }
                    },
                    interaction: { intersect: false, mode: 'index' },
                    animation: { duration: 1000, easing: 'easeOutQuart' }
                }
            });

            createLegend(plantId, metric, config);
        }

        function createLegend(plantId, metric, config) {
            const legendId = plantId + '-' + metric.toLowerCase() + 'Legend';
            const legendContainer = document.getElementById(legendId);
            
            if (!legendContainer) {
                console.error('Legend container not found for:', legendId);
                return;
            }
            
            const legends = [
                { color: config.colorManual, label: 'Manual Spray - Actual' },
                { color: config.colorChamber, label: 'Chamber Spray - Actual' },
                { color: config.colorManual, label: 'Manual Spray - Predicted', dashed: true },
                { color: config.colorChamber, label: 'Chamber Spray - Predicted', dashed: true }
            ];

            let html = '';
            legends.forEach(legend => {
                html += `
                    <div class="legend-item">
                        <div class="legend-color" style="
                            background-color: ${legend.color}; 
                            ${legend.dashed ? 'background: repeating-linear-gradient(45deg, transparent, transparent 2px, ' + legend.color + ' 2px, ' + legend.color + ' 4px)' : ''}
                        "></div>
                        <span>${legend.label}</span>
                    </div>
                `;
            });

            legendContainer.innerHTML = html;
        }

        function displayPredictionsTable(plantId, predictions) {
            const container = document.getElementById(plantId + '-predictionsTable');
            
            if (!predictions || !predictions.predictions) {
                container.innerHTML = '<div class="error">No prediction data available</div>';
                return;
            }
            
            let html = `
                <table>
                    <thead>
                        <tr>
                            <th>Week</th>
                            <th>Spray Type</th>
                            <th>Leaf Count</th>
                            <th>Survivability (%)</th>
                            <th>Chlorophyll</th>
                            <th>Data Type</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            for (let week = 1; week <= 6; week++) {
                for (let sprayType of ['Manual', 'Chamber']) {
                    const leafCountData = predictions.predictions.LeafCount.find(
                        item => item.week === week && item.spray_type === sprayType
                    );
                    const survivabilityData = predictions.predictions.Survivability.find(
                        item => item.week === week && item.spray_type === sprayType
                    );
                    const chlorophyllData = predictions.predictions.Chlorophyll.find(
                        item => item.week === week && item.spray_type === sprayType
                    );
                    
                    // Determine data types and styling
                    let rowClass = 'actual';
                    let overallDataType = 'Actual Data';
                    let dataTypeColor = '#c9af04'; // OHN gold
                    let tooltip = '';
                    
                    if (week >= 4) {
                        rowClass = 'predicted';
                        overallDataType = 'Predicted';
                        dataTypeColor = '#e67e22';
                        tooltip = 'Future prediction based on linear regression';
                    } else if (week === 1) {
                        rowClass = 'predicted';
                        overallDataType = 'Mixed Data';
                        dataTypeColor = '#e67e22';
                        tooltip = 'Leaf Count & Survivability: Actual data<br>Chlorophyll: Estimated from Week 2-3 trend';
                    }
                    
                    html += `
                        <tr class="${rowClass}">
                            <td><strong>Week ${week}</strong></td>
                            <td>
                                <span class="spray-badge spray-${sprayType.toLowerCase()}">
                                    ${sprayType}
                                </span>
                            </td>
                            <td>
                                <div style="position: relative;">
                                    ${leafCountData && leafCountData.value !== null ? leafCountData.value.toFixed(1) : '-'}
                                    ${week <= 3 ? '' : '<div style="font-size: 0.7em; color: #999;">predicted</div>'}
                                </div>
                            </td>
                            <td>
                                <div style="position: relative;">
                                    ${survivabilityData && survivabilityData.value !== null ? survivabilityData.value.toFixed(1) : '-'}
                                    ${week <= 3 ? '' : '<div style="font-size: 0.7em; color: #999;">predicted</div>'}
                                </div>
                            </td>
                            <td>
                                <div style="position: relative;">
                                    ${chlorophyllData && chlorophyllData.value !== null ? chlorophyllData.value.toFixed(3) : '-'}
                                    ${week === 1 ? 
                                        '<div style="font-size: 0.7em; color: #e67e22; font-weight: bold;">estimated</div>' : 
                                        week <= 3 ? '' : '<div style="font-size: 0.7em; color: #999;">predicted</div>'
                                    }
                                </div>
                            </td>
                            <td>
                                <span style="font-weight: 600; color: ${dataTypeColor}" 
                                    title="${tooltip}">
                                    ${overallDataType}
                                    ${tooltip ? ' ⓘ' : ''}
                                </span>
                            </td>
                        </tr>
                    `;
                }
            }

            html += `
                    </tbody>
                </table>
                <div style="margin-top: 15px; padding: 10px; background: #2a2a2a; border-radius: 5px; font-size: 0.9em; color: #ccc;">
                    <strong>Data Legend:</strong>
                    <span style="color: #c9af04;">● Actual</span> | 
                    <span style="color: #e67e22;">● Predicted</span> | 
                    <span style="color: #e67e22;">● Estimated Chlorophyll</span> (calculated from Week 2-3 trend)
                </div>
            `;

            container.innerHTML = html;
        }
    </script>
</body>
</html>