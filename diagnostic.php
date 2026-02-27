<?php
// ============================================================
// DIAGNOSTIC SCRIPT - Cek Koneksi Database
// ============================================================

echo "=== DIAGNOSTIC CRM DATABASE CONNECTION ===\n\n";

// 1. Check PHP Version & Extensions
echo "1. PHP dan Extensions:\n";
echo "   PHP Version: " . phpversion() . "\n";
echo "   MySQLi Extension: " . (extension_loaded('mysqli') ? "✓ Loaded" : "✗ NOT Loaded") . "\n";
echo "   PDO MySQL: " . (extension_loaded('pdo_mysql') ? "✓ Loaded" : "✗ NOT Loaded") . "\n\n";

// 2. Check MySQL Connection
echo "2. MySQL Connection Test:\n";
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'db_crm';

try {
    $mysqli = new mysqli($hostname, $username, $password, $database);
    
    if ($mysqli->connect_error) {
        echo "   ✗ Connection FAILED\n";
        echo "   Error: " . $mysqli->connect_error . "\n";
        echo "   Error Code: " . $mysqli->connect_errno . "\n\n";
    } else {
        echo "   ✓ Connection SUCCESS\n";
        echo "   Host: $hostname\n";
        echo "   Database: $database\n";
        echo "   MySQL Version: " . $mysqli->server_info . "\n\n";
        
        // 3. Check Tables
        echo "3. Database Tables:\n";
        $tables = [];
        $result = $mysqli->query("SHOW TABLES");
        if ($result) {
            while ($row = $result->fetch_row()) {
                $tables[] = $row[0];
            }
            echo "   Found " . count($tables) . " tables:\n";
            foreach ($tables as $table) {
                echo "      - $table\n";
            }
            echo "\n";
        }
        
        // 4. Check Critical Tables
        echo "4. Critical Tables Status:\n";
        $critical = ['cm_task', 'cm_category', 'cm_status'];
        foreach ($critical as $table) {
            $result = $mysqli->query("SELECT COUNT(*) as cnt FROM $table");
            if ($result) {
                $row = $result->fetch_assoc();
                echo "   ✓ $table: " . $row['cnt'] . " records\n";
            } else {
                echo "   ✗ $table: ERROR - " . $mysqli->error . "\n";
            }
        }
        echo "\n";
        
        // 5. Test Query
        echo "5. Test Query (get_drilldown_verifikasi simulation):\n";
        $query = "
            SELECT COUNT(*) as total
            FROM cm_task t
            JOIN cm_category c ON c.id = t.id_category
            JOIN cm_status s ON s.id = t.status
            WHERE t.status NOT IN (1, 2, 8, 9)
            LIMIT 1
        ";
        $result = $mysqli->query($query);
        if ($result) {
            $row = $result->fetch_assoc();
            echo "   ✓ Query OK - Total rekam: " . $row['total'] . "\n\n";
        } else {
            echo "   ✗ Query FAILED: " . $mysqli->error . "\n\n";
        }
        
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "   ✗ Exception: " . $e->getMessage() . "\n\n";
}

// 6. Check CodeIgniter Config
echo "6. CodeIgniter Database Configuration:\n";
$config_file = __DIR__ . '/application/config/database.php';
if (file_exists($config_file)) {
    echo "   ✓ Config file found\n";
    $file_content = file_get_contents($config_file);
    // Extract config values
    if (preg_match("/'hostname'\s*=>\s*'([^']+)'/", $file_content, $m)) echo "     Hostname: {$m[1]}\n";
    if (preg_match("/'username'\s*=>\s*'([^']+)'/", $file_content, $m)) echo "     Username: {$m[1]}\n";
    if (preg_match("/'database'\s*=>\s*'([^']+)'/", $file_content, $m)) echo "     Database: {$m[1]}\n";
    if (preg_match("/'dbdriver'\s*=>\s*'([^']+)'/", $file_content, $m)) echo "     Driver: {$m[1]}\n";
    echo "\n";
} else {
    echo "   ✗ Config file NOT found: $config_file\n\n";
}

// 7. Check Controller
echo "7. Controller Status:\n";
$controller_file = __DIR__ . '/application/controllers/dashboard.php';
if (file_exists($controller_file)) {
    echo "   ✓ dashboard.php found\n";
} else {
    echo "   ✗ dashboard.php NOT found\n";
}

$model_file = __DIR__ . '/application/models/Dashboard_model.php';
if (file_exists($model_file)) {
    echo "   ✓ Dashboard_model.php found\n";
} else {
    echo "   ✗ Dashboard_model.php NOT found\n";
}
echo "\n";

echo "=== END DIAGNOSTIC ===\n";
?>
