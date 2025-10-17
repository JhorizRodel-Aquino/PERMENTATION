<?php
class PlantGrowthPredictor {
    private $data;
    
    public function __construct() {
        $this->data = [
            'Week' => [1, 2, 3, 1, 2, 3],
            'Spray' => ['Manual', 'Manual', 'Manual', 'Chamber', 'Chamber', 'Chamber'],
            'LeafCount' => [0.769, 1.283, 2.769, 2.063, 3.142, 5.240],
            'Survivability' => [48.56, 63.46, 83.69, 76.44, 94, 98.32],
            'Chlorophyll' => [null, 0.378, 0.337, null, 0.383, 0.300] // No data for week 1
        ];
    }
    
    // Linear regression that handles available data points
    private function linearRegression($x, $y) {
        // Filter out null values but keep the data points
        $valid_data = [];
        for ($i = 0; $i < count($x); $i++) {
            if ($y[$i] !== null) {
                $valid_data[] = [
                    'x' => $x[$i],
                    'y' => $y[$i]
                ];
            }
        }
        
        // We need at least 2 data points for regression
        if (count($valid_data) < 2) {
            return ['slope' => 0, 'intercept' => 0, 'valid' => false, 'data_points' => count($valid_data)];
        }
        
        $n = count($valid_data);
        $sum_x = 0;
        $sum_y = 0;
        $sum_xy = 0;
        $sum_xx = 0;
        
        foreach ($valid_data as $point) {
            $sum_x += $point['x'];
            $sum_y += $point['y'];
            $sum_xy += $point['x'] * $point['y'];
            $sum_xx += $point['x'] * $point['x'];
        }
        
        $slope = ($n * $sum_xy - $sum_x * $sum_y) / ($n * $sum_xx - $sum_x * $sum_x);
        $intercept = ($sum_y - $slope * $sum_x) / $n;
        
        return [
            'slope' => $slope, 
            'intercept' => $intercept, 
            'valid' => true, 
            'data_points' => count($valid_data)
        ];
    }
    
