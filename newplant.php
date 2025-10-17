<?php
// Connect to database
$conn = mysqli_connect("127.0.0.1", "root", "", "permdb");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle form submission
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_id'])) {
        // Handle delete
        $delete_id = intval($_POST['delete_id']);
        $delete_sql = "DELETE FROM plant_analysis WHERE id = $delete_id";
        if (mysqli_query($conn, $delete_sql)) {
            $msg = "Entry deleted successfully!";
        } else {
            $msg = "Error deleting entry: " . mysqli_error($conn);
        }
    } else {
        // Handle new entry
        $crop = $_POST['crop'];
        $week = intval($_POST['week']);
        $chlorophyll_manual = floatval($_POST['chlorophyll_manual']);
        $chlorophyll_chamber = floatval($_POST['chlorophyll_chamber']);
        $leafcount_manual = floatval($_POST['leafcount_manual']);
        $leafcount_chamber = floatval($_POST['leafcount_chamber']);
        $survivability_manual = floatval($_POST['survivability_manual']);
        $survivability_chamber = floatval($_POST['survivability_chamber']);

        // Check if entry already exists for this crop and week
        $check_sql = "SELECT id FROM plant_analysis WHERE crop = '$crop' AND week = $week";
        $check_result = mysqli_query($conn, $check_sql);
        
        if (mysqli_num_rows($check_result) > 0) {
            $msg = "Error: An entry already exists for $crop Week $week. Please delete the existing entry first.";
        } else {
            $sql = "INSERT INTO plant_analysis 
                (crop, week, chlorophyll_manual, chlorophyll_chamber, leafcount_manual, leafcount_chamber, survivability_manual, survivability_chamber)
                VALUES 
                ('$crop', $week, $chlorophyll_manual, $chlorophyll_chamber, $leafcount_manual, $leafcount_chamber, $survivability_manual, $survivability_chamber)";
            if (mysqli_query($conn, $sql)) {
                $msg = "Entry saved!";
            } else {
                $msg = "Error: " . mysqli_error($conn);
            }
        }
    }
}

