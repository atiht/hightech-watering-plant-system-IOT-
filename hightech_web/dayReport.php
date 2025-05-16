<?php
include('./components/loginChecker.php');
include('./backend/classes/Database.class.php');
include('./backend/classes/Program.class.php');
$Program = new Program();

function FormatWateringTime($wateringSec)
{
	// แปลงเวลาทั้งหมดจากมิลลิวินาทีเป็นวินาที
	$totalSeconds = $wateringSec / 1000;

	// แปลงเวลาทั้งหมดเป็นหน่วยนาทีและวินาที
	$minutes = floor($totalSeconds / 60);
	$seconds = $totalSeconds % 60;

	// สร้างสตริงแสดงผลลัพธ์ตามที่ต้องการ
	if ($minutes > 0) {
		if ($seconds > 0) {
			// มีทั้งนาทีและวินาที
			return sprintf("%d นาที %d วินาที", $minutes, $seconds);
		} else {
			// มีแต่นาทีเท่านั้น
			return sprintf("%d นาที", $minutes);
		}
	} else {
		// มีแค่วินาทีเท่านั้น
		return sprintf("%d วินาที", $seconds);
	}
}

// watering info
$watering = $Program->selectAll(
    'watering',
    'WateringSec, DATE(WateringDateTime) WateringDate, TIME(WateringDateTime) WateringTime',
    'DATE(WateringDateTime) = :Watering_Date_Time',
    '',
    '',
    [
        ':Watering_Date_Time' => date("Y-m-d")
    ]
);
if ($watering['status'] == 'error') {
    echo "<pre>";
    print_r($watering);
    exit;
}

// select count watering
$CountWatering = $Program->selectOne(
    'watering',
    'COUNT(WateringID) CountWatering',
    'DATE(WateringDateTime) = :Watering_Date_Time',
    '',
    '',
    [
        ":Watering_Date_Time" => date("Y-m-d")
    ]
);
if ($CountWatering['status'] == 'error') {
    echo "<pre>";
    print_r($CountWatering);
    exit;
}
$CountWatering = isset($CountWatering['data']['CountWatering']) ? "{$CountWatering['data']['CountWatering']} ครั้ง" : "0 ครั้ง";

// pump 4 L. / 1.min
$CountWater = $Program->selectOne(
    'watering',
    'SUM(WateringSec) WateringSec',
    'DATE(WateringDateTime) = :Watering_Date_Time',
    '',
    '',
    [
        ":Watering_Date_Time" => date("Y-m-d")
    ]
);
if ($CountWater['status'] == 'error') {
    echo "<pre>";
    print_r($CountWater);
    exit;
}

