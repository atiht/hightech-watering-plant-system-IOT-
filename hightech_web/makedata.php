<?php
// การเชื่อมต่อกับฐานข้อมูล MySQL
$servername = "localhost";
$username = "tnjdevelop_hightech";
$password = "bgXtVsjuJeue6NsRRntV";
$dbname = "tnjdevelop_hightech_project";

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}

// ฟังก์ชั่นสุ่มเวลาภายในช่วงที่กำหนด
function getRandomTime($startHour) {
    $randomSeconds = rand(0, 3600); // สุ่มวินาทีภายใน 1 ชั่วโมง
    return $startHour * 3600 + $randomSeconds; // แปลงเป็นวินาทีทั้งหมดของวัน
}

// กำหนดช่วงวันที่
$startDate = new DateTime("2024-05-02");
$endDate = new DateTime("2024-06-28");

// ลูปผ่านทุกวันในช่วงที่กำหนด
$currentDate = clone $startDate;
while ($currentDate <= $endDate) {
    // สุ่มเวลาในช่วงเช้า (06:00:00 - 07:00:00)
    $morningSeconds = getRandomTime(6);
    $morningTime = clone $currentDate;
    $morningTime->modify("+$morningSeconds seconds");

    // สุ่มเวลาในช่วงเย็น (16:00:00 - 17:00:00)
    $eveningSeconds = getRandomTime(16);
    $eveningTime = clone $currentDate;
    $eveningTime->modify("+$eveningSeconds seconds");

    // สร้างและรัน SQL statements
    $sqlInsertMorning = $conn->prepare("INSERT INTO watering (WateringSec, WateringDateTime) VALUES (?, ?)");
    $sqlInsertMorning->bind_param("is", $wateringSec, $wateringDateTime);
    
    $wateringSec = 5000;
    $wateringDateTime = $morningTime->format('Y-m-d H:i:s');
    $sqlInsertMorning->execute();

    $sqlInsertEvening = $conn->prepare("INSERT INTO watering (WateringSec, WateringDateTime) VALUES (?, ?)");
    $sqlInsertEvening->bind_param("is", $wateringSec, $wateringDateTime);

    $wateringDateTime = $eveningTime->format('Y-m-d H:i:s');
    $sqlInsertEvening->execute();

    // ไปยังวันถัดไป
    $currentDate->modify("+1 day");
}

// ปิดการเชื่อมต่อกับฐานข้อมูล
$conn->close();

echo "Data insertion completed.";
?>
