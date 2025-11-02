<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "booking_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "การเชื่อมต่อล้มเหลว: " . $e->getMessage();
}

// คำสั่ง SQL เพื่อดึงข้อมูลการใช้งานห้อง
$query = "SELECT room_id, DATE(start_date) AS date, SUM(TIMESTAMPDIFF(HOUR, start_date, end_date)) AS hours
          FROM tb_booking
          WHERE DATE(start_date) = CURDATE() 
          GROUP BY room_id, DATE(start_date)";

$stmt = $conn->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cost_per_kwh = 5; // ค่าใช้จ่ายต่อ kWh
$room_names = [101, 102, 'สุพรรณกัลยา', 201, 301, 302, 303, 304, 305, 306, 307, 308, 309, 310];

// รายละเอียดอุปกรณ์ในแต่ละห้อง
$room_equipment = [
    0 => [ // ห้อง 101
        ['type' => 'โคมไฟดาวน์ไลท์ Dimmable 6 นิ้ว', 'watt' => 10, 'quantity' => 25],
        ['type' => 'โคมระย้า', 'watt' => 14.7, 'quantity' => 25],
        ['type' => 'พักลงระบายอากาศ 6 นิ้ว', 'watt' => 14, 'quantity' => 2]
    ],
    1 => [ // ห้อง 102
        ['type' => 'โคมไฟ LED 12W', 'watt' => 12, 'quantity' => 15],
        ['type' => 'เครื่องปรับอากาศ', 'watt' => 1200, 'quantity' => 1]
    ],
    2 => [ // ห้อง สุพรรณกัลยา
        ['type' => 'โคมไฟฟ้า 15W', 'watt' => 15, 'quantity' => 20],
        ['type' => 'พัดลม', 'watt' => 75, 'quantity' => 3]
    ],
    3 => [ // ห้อง 201
        ['type' => 'โคมไฟ LED 10W', 'watt' => 10, 'quantity' => 10],
        ['type' => 'คอมพิวเตอร์', 'watt' => 300, 'quantity' => 5]
    ],
    4 => [ // ห้อง 301
        ['type' => 'โคมไฟ LED 20W', 'watt' => 20, 'quantity' => 12],
        ['type' => 'โปรเจคเตอร์', 'watt' => 250, 'quantity' => 1]
    ],
    5 => [ // ห้อง 302
        ['type' => 'โคมไฟ LED 15W', 'watt' => 15, 'quantity' => 15],
        ['type' => 'แล็ปท็อป', 'watt' => 50, 'quantity' => 10]
    ],
    6 => [ // ห้อง 303
        ['type' => 'โคมไฟดาวน์ไลท์ 12W', 'watt' => 12, 'quantity' => 8],
        ['type' => 'เครื่องฉายภาพ', 'watt' => 200, 'quantity' => 1]
    ],
    7 => [ // ห้อง 304
        ['type' => 'โคมไฟ LED 10W', 'watt' => 10, 'quantity' => 15],
        ['type' => 'เครื่องคอมพิวเตอร์', 'watt' => 400, 'quantity' => 5]
    ],
    8 => [ // ห้อง 305
        ['type' => 'โคมไฟฟ้า 18W', 'watt' => 18, 'quantity' => 10],
        ['type' => 'แอร์', 'watt' => 1200, 'quantity' => 1]
    ],
    9 => [ // ห้อง 306
        ['type' => 'โคมไฟ LED 15W', 'watt' => 15, 'quantity' => 20],
        ['type' => 'พัดลม', 'watt' => 75, 'quantity' => 4]
    ],
    10 => [ // ห้อง 307
        ['type' => 'โคมไฟดาวน์ไลท์ 12W', 'watt' => 12, 'quantity' => 10],
        ['type' => 'คอมพิวเตอร์', 'watt' => 300, 'quantity' => 3]
    ],
    11 => [ // ห้อง 308
        ['type' => 'โคมไฟ LED 10W', 'watt' => 10, 'quantity' => 12],
        ['type' => 'เครื่องปรับอากาศ', 'watt' => 1200, 'quantity' => 1]
    ],
    12 => [ // ห้อง 309
        ['type' => 'โคมไฟ LED 12W', 'watt' => 12, 'quantity' => 15],
        ['type' => 'เครื่องฉาย', 'watt' => 200, 'quantity' => 1]
    ],
    13 => [ // ห้อง 310
        ['type' => 'โคมไฟฟ้า 15W', 'watt' => 15, 'quantity' => 10],
        ['type' => 'พัดลม', 'watt' => 75, 'quantity' => 2]
    ],
];