if (isset($CountWater['data']['WateringSec'])) {
    $CountWaterLiter = ($CountWater['data']['WateringSec'] * 4) / 60000;
    $CountWaterLiter = number_format($CountWaterLiter, 2) . " ลิตร";
    $CountWateringTime = FormatWateringTime($CountWater['data']['WateringSec']);
} else {
    $CountWaterLiter = "0.0 ลิตร";
    $CountWateringTime = "<test class='text-danger'>ไม่มีการรดน้ำ</test>";
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include("./structures/head.php") ?>
    <link href="https://dsil.kmutt.ac.th/service/assets/plugins/apexcharts-bundle/css/apexcharts.css" rel="stylesheet" />
    <title>รายงานประจำวัน</title>
</head>

<body class="bg-body-tertiary">
    <?php include("./components/header.php") ?>
    <div class="container mt-5 mb-5">

        <div class="card border-top border-0 border-4 border-success shadow" data-aos="fade-up" data-aos-duration="1500">
            <div class="card-body p-4">
                <div class="card-title d-flex align-items-center">
                    <h3 class="mb-0 text-success fw-bold">รายงานประจำวัน</h3>
                </div>
                <hr>
                <div class="row row-cols-1 row-cols-md-3 g-3">
                    <div class="col">
                        <div class="card radius-15 mb-0 shadow-sm border">
                            <div class="card-body text-center">
                                <div class="widgets-icons mx-auto rounded-circle bg-success text-white">
                                    <i class="bi bi-droplet-half"></i>
                                </div>
                                <h4 class="mb-0 font-weight-bold mt-3"><?php echo $CountWatering ?></h4>
                                <p class="mb-0">จำนวนการรดน้ำ</p>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-15 mb-0 shadow-sm border">
                            <div class="card-body text-center">
                                <div class="widgets-icons mx-auto bg-success text-white rounded-circle">
                                    <i class="bi bi-moisture"></i>
                                </div>
                                <h4 class="mb-0 font-weight-bold mt-3"><?php echo $CountWaterLiter ?></h4>
                                <p class="mb-0">ปริมาณน้ำทั้งหมด</p>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-15 mb-0 shadow-sm border">
                            <div class="card-body text-center">
                                <div class="widgets-icons mx-auto bg-success rounded-circle text-white">
                                    <i class="bi bi-alarm-fill"></i>
                                </div>
                                <h4 class="mb-0 font-weight-bold mt-3"><?php echo $CountWateringTime ?></h4>
                                <p class="mb-0">เวลาที่รดน้ำทั้งหมด</p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (isset($watering['data']) && $watering['data']) : $n = 0; ?>
                    <br>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="thead-dark">
                                <tr>
                                    <th class="text-dark fw-bold text-center">#</th>
                                    <th class="text-dark fw-bold text-center">วันที่</th>
                                    <th class="text-dark fw-bold text-center">เวลา</th>
                                    <th class="text-dark fw-bold text-center">ปริมาณน้ำ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($watering['data'] as $w) : ++$n; ?>
                                    <tr>
                                        <td class="text-center"><?php echo $n ?></td>
                                        <td class="text-center"><?php echo $Program->DateThai($w['WateringDate'], "Y-m-d") ?></td>
                                        <td class="text-center"><?php echo date("H:i", strtotime($w['WateringTime'])) ?></td>
                                        <td class="text-center"><?php echo number_format(((($w['WateringSec'] * 4) / 60000) * 1000), 2) . " มล."  ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif ?>
            </div>
        </div>
    </div>
    <?php include('./components/footer.php') ?>
    <?php include("./structures/script.php") ?>
    <script src="https://dsil.kmutt.ac.th/service/assets/plugins/apexcharts-bundle/js/apexcharts.min.js"></script>
    <script>
        // TotalNumberWateringChart
        var options = {
            series: [{
                name: 'series1',
                data: [31, 40, 28, 51, 42, 109, 100]
            }, {
                name: 'series2',
                data: [11, 32, 45, 32, 34, 52, 41]
            }],
            chart: {
                foreColor: '#9ba7b2',
                height: 400,
                type: 'area',
                zoom: {
                    enabled: false
                },
                toolbar: {
                    show: true
                },
            },
            colors: ["#673ab7", '#198fed'],
            title: {
                text: 'Area Chart',
                align: 'left',
                style: {
                    fontSize: "16px",
                    color: '#666'
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth'
            },
            xaxis: {
                type: 'datetime',
                categories: ["2018-09-19T00:00:00.000Z", "2018-09-19T01:30:00.000Z", "2018-09-19T02:30:00.000Z", "2018-09-19T03:30:00.000Z", "2018-09-19T04:30:00.000Z", "2018-09-19T05:30:00.000Z", "2018-09-19T06:30:00.000Z"]
            },
            tooltip: {
                x: {
                    format: 'dd/MM/yy HH:mm'
                },
            },
        };
        var chart = new ApexCharts(document.querySelector("#TotalNumberWateringChart"), options);
        chart.render();
        // TotalWateringChart
        var options = {
            series: [{
                name: 'series1',
                data: [31, 40, 28, 51, 42, 109, 100]
            }, {
                name: 'series2',
                data: [11, 32, 45, 32, 34, 52, 41]
            }],
            chart: {
                foreColor: '#9ba7b2',
                height: 400,
                type: 'area',
                zoom: {
                    enabled: false
                },
                toolbar: {
                    show: true
                },
            },
            colors: ["#673ab7", '#198fed'],
            title: {
                text: 'Area Chart',
                align: 'left',
                style: {
                    fontSize: "16px",
                    color: '#666'
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth'
            },
            xaxis: {
                type: 'datetime',
                categories: ["2018-09-19T00:00:00.000Z", "2018-09-19T01:30:00.000Z", "2018-09-19T02:30:00.000Z", "2018-09-19T03:30:00.000Z", "2018-09-19T04:30:00.000Z", "2018-09-19T05:30:00.000Z", "2018-09-19T06:30:00.000Z"]
            },
            tooltip: {
                x: {
                    format: 'dd/MM/yy HH:mm'
                },
            },
        };
        var chart = new ApexCharts(document.querySelector("#TotalWateringChart"), options);
        chart.render();
    </script>
</body>

</html>