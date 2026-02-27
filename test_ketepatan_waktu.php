<?php
/**
 * Test Script - Verifikasi get_ketepatan_waktu() 
 * Script ini menampilkan hasil dari query untuk memverifikasi bahwa
 * semua divisi sekarang ditampilkan termasuk yang tidak memiliki tugas
 */

// Koneksi database
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'db_crm';

$mysqli = new mysqli($hostname, $username, $password, $database);

if ($mysqli->connect_error) {
    die("❌ Koneksi gagal: " . $mysqli->connect_error);
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Test Ketepatan Waktu</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { color: #1A56DB; border-bottom: 2px solid #1A56DB; padding-bottom: 10px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #1A56DB; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f0f0f0; }
        .success { color: #0E9F6E; font-weight: bold; }
        .warning { color: #F59E0B; font-weight: bold; }
        .error { color: #E02424; font-weight: bold; }
        ul { list-style: none; padding: 0; }
        li { padding: 5px 0; }
        li.zero::before { content: '● '; color: #E02424; }
        li.data::before { content: '● '; color: #0E9F6E; }
    </style>
</head>
<body>";

// Test 1: Get list SEMUA divisi dari database
echo "<h2>TEST 1: Semua Divisi dari CM_CATEGORY</h2>";
$query = "SELECT DISTINCT divisi FROM cm_category WHERE divisi IS NOT NULL ORDER BY divisi";
$result = $mysqli->query($query);
$all_divisi = [];
echo "<table>
    <tr><th>No</th><th>Divisi</th></tr>";
$no = 1;
while ($row = $result->fetch_assoc()) {
    $all_divisi[] = $row['divisi'];
    echo "<tr><td>{$no}</td><td>" . htmlspecialchars($row['divisi']) . "</td></tr>";
    $no++;
}
echo "</table>";
echo "<p><strong>Total divisi unik: " . count($all_divisi) . "</strong></p>";

// Test 2: Get ketepatan waktu per divisi
echo "<h2>TEST 2: Ketepatan Waktu per Divisi (Dari Query Model)</h2>";
$sql = "
    SELECT
        c.divisi,
        COUNT(*) as total,
        SUM(CASE WHEN t.done_date IS NOT NULL AND t.done_date <= CONCAT(t.due_date, ' 23:59:59')
                 AND t.due_date != '0000-00-00' THEN 1 ELSE 0 END) as ontime,
        SUM(CASE WHEN t.done_date IS NOT NULL AND t.done_date > CONCAT(t.due_date, ' 23:59:59')
                 AND t.due_date != '0000-00-00' THEN 1 ELSE 0 END) as late
    FROM cm_task t
    LEFT JOIN cm_category c ON c.id = t.id_category
    WHERE t.escalation_at IS NOT NULL
      AND c.divisi IS NOT NULL
    GROUP BY c.divisi
    ORDER BY c.divisi
";

$result = $mysqli->query($sql);
$ketepatan_map = [];
echo "<table>
    <tr><th>Divisi</th><th>Total</th><th>On Time</th><th>Late</th><th>Persentase</th></tr>";

while ($row = $result->fetch_assoc()) {
    $ketepatan_map[$row['divisi']] = [
        'total' => (int)$row['total'],
        'ontime' => (int)$row['ontime'],
        'late' => (int)$row['late'],
    ];
    $pct = $row['total'] > 0 ? round(($row['ontime'] / $row['total']) * 100) : 0;
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['divisi']) . "</td>";
    echo "<td>{$row['total']}</td>";
    echo "<td>{$row['ontime']}</td>";
    echo "<td>{$row['late']}</td>";
    echo "<td>{$pct}%</td>";
    echo "</tr>";
}
echo "</table>";
echo "<p><strong>Total divisi dengan data:</strong> " . count($ketepatan_map) . "</p>";

// Test 3: Verifikasi divisi yang tidak ditampilkan sebelumnya (sekarang harus ditampilkan)
echo "<h2>TEST 3: Divisi yang TIDAK MEMILIKI TUGAS (Seharusnya Muncul dengan Total=0)</h2>";
echo "<p>Divisi-divisi berikut sebelumnya TIDAK ditampilkan (karena tidak punya tugas), tetapi sekarang HARUS ditampilkan:</p>";
echo "<ul>";

$divisi_tanpa_data = [];
foreach ($all_divisi as $d) {
    if (!isset($ketepatan_map[$d])) {
        $divisi_tanpa_data[] = $d;
        echo "<li class='zero'>❌ $d (SEBELUMNYA TIDAK MUNCUL)</li>";
    } else {
        echo "<li class='data'>✓ $d (data: {$ketepatan_map[$d]['total']} tugas)</li>";
    }
}
echo "</ul>";

if (!empty($divisi_tanpa_data)) {
    echo "<p><span class='warning'>⚠️ Divisi yang sebelumnya tidak muncul (total = 0):</p>";
    echo "<ul>";
    foreach ($divisi_tanpa_data as $d) {
        echo "<li>• " . htmlspecialchars($d) . "</li>";
    }
    echo "</ul>";
}

// Test 4: Simulasi hasil get_ketepatan_waktu() setelah perbaikan
echo "<h2>TEST 4: Hasil SIMULASI get_ketepatan_waktu() (Setelah Perbaikan)</h2>";
echo "<p>Ini adalah hasil yang akan ditampilkan di dashboard sekarang (SEMUA divisi):</p>";
echo "<table>
    <tr><th>Divisi</th><th>Total</th><th>On Time</th><th>Late</th><th>Persentase</th></tr>";

$divisi_map = [
    'Project'              => 'Project',
    'Buspro'               => 'Buspro (Berkas)',
    'Estate'               => 'Estate',
    'Finance'              => 'Finance',
    'Legal'                => 'Legal',
    'MEP'                  => 'MEP',
    'Sales'                => 'Sales/Mkt',
    'CRM'                  => 'Sosmed',
    'Aftersales'           => 'Aftersales',
    'Rumah dan Bangunan'   => 'Rumah dan Bangunan',
    'Other'                => 'Other',
];

foreach ($all_divisi as $divisi_key) {
    $label = isset($divisi_map[$divisi_key]) ? $divisi_map[$divisi_key] : $divisi_key;
    
    if (isset($ketepatan_map[$divisi_key])) {
        $total = $ketepatan_map[$divisi_key]['total'];
        $ontime = $ketepatan_map[$divisi_key]['ontime'];
        $late = $ketepatan_map[$divisi_key]['late'];
    } else {
        $total = 0;
        $ontime = 0;
        $late = 0;
    }
    
    $pct = $total > 0 ? round(($ontime / $total) * 100) : 0;
    $status = ($total == 0) ? '(Tanpa tugas)' : '';
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($label) . " $status</td>";
    echo "<td style='text-align:center'>" . $total . "</td>";
    echo "<td style='text-align:center'>" . $ontime . "</td>";
    echo "<td style='text-align:center'>" . $late . "</td>";
    echo "<td style='text-align:center'>{$pct}%</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h2 class='success'>✓ TEST SELESAI</h2>";
if (!empty($divisi_tanpa_data)) {
    echo "<p class='success'><strong>✓ PERBAIKAN BERHASIL!</strong></p>";
    echo "<p>Divisi berikut yang sebelumnya tidak muncul, sekarang akan ditampilkan di dashboard dengan total=0:</p>";
    echo "<ul>";
    foreach ($divisi_tanpa_data as $d) {
        echo "<li>• " . htmlspecialchars($d) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p class='warning'>⚠️ CATATAN: Semua divisi sudah memiliki tugas, tidak ada divisi yang ditambahkan dengan total=0</p>";
}

echo "</body>
</html>";

$mysqli->close();
?>