    public function predictFutureValues() {
        $predictions = [
            'actual_data' => $this->data,
            'predictions' => [],
            'models_info' => []
        ];
        
        $metrics = ['LeafCount', 'Survivability', 'Chlorophyll'];
        
        foreach ($metrics as $metric) {
            // Separate data by spray type
            $manual_data = [];
            $chamber_data = [];
            
            for ($i = 0; $i < count($this->data['Week']); $i++) {
                if ($this->data['Spray'][$i] === 'Manual') {
                    $manual_data[] = [
                        'week' => $this->data['Week'][$i],
                        'value' => $this->data[$metric][$i]
                    ];
                } else {
                    $chamber_data[] = [
                        'week' => $this->data['Week'][$i],
                        'value' => $this->data[$metric][$i]
                    ];
                }
            }
            
            // Perform regression for each spray type using available data
            $manual_weeks = array_column($manual_data, 'week');
            $manual_values = array_column($manual_data, 'value');
            $manual_model = $this->linearRegression($manual_weeks, $manual_values);
            
            $chamber_weeks = array_column($chamber_data, 'week');
            $chamber_values = array_column($chamber_data, 'value');
            $chamber_model = $this->linearRegression($chamber_weeks, $chamber_values);
            
            // Calculate Week 3 comparison for advantage analysis
            $week3_comparison = null;
            if ($manual_model['valid'] && $chamber_model['valid']) {
                $manual_week3 = $manual_model['slope'] * 3 + $manual_model['intercept'];
                $chamber_week3 = $chamber_model['slope'] * 3 + $chamber_model['intercept'];
                
                if ($metric === 'Survivability' || $metric === 'Chlorophyll' || $metric === 'LeafCount') {
                    if ($chamber_week3 > $manual_week3) {
                        $advantage = (($chamber_week3 - $manual_week3) / $manual_week3) * 100;
                        $advantage_text = "Chamber is " . round($advantage, 1) . "% better";
                    } else {
                        $advantage = (($manual_week3 - $chamber_week3) / $chamber_week3) * 100;
                        $advantage_text = "Manual is " . round($advantage, 1) . "% better";
                    }
                }
                
                $week3_comparison = [
                    'manual_week3' => $manual_week3,
                    'chamber_week3' => $chamber_week3,
                    'advantage_text' => $advantage_text
                ];
            }
            
            // Store model info
            $predictions['models_info'][$metric] = [
                'manual' => $manual_model,
                'chamber' => $chamber_model,
                'week3_comparison' => $week3_comparison
            ];
            
            // Generate predictions for weeks 1-6
            $metric_predictions = [];
            for ($week = 1; $week <= 6; $week++) {
                // For weeks 1-3: Use actual data where available
                // For weeks 4-6: Use predictions
                // Special case: For Chlorophyll week 1, use prediction since we have no data
                
                $is_future_week = $week >= 4;
                $is_chlorophyll_missing_week1 = ($metric === 'Chlorophyll' && $week === 1);
                
                $use_prediction = $is_future_week || $is_chlorophyll_missing_week1;
                
                // Manual spray
                $manual_actual_value = null;
                $manual_pred_value = null;
                
                // Find actual value if it exists
                foreach ($manual_data as $data_point) {
                    if ($data_point['week'] === $week && $data_point['value'] !== null) {
                        $manual_actual_value = $data_point['value'];
                    }
                }
                
                // Calculate prediction if needed
                if ($use_prediction && $manual_model['valid']) {
                    $manual_pred_value = $manual_model['slope'] * $week + $manual_model['intercept'];
                    
                    // Apply constraints
                    if ($metric === 'Survivability') {
                        if ($manual_pred_value > 100) $manual_pred_value = 100;
                        if ($manual_pred_value < 0) $manual_pred_value = 0;
                    }
                    if ($metric === 'Chlorophyll' && $manual_pred_value < 0) {
                        $manual_pred_value = 0;
                    }
                    
                    $manual_pred_value = round($manual_pred_value, 3);
                }
                
                // Chamber spray
                $chamber_actual_value = null;
                $chamber_pred_value = null;
                
                // Find actual value if it exists
                foreach ($chamber_data as $data_point) {
                    if ($data_point['week'] === $week && $data_point['value'] !== null) {
                        $chamber_actual_value = $data_point['value'];
                    }
                }
                
                // Calculate prediction if needed
                if ($use_prediction && $chamber_model['valid']) {
                    $chamber_pred_value = $chamber_model['slope'] * $week + $chamber_model['intercept'];
                    
                    // Apply constraints
                    if ($metric === 'Survivability') {
                        if ($chamber_pred_value > 100) $chamber_pred_value = 100;
                        if ($chamber_pred_value < 0) $chamber_pred_value = 0;
                    }
                    if ($metric === 'Chlorophyll' && $chamber_pred_value < 0) {
                        $chamber_pred_value = 0;
                    }
                    
                    $chamber_pred_value = round($chamber_pred_value, 3);
                }
                
                // Manual data point
                $metric_predictions[] = [
                    'week' => $week,
                    'spray_type' => 'Manual',
                    'value' => $use_prediction ? $manual_pred_value : $manual_actual_value,
                    'is_actual' => !$use_prediction && $manual_actual_value !== null,
                    'is_prediction' => $use_prediction,
                    'data_type' => $use_prediction ? 'predicted' : ($manual_actual_value !== null ? 'actual' : 'missing')
                ];
                
                // Chamber data point
                $metric_predictions[] = [
                    'week' => $week,
                    'spray_type' => 'Chamber',
                    'value' => $use_prediction ? $chamber_pred_value : $chamber_actual_value,
                    'is_actual' => !$use_prediction && $chamber_actual_value !== null,
                    'is_prediction' => $use_prediction,
                    'data_type' => $use_prediction ? 'predicted' : ($chamber_actual_value !== null ? 'actual' : 'missing')
                ];
            }
            
            $predictions['predictions'][$metric] = $metric_predictions;
        }
        
        return $predictions;
    }
    
    public function formatForCharts($predictions) {
        $formatted = [];
        
        foreach (['LeafCount', 'Survivability', 'Chlorophyll'] as $metric) {
            $formatted[$metric] = [
                'weeks' => range(1, 6),
                'manual' => [],
                'chamber' => [],
                'manual_actual' => [],
                'chamber_actual' => []
            ];
            
            // Initialize arrays with null values
            for ($i = 0; $i < 6; $i++) {
                $formatted[$metric]['manual'][$i] = null;
                $formatted[$metric]['chamber'][$i] = null;
                $formatted[$metric]['manual_actual'][$i] = null;
                $formatted[$metric]['chamber_actual'][$i] = null;
            }
            
            // Fill with data
            foreach ($predictions['predictions'][$metric] as $point) {
                $index = $point['week'] - 1;
                
                if ($point['spray_type'] === 'Manual') {
                    if ($point['is_actual']) {
                        // Actual data
                        $formatted[$metric]['manual_actual'][$index] = $point['value'];
                        $formatted[$metric]['manual'][$index] = $point['value'];
                    } else if ($point['is_prediction']) {
                        // Prediction
                        $formatted[$metric]['manual'][$index] = $point['value'];
                    }
                } else {
                    if ($point['is_actual']) {
                        // Actual data
                        $formatted[$metric]['chamber_actual'][$index] = $point['value'];
                        $formatted[$metric]['chamber'][$index] = $point['value'];
                    } else if ($point['is_prediction']) {
                        // Prediction
                        $formatted[$metric]['chamber'][$index] = $point['value'];
                    }
                }
            }
        }
        
        return $formatted;
    }
}

// Handle the request
$predictor = new PlantGrowthPredictor();
$predictions = $predictor->predictFutureValues();
$chartData = $predictor->formatForCharts($predictions);

// Output as JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
echo json_encode([
    'success' => true,
    'predictions' => $predictions,
    'chart_data' => $chartData
]);
?>