<?php
session_start();
include '../includes/session.php';
if (!isAdmin()) {
    header("Location: ../unauthorized.php");
    exit;
}
include '../config/db.php'; // Include the database configuration file

function backupDatabase($host, $user, $pass, $name, $tables = '*') {
    $link = mysqli_connect($host, $user, $pass, $name);

    if (mysqli_connect_errno()) {
        return "MySQL connection failed: " . mysqli_connect_error();
    }

    mysqli_query($link, "SET NAMES 'utf8'");

    // Get all table names
    if ($tables == '*') {
        $tables = array();
        $result = mysqli_query($link, "SHOW TABLES");
        while ($row = mysqli_fetch_row($result)) {
            $tables[] = $row[0];
        }
    } else {
        $tables = is_array($tables) ? $tables : explode(',', $tables);
    }

    $return = '';
    foreach ($tables as $table) {
        $result = mysqli_query($link, 'SELECT * FROM ' . $table);
        $num_fields = mysqli_num_fields($result);

        $return .= 'DROP TABLE IF EXISTS ' . $table . ';';
        $row2 = mysqli_fetch_row(mysqli_query($link, 'SHOW CREATE TABLE ' . $table));
        $return .= "\n\n" . $row2[1] . ";\n\n";

        for ($i = 0; $i < $num_fields; $i++) {
            while ($row = mysqli_fetch_row($result)) {
                $return .= 'INSERT INTO ' . $table . ' VALUES(';
                for ($j = 0; $j < $num_fields; $j++) {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = str_replace("\n", "\\n", $row[$j]);
                    if (isset($row[$j])) {
                        $return .= '"' . $row[$j] . '"';
                    } else {
                        $return .= '""';
                    }
                    if ($j < ($num_fields - 1)) {
                        $return .= ',';
                    }
                }
                $return .= ");\n";
            }
        }
        $return .= "\n\n\n";
    }

    mysqli_close($link);
    return $return;
}

// Get database credentials from config file
$db_host = DB_HOST;
$db_user = DB_USER;
$db_pass = DB_PASSWORD;
$db_name = DB_NAME;

// Perform backup and offer download
$backup_content = backupDatabase($db_host, $db_user, $db_pass, $db_name);

if (is_string($backup_content) && strpos($backup_content, 'MySQL connection failed') !== false) {
    header("Location: admin_panel.php?action=database_backups&error=" . urlencode($backup_content));
    exit;
}

$filename = 'db_backup_' . date('Y-m-d_H-i-s') . '.sql';

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . strlen($backup_content));

echo $backup_content;
exit;
?>