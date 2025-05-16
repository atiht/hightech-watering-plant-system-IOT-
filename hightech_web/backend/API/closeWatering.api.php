<?php
date_default_timezone_set("Asia/Bangkok");
header("Content-type: application/json; charset=utf-8");
if (!isset($_POST['command'])) {
    echo json_encode([
        'status' => 'error',
        'errorType' => 'not found path'
    ]);
    exit;
}
if ($_POST['command'] == 'watered') {
    // read json file
    $WateringJsonData = file_get_contents("watering.json");
    $data = json_decode($WateringJsonData, true);
    // ตรวจสอบว่าการแปลง JSON สำเร็จหรือไม่
    if (json_last_error() === JSON_ERROR_NONE) {
        $data['sec'] = 0;
        $NewJsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (file_put_contents("watering.json", $NewJsonData)) {
            if (isset($_POST['sec'])) {
                include('../classes/Database.class.php');
                include('../classes/Program.class.php');
                $Program = new Program();
                $InsertWatering = $Program->insert(
                    'watered',
                    [
                        'WateringSec' => $Program->XssProtection($_POST['sec']),
                        'WateringDateTime' => date("Y-m-d H:i:s")
                    ]
                );
                if($InsertWatering['status'] == 'error') {
                    echo json_encode([$InsertWatering]);
                    exit;
                }else {
                    echo json_encode([
                        'status' => 'success',
                        'msg' => 'have sec'
                    ]);
                    exit;
                }
            }else {
                echo json_encode([
                    'status' => 'success',
                    'msg' => 'no sec'
                ]);
                exit;
            }
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
