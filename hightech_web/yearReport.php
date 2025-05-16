<?php
include('./components/loginChecker.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// select watering month
// SELECT DISTINCT MONTH(WateringDateTime) month, YEAR(WateringDateTime) year FROM `watering`;
// $WateringMonth = $Program->selectAll(
// 	'watering',
// 	'DISTINCT MONTH(WateringDateTime) month, YEAR(WateringDateTime) year'
// );
// if ($WateringMonth['status'] == 'error') {
// 	echo "<pre>";
// 	echo json_encode($WateringMonth);
// 	exit;
// }

// select count watering
$CountWatering = $Program->selectOne(
	'watering',
	'COUNT(WateringID) CountWatering',
	'YEAR(WateringDateTime) = :CurrentYear',
	'',
	'',
	[
		':CurrentYear' => date("Y")
	]
);
if ($CountWatering['status'] == 'error') {
	echo "<pre>";
	print_r($CountWatering);
	exit;
}
$CountWatering = isset($CountWatering['data']['CountWatering']) ? $CountWatering['data']['CountWatering'] : 0;


// pump 4 L. / 1.min
$CountWater = $Program->selectOne(
	'watering',
	'SUM(WateringSec) WateringSec',
	'YEAR(WateringDateTime) = :CurrentYear',
	'',
	'',
	[
		':CurrentYear' => date("Y")
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


// data for chart
$WateringCountMont = $Program->selectAll(
	'watering',
	'COUNT(WateringID) AS WateringCount, MONTH(WateringDateTime) AS Month, SUM(WateringSec) As WateringSec',
	'YEAR(WateringDateTime) = :CurrentYear GROUP BY MONTH(WateringDateTime)',
	'',
	'',
	[
		':CurrentYear' => date("Y")
	]
);
if ($WateringCountMont['status'] == 'error') {
	echo "<pre>";
	print_r($WateringCountMont);
	exit;
}

if (isset($WateringCountMont['data']) && $WateringCountMont['data']) {
	$TotalWateringCountMont = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
	$TotalWateringSec = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

	foreach ($WateringCountMont['data'] as $WateringCountByMont) {
		$TotalWateringCountMont[$WateringCountByMont['Month'] - 1] = $WateringCountByMont['WateringCount'];
		$TotalWateringSec[$WateringCountByMont['Month'] - 1] = number_format(($WateringCountByMont['WateringSec'] * 4) / 600000, 2);
	}
} else {
	$TotalWateringCountMont = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
	$TotalWateringSec = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
}
$TotalWateringCountMont = implode(",", $TotalWateringCountMont);
$TotalWateringSec = implode(",", $TotalWateringSec);


// watering data in this month
$WateringData = $Program->selectAll(
	'watering',
	'*',
	'YEAR(WateringDateTime) = :CurrentYear',
	'',
	'',
	[
		':CurrentYear' => date('Y')
	]
);
$WateringData = $WateringData['data'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<link href="https://dsil.kmutt.ac.th/service/assets/plugins/fullcalendar/css/main.min.css" rel="stylesheet" />
	<?php include("./structures/head.php") ?>
	<link href="https://dsil.kmutt.ac.th/service/assets/plugins/apexcharts-bundle/css/apexcharts.css" rel="stylesheet" />
	<title>รายงานประจำปี</title>
</head>

<body class="bg-body-tertiary">
	<?php include("./components/header.php") ?>
	<div class="container mt-5 mb-5">

		<div class="card border-top border-0 border-4 border-success shadow">
			<div class="card-body p-4">
				<div class="card-title">
					<div class="d-flex justify-content-between align-items-center">
						<div class="">
							<h3 class="mb-0 text-success fw-bold">รายงานประจำปี <?php echo date("Y") + 543 ?></h3>
						</div>
					</div>
				</div>
				<hr>
				<div class="row row-cols-1 row-cols-md-3 g-3 mt-3">
					<div class="col" data-aos="fade-up" data-aos-duration="1500">
						<div class="card radius-15 mb-0 shadow-sm border">
							<div class="card-body text-center">
								<div class="widgets-icons mx-auto rounded-circle bg-success text-white">
									<i class="bi bi-droplet-half"></i>
								</div>
								<h4 class="mb-0 font-weight-bold mt-3"><?php echo "{$CountWatering} ครั้ง" ?></h4>
								<p class="mb-0">จำนวนการรดน้ำทั้งหมด</p>
							</div>
						</div>
					</div>
					<div class="col" data-aos="fade-up" data-aos-duration="1500">
						<div class="card radius-15 mb-0 shadow-sm border">
							<div class="card-body text-center">
								<div class="widgets-icons mx-auto bg-success text-white rounded-circle">
									<i class="bi bi-moisture"></i>
								</div>
								<h4 class="mb-0 font-weight-bold mt-3"><?php echo "{$CountWaterLiter}" ?></h4>
								<p class="mb-0">ปริมาณน้ำทั้งหมด</p>
							</div>
						</div>
					</div>
					<div class="col" data-aos="fade-up" data-aos-duration="1500">
						<div class="card radius-15 mb-0 shadow-sm border">
							<div class="card-body text-center">
								<div class="widgets-icons mx-auto bg-success rounded-circle text-white">
									<i class="bi bi-alarm-fill"></i>
								</div>
								<h4 class="mb-0 font-weight-bold mt-3"><?Php echo "{$CountWateringTime}" ?></h4>
								<p class="mb-0">เวลาที่รดน้ำทั้งหมด</p>
							</div>
						</div>
					</div>
				</div>

				<div class="row mt-3 g-3" data-aos="fade-up" data-aos-duration="2000">
					<div class="col-sm">
						<div class="card radius-15 shadow-sm">
							<div class="card-body">
								<div id="TotalNumberWateringChart"></div>
							</div>
						</div>
					</div>
					<div class="col-sm">
						<div class="card radius-15 shadow-sm">
							<div class="card-body">
								<div id="TotalWateringChart"></div>
							</div>
						</div>
					</div>
				</div>

				<div class="row mt-4" data-aos="fade-up" data-aos-duration="2000">
					<div class="col-sm">
						<div class="card radius-15 shadow-sm border">
							<div class="card-body">
								<div id="WateringCalendar"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>


	<?php include('./components/footer.php') ?>
	<?php include("./structures/script.php") ?>
	<script src="https://dsil.kmutt.ac.th/service/assets/plugins/fullcalendar/js/main.min.js"></script>
	<script src="https://dsil.kmutt.ac.th/service/assets/plugins/apexcharts-bundle/js/apexcharts.min.js"></script>
	<script>
		// chart
		const WateringCountMont = "<?php echo $TotalWateringCountMont ?>".split(",");
		const TotalWateringSec = "<?php echo $TotalWateringSec ?>".split(",");

		// TotalNumberWateringChart
		var options = {
			series: [{
				name: '',
				data: WateringCountMont
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
			colors: ['#673ab7'],
			title: {
				text: 'การรดน้ำตลอดการใช้งาน (ครั้ง)',
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
				categories: ["ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."]
			}
		};
		var chart = new ApexCharts(document.querySelector("#TotalNumberWateringChart"), options);
		chart.render();
		// TotalWateringChart
		var options = {
			series: [{
				name: '',
				data: TotalWateringSec
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
			colors: ['#198fed'],
			title: {
				text: 'ปริมาณน้ำทั้งหมด (ลิตร)',
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
				categories: ["ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."]
			},
			tooltip: {
				x: {
					format: 'dd/MM/yy HH:mm'
				},
			},
		};
		var chart = new ApexCharts(document.querySelector("#TotalWateringChart"), options);
		chart.render();


		document.addEventListener('DOMContentLoaded', function() {
			var calendarEl = document.getElementById('WateringCalendar');
			var calendar = new FullCalendar.Calendar(calendarEl, {
				locale: 'th',
				headerToolbar: {
					left: 'prev,next today',
					center: 'title',
					right: 'listWeek'
				},
				initialView: 'listWeek',
				navLinks: true, // can click day/week names to navigate views
				selectable: true,
				nowIndicator: true,
				dayMaxEvents: true, // allow "more" link when too many events
				editable: true,
				selectable: true,
				businessHours: true,
				dayMaxEvents: true, // allow "more" link when too many events
				events: [
					<?php foreach ($WateringData as $row) : ?>
						<?php $row['WateringSec'] = FormatWateringTime($row['WateringSec']) ?>
						<?php
						echo "
							{
								title: 'รดน้ำ {$row['WateringSec']}',
								start: '{$row['WateringDateTime']}'
							},
							"
						?>
					<?php endforeach ?>
				],
			});
			calendar.render();
		});
	</script>
</body>

</html>