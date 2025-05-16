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
    <title>สั่งรดน้ำ</title>
</head>

<body class="bg-body-tertiary">
    <?php include("./components/header.php") ?>
    <div class="container mt-5 mb-5">
        <div class="card border-top border-0 border-4 border-success shadow" data-aos="fade-up" data-aos-duration="1500">
            <div class="card-body p-4">
                <div class="card-title d-flex align-items-center">
                    <h3 class="mb-0 text-success fw-bold">สั่งรดน้ำ</h3>
                </div>
                <hr>
                <form action="" method="POST" class="needs-validation" novalidate>
                    <div class="row mb-3 mt-3">
                        <div class="col-sm">
                            <div class="input-group">
                                <select name="sec" id="sec" class="form-select form-select-lg" required>
                                    <option value="">คลิกเพื่อเลือก</option>
                                    <option value="1000">1 วินาที</option>
                                    <option value="2000">2 วินาที</option>
                                    <option value="3000">3 วินาที</option>
                                    <option value="4000">4 วินาที</option>
                                    <option value="5000">5 วินาที</option>
                                </select>
                                <button type="submit" name="BtnSubmit" class="btn btn-dark"><i class="bi bi-droplet-half"></i> สั่งรดน้ำ</button>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="card radius-15">
                    <div class="card-body">
                        <div class="card-title">
                            <h5 class="mb-0 text-success">ข้อมูลการรดน้ำของวันนี้</h5>
                        </div>
                        <hr />
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
        </div>
    </div>
    <?php include('./components/footer.php') ?>
    <?php include("./structures/script.php") ?>
    <script>
        async function watering(sec) {
            try {
                $.ajax({
                    type: "POST",
                    url: "backend/API/watering.api.php",
                    data: {
                        'path': '/watering',
                        'sec': sec
                    },
                    dataType: "json",
                    success: (res) => {
                        return res;
                    },
                });
            } catch (err) {

            }
        }

        $(document).ready(async () => {
            const forms = $('.needs-validation');
            forms.each((index, form) => {
                $(form).on('submit', e => {
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                        $(form).addClass('was-validated');
                    } else {
                        e.preventDefault();
                        e.stopPropagation();
                        let sec = $("#sec").val() ?? 0;
                        $.ajax({
                            type: "POST",
                            url: "backend/API/watering.api.php",
                            data: {
                                'path': '/watering',
                                'sec': sec
                            },
                            dataType: "json",
                            success: (res) => {
                                const WateringTime = Number(sec) + 1000;
                                if (res.status == 'success') {
                                    let timerInterval;
                                    Swal.fire({
                                        title: "กำลังลดน้ำ",
                                        html: "ลดน้ำเสร็จใน <b></b> ",
                                        timer: WateringTime,
                                        timerProgressBar: true,
                                        didOpen: () => {
                                            Swal.showLoading();
                                            const timer = Swal
                                                .getPopup()
                                                .querySelector("b");
                                            timerInterval = setInterval(
                                                () => {
                                                    timer
                                                        .textContent =
                                                        `${Swal.getTimerLeft()}`;
                                                }, 100);
                                        },
                                        willClose: () => {
                                            clearInterval(
                                                timerInterval);
                                        }
                                    }).then((result) => {
                                        /* Read more about handling dismissals below */
                                        if (result.dismiss === Swal.DismissReason.timer) {
                                            window.location.reload();
                                        }
                                    });
                                }
                            },
                            error: (err) => {
                                console.error("an error : ", err);
                            }
                        });

                    }
                });
            });
        })
    </script>
</body>

</html>