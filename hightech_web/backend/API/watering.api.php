<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set("Asia/Bangkok");
header("Content-type: application/json; charset=utf-8");
if (isset($_POST['path']) || isset($_GET['path'])) {
    include("../classes/Database.class.php");
    include("../classes/Program.class.php");
    $Program = new Program();
} else {
    echo json_encode([
        'status' => 'error',
        'errorType' => 'not found path'
    ]);
    exit;
}

if ($_POST['path'] == '/watering') {
    $sec = isset($_POST['sec']) && !empty(trim($_POST['sec'])) && is_numeric($_POST['sec']) ? $Program->XssProtection($_POST['sec']) : null;
    if (is_null($sec)) {
        echo json_encode([
            'status' => 'error',
            'errorType' => 'sec is not correct'
        ]);
        exit;
    }
    $InsertWatering = $Program->insert(
        'watering',
        [
            'WateringSec' => $sec,
            'WateringDateTime' => date("Y-m-d H:i:s")
        ]
    );
    if ($InsertWatering['status'] == 'error') {
        echo json_encode($InsertWatering);
        exit;
    }
    // read json file
    $WateringJsonData = file_get_contents("watering.json");
    $data = json_decode($WateringJsonData, true);
    // ตรวจสอบว่าการแปลง JSON สำเร็จหรือไม่
    if (json_last_error() === JSON_ERROR_NONE) {
        $data['sec'] = $sec;
        $NewJsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (file_put_contents("watering.json", $NewJsonData)) {
            echo json_encode([
                'status' => 'success',
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'errorType' => 'JSON_ERROR'
        ]);
        exit;
    }
}


