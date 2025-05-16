<?php
    date_default_timezone_set("Asia/Bangkok");
    header("Content-type: application/json; charset=utf-8");
    if(isset($_POST['humidity']) && isset($_POST['temperature'])) {
        if($_POST['humidity'] != 'nan' && $_POST['temperature'] != 'nan') {
            include("../classes/Database.class.php");
            include("../classes/Program.class.php");
            $Program = new Program();
            $insert = $Program->insert(
                'farminfo',
                [
                    'Humidity' => $Program->XssProtection($_POST['humidity']),
                    'Temperature' => $Program->XssProtection($_POST['temperature']),
                    'InfoDateTime' => date("Y-m-d H:i:s")
                ]
            );
            if($insert['status'] == 'error') {
                echo json_encode($insert);
                exit;
            }
            echo json_encode([
                'status' => 'success'
            ]);
        }else {
            echo json_encode([
                'status' => 'error',
                'errotType' => 'value is wrong'
            ]);
        }
    }else {
        echo json_encode([
            'status' => 'error',
            'errotType' => 'not found humidity or temperature'
        ]);
    }
?>