// Fetch existing entries
$existing_entries_sql = "SELECT * FROM plant_analysis ORDER BY crop, week";
$existing_entries_result = mysqli_query($conn, $existing_entries_sql);
$existing_entries = [];
while ($row = mysqli_fetch_assoc($existing_entries_result)) {
    $existing_entries[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Plant Analysis Entry</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #181818;
            color: #fff;
            font-family: Arial, sans-serif;
            margin: 0;
        }
        .header {
            background: linear-gradient(to right, #0d0d0ddb, #c9af04 90%);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            height: 70px;
        }
        .header-title {
            font-size: 26px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .header-date {
            font-size: 18px;
        }

        .container {
            display: grid;
            grid-template-columns: 40% 60%;
            align-items: start;
            gap: 50px;
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }

        form {
            display: grid;
            gap: 20px;
        }
        .form-group {
            display: grid;
            gap: 2px

        }

        .form-container {
            flex: 1;
            background: #232323;
            padding: 20px;
            border-radius: 16px;
        }

        .form-group label {
            display: block;
            font-size: large;

            font-weight: bold;
            color: #f1c40f;
        }

        .form-group input, .form-group select {

            color: #fff;
            padding: 8px;
            font-size: 15px;
            border-radius: 8px;
            background: #181818;
        }

        .entries-container {
            padding: 20px 30px;

            background: #232323;
            border-radius: 12px;
            box-shadow: 0 2px 10px #0006;
            flex: 1;
        }

        .btn-submit {
            background: #2ecc40;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 10px 24px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
            width: 100%;
        }
        .btn-submit:hover {
            background: #27ae38;
        }
        .btn-delete {
            background: #e74c3c;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 5px 12px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-delete:hover {
            background: #c0392b;
        }
        .msg {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
        }
        .msg.success {
            background: #2ecc40;
            color: #fff;
        }
        .msg.error {
            background: #e74c3c;
            color: #fff;
        }
        .entries-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .entries-table th {
            background: #c9af04;
            color: #181818;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        .entries-table td {
            padding: 10px;
            border-bottom: 1px solid #444;
        }
        .entries-table tr:nth-child(even) {
            background: #2a2a2a;
        }
        .crop-section {
            margin-bottom: 25px;
        }
        .crop-title {
            color: #c9af04;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #c9af04;
        }
        .no-entries {
            text-align: center;
            color: #999;
            font-style: italic;
            padding: 20px;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                padding: 15px;
            }
            .form-container, .entries-container {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div style="display: flex; align-items: center;">
            <a href="analysis.php" style="color:#fff; text-decoration:none; margin-right:18px;">
                <i class="fas fa-arrow-left" style="font-size:22px;"></i>
            </a>
            <span class="header-title">NEW PLANT ANALYSIS ENTRY</span>
        </div>
        <span class="header-date"><?php echo date("F d, Y"); ?></span>
    </div>

    <div class="container">
        <div class="form-container">
            <h2><i class="fas fa-leaf"></i> Add Analysis Data</h2>
            <?php if ($msg): ?>
                <div class="msg <?php echo strpos($msg, 'Error') === false ? 'success' : 'error'; ?>"><?php echo $msg; ?></div>
            <?php endif; ?>
            <form method="post" autocomplete="off">
                <div class="form-group">
                    <label for="crop">Crop</label>
                    <select name="crop" id="crop" required>
                        <option value="">Select Crop</option>
                        <option value="Lettuce">Lettuce</option>
                        <option value="Mustasa">Mustasa</option>
                        <option value="Pechay">Pechay</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="week">Week</label>
                    <input type="number" name="week" id="week" min="1" max="52" required>
                </div>
                <div class="form-group">
                    <label for="chlorophyll_manual">Chlorophyll Index (Manual OHN)</label>
                    <input type="number" step="0.01" name="chlorophyll_manual" id="chlorophyll_manual" required>
                </div>
                <div class="form-group">
                    <label for="chlorophyll_chamber">Chlorophyll Index (Chamber OHN)</label>
                    <input type="number" step="0.01" name="chlorophyll_chamber" id="chlorophyll_chamber" required>
                </div>
                <div class="form-group">
                    <label for="leafcount_manual">Leaf Count (Manual OHN)</label>
                    <input type="number" step="0.01" name="leafcount_manual" id="leafcount_manual" required>
                </div>
                <div class="form-group">
                    <label for="leafcount_chamber">Leaf Count (Chamber OHN)</label>
                    <input type="number" step="0.01" name="leafcount_chamber" id="leafcount_chamber" required>
                </div>
                <div class="form-group">
                    <label for="survivability_manual">Survivability Rate (Manual OHN)</label>
                    <input type="number" step="0.01" name="survivability_manual" id="survivability_manual" required>
                </div>
                <div class="form-group">
                    <label for="survivability_chamber">Survivability Rate (Chamber OHN)</label>
                    <input type="number" step="0.01" name="survivability_chamber" id="survivability_chamber" required>
                </div>
                <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Save Entry</button>
            </form>
        </div>

        <div class="entries-container">
            <h2><i class="fas fa-list"></i> Existing Entries</h2>
            <?php if (empty($existing_entries)): ?>
                <div class="no-entries">No entries found. Add your first entry using the form.</div>
            <?php else: ?>
                <?php
                // Group entries by crop
                $grouped_entries = [];
                foreach ($existing_entries as $entry) {
                    $grouped_entries[$entry['crop']][] = $entry;
                }
                
                foreach ($grouped_entries as $crop => $entries): 
                ?>
                    <div class="crop-section">
                        <div class="crop-title"><?php echo $crop; ?></div>
                        <table class="entries-table">
                            <thead>
                                <tr>
                                    <th>Week</th>
                                    <th>Chlorophyll (M)</th>
                                    <th>Chlorophyll (C)</th>
                                    <th>Leaf Count (M)</th>
                                    <th>Leaf Count (C)</th>
                                    <th>Survivability (M)</th>
                                    <th>Survivability (C)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($entries as $entry): ?>
                                <tr>
                                    <td>Week <?php echo $entry['week']; ?></td>
                                    <td><?php echo $entry['chlorophyll_manual']; ?></td>
                                    <td><?php echo $entry['chlorophyll_chamber']; ?></td>
                                    <td><?php echo $entry['leafcount_manual']; ?></td>
                                    <td><?php echo $entry['leafcount_chamber']; ?></td>
                                    <td><?php echo $entry['survivability_manual']; ?></td>
                                    <td><?php echo $entry['survivability_chamber']; ?></td>
                                    <td>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="delete_id" value="<?php echo $entry['id']; ?>">
                                            <button type="submit" class="btn-delete" onclick="return confirm('Are you sure you want to delete this entry?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>