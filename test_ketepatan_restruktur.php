<?php
/**
 * Test Script - Verifikasi Restruktur get_ketepatan_waktu()
 * Memastikan query mengambil data dengan benar dari cm_category dan cm_task
 */

// Koneksi database
$servername = '127.0.0.1';
$username = 'root';
$password = '';
$dbname = 'db_crm';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "<h2>TEST: Restruktur get_ketepatan_waktu()</h2>";
echo "<p>Memverifikasi query mengambil data Done (status=6) dan membandingkan due_date vs done_date</p>";
echo "<hr>";

// TEST 1: Cek jumlah task per status
echo "<h3>TEST 1: Distribusi Task per Status</h3>";
$sql1 = "SELECT s.id, s.status, COUNT(*) as total
         FROM cm_task t
         LEFT JOIN cm_status s ON s.id = t.status
         GROUP BY t.status
         ORDER BY t.status";
$result1 = $conn->query($sql1);

echo "<table border='1' cellpadding='10' style='border-collapse:collapse'>";
echo "<tr><th>Status ID</th><th>Status Name</th><th>Total</th></tr>";
while($row = $result1->fetch_assoc()) {
    echo "<tr><td>" . $row['id'] . "</td><td>" . $row['status'] . "</td><td>" . $row['total'] . "</td></tr>";
}
echo "</table>";

// TEST 2: Cek task dengan status Done (6) yang memiliki done_date
echo "<h3>TEST 2: Task Status Done dengan done_date</h3>";
$sql2 = "SELECT COUNT(*) as total_done_with_date
         FROM cm_task
         WHERE status = 6 AND done_date IS NOT NULL AND done_date != '0000-00-00 00:00:00'";
$result2 = $conn->query($sql2);
$row2 = $result2->fetch_assoc();
echo "<p>Task Done (status=6) dengan done_date valid: <strong>" . $row2['total_done_with_date'] . "</strong></p>";

// TEST 3: Query Ketepatan Waktu per Divisi
echo "<h3>TEST 3: Hasil Query Ketepatan Waktu per Divisi</h3>";
$sql3 = "
    SELECT
        c.divisi,
        COUNT(*) as total,
        SUM(CASE WHEN t.done_date IS NOT NULL 
                 AND CAST(t.done_date AS DATE) <= t.due_date
                 AND t.due_date != '0000-00-00' 
                 THEN 1 ELSE 0 END) as ontime,
        SUM(CASE WHEN t.done_date IS NOT NULL 
                 AND CAST(t.done_date AS DATE) > t.due_date
                 AND t.due_date != '0000-00-00' 
                 THEN 1 ELSE 0 END) as late
    FROM cm_task t
    LEFT JOIN cm_category c ON c.id = t.id_category
    WHERE t.status = 6
      AND t.done_date IS NOT NULL
      AND c.divisi IS NOT NULL
    GROUP BY c.divisi ORDER BY c.divisi
";
$result3 = $conn->query($sql3);

echo "<table border='1' cellpadding='10' style='border-collapse:collapse'>";
echo "<tr><th>Divisi</th><th>Total Done</th><th>On Time</th><th>Late</th><th>% On Time</th></tr>";

if ($result3->num_rows > 0) {
    while($row = $result3->fetch_assoc()) {
        $total = (int)$row['total'];
        $ontime = (int)$row['ontime'];
        $pct = $total > 0 ? round(($ontime / $total) * 100) : 0;
        echo "<tr>";
        echo "<td>" . $row['divisi'] . "</td>";
        echo "<td>" . $total . "</td>";
        echo "<td>" . $ontime . "</td>";
        echo "<td>" . $row['late'] . "</td>";
        echo "<td style='text-align:center'>" . $pct . "%</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5' style='text-align:center'>Tidak ada data</td></tr>";
}
echo "</table>";

// TEST 4: Cek kategori-divisi mapping
echo "<h3>TEST 4: Mapping Divisi dan Kategori</h3>";
$sql4 = "SELECT DISTINCT divisi FROM cm_category ORDER BY divisi";
$result4 = $conn->query($sql4);

$divisi_list = [];
if ($result4->num_rows > 0) {
    while($row = $result4->fetch_assoc()) {
        $divisi_list[] = $row['divisi'];
    }
}

echo "<p>Divisi yang ditemukan di database:</p>";
echo "<ul>";
foreach ($divisi_list as $div) {
    echo "<li>" . htmlspecialchars($div) . "</li>";
}
echo "</ul>";

// TEST 5: Expected divisi list
echo "<h3>TEST 5: Validasi Expected Divisi</h3>";
$expected_divisi = [
    'Project',
    'MEP',
    'Finance',
    'Buspro',
    'Legal',
    'Sales',
    'CRM',
    'Estate',
    'Rumah dan Bangunan',
    'Other',
];

echo "<p>Expected divisi dalam sistem:</p>";
echo "<table border='1' cellpadding='10' style='border-collapse:collapse'>";
echo "<tr><th>Expected</th><th>Found in DB</th><th>Status</th></tr>";

foreach ($expected_divisi as $exp_div) {
    $found = in_array($exp_div, $divisi_list) ? 'Yes' : 'No';
    $status_color = $found === 'Yes' ? '#90EE90' : '#FFB6C6';
    echo "<tr style='background-color:" . $status_color . "'>";
    echo "<td>" . $exp_div . "</td>";
    echo "<td>" . $found . "</td>";
    echo "<td>" . ($found === 'Yes' ? '✓' : '✗') . "</td>";
    echo "</tr>";
}
echo "</table>";

$conn->close();
?>