// เก็บข้อมูลการใช้งานและค่าใช้จ่าย
$data_usage = array_fill(0, count($room_names), 0);
$data_cost = array_fill(0, count($room_names), 0);

foreach ($results as $res) {
    $hours = $res['hours'];
    $room_index = $res['room_id'] - 1;

    // คำนวณการใช้พลังงานตามอุปกรณ์
    $watt_usage = 0;
    if (isset($room_equipment[$room_index])) {
        foreach ($room_equipment[$room_index] as $equipment) {
            $watt_usage += $equipment['watt'] * $equipment['quantity'];
        }
    }

    // คำนวณค่าใช้จ่าย
    $kwh_usage = ($watt_usage * $hours) / 1000;
    $cost = $kwh_usage * $cost_per_kwh;

    if (isset($room_names[$room_index])) {
        $data_usage[$room_index] = $watt_usage * $hours; // การใช้ไฟฟ้า (วัตต์)
        $data_cost[$room_index] = $cost; // ค่าใช้จ่าย
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>การใช้ไฟฟ้าและค่าใช้จ่ายรายวันตามห้องประชุม</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f0f0f0; /* เปลี่ยนสีพื้นหลัง */
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #4A90E2;
            padding: 20px;
            color: white;
        }
        header img {
            width: 100px;
            vertical-align: middle;
        }
        h2 {
            color: #333;
            margin-top: 10px;
        }
        #chartContainer {
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        canvas {
            max-width: 1000px; /* ขยายความกว้างของกราฟ */
            max-height: 800px; /* ขยายความสูงของกราฟ */
            width: 100%; /* ทำให้กราฟตอบสนองต่อขนาดหน้าจอ */
            height: auto;
        }
        footer {
            margin-top: 20px;
            padding: 10px;
            background-color: #333;
            color: white;
            position: relative;
            bottom: 0;
            width: 100%;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <img src="https://upload.wikimedia.org/wikipedia/th/thumb/3/3a/Naresuan_University_Logo.svg/800px-Naresuan_University_Logo.svg.png" alt="โลโก้">
        <h2>การใช้ไฟฟ้าและค่าใช้จ่ายรายวันตามห้องประชุม</h2>
    </header>
    <div id="chartContainer">
        <canvas id="usageCostChart" width="800" height="500"></canvas> <!-- ขยายขนาด -->
    </div>
    <footer>
        <p>&copy; <?= date("Y"); ?> มหาวิทยาลัยนเรศวร</p>
    </footer>

    <script>
        const roomNames = <?= json_encode($room_names); ?>;
        const usageData = <?= json_encode($data_usage); ?>;
        const costData = <?= json_encode($data_cost); ?>;

        const ctx = document.getElementById('usageCostChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: roomNames,
                datasets: [
                    {
                        label: 'การใช้ไฟฟ้า (W)',
                        data: usageData,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'ค่าใช้จ่าย (THB)',
                        data: costData,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                scales: {
                    x: {
                        title: { display: true, text: 'ห้อง', color: '#666' },
                        ticks: { color: '#333' }
                    },
                    y: {
                        title: { display: true, text: 'การใช้ / ค่าใช้จ่าย', color: '#666' },
                        ticks: { color: '#333', beginAtZero: true }
                    }
                },
                responsive: true,
                plugins: {
                    legend: { position: 'top', labels: { color: '#333' } }
                }
            }
        });
    </script>
</body>
</html>
