<?php
$logFile = 'C:\\xampp\\apache\\logs\\error.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $count = 0;
    foreach (array_reverse($lines) as $line) {
        if (strpos($line, 'SMS API Failure') !== false) {
            echo $line . "\n";
            $count++;
            if ($count >= 5) break; 
        }
    }
} else {
    echo "Log file not found at $logFile\n";
}
?>
