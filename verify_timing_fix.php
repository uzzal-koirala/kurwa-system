<?php
function check_open($curr_time, $open, $close) {
    if ($open <= $close) {
        return ($curr_time >= $open && $curr_time <= $close);
    } else {
        return ($curr_time >= $open || $curr_time <= $close);
    }
}

$test_cases = [
    ['curr' => '10:00:00', 'open' => '08:00:00', 'close' => '22:00:00', 'expected' => true],
    ['curr' => '23:00:00', 'open' => '08:00:00', 'close' => '22:00:00', 'expected' => false],
    ['curr' => '02:00:00', 'open' => '20:00:00', 'close' => '04:00:00', 'expected' => true],
    ['curr' => '21:00:00', 'open' => '20:00:00', 'close' => '04:00:00', 'expected' => true],
    ['curr' => '05:00:00', 'open' => '20:00:00', 'close' => '04:00:00', 'expected' => false],
    ['curr' => '19:00:00', 'open' => '20:00:00', 'close' => '04:00:00', 'expected' => false],
    ['curr' => '03:00:00', 'open' => '08:00:00', 'close' => '04:00:00', 'expected' => true], // User's specific case
];

echo "Running Verification Tests:\n";
echo str_repeat("-", 60) . "\n";
echo sprintf("%-10s | %-10s | %-10s | %-8s | %-6s\n", "Current", "Open", "Close", "Expected", "Result");
echo str_repeat("-", 60) . "\n";

$all_passed = true;
foreach ($test_cases as $tc) {
    $res = check_open($tc['curr'], $tc['open'], $tc['close']);
    $passed = ($res === $tc['expected']);
    if (!$passed) $all_passed = false;
    echo sprintf("%-10s | %-10s | %-10s | %-8s | %-6s\n", 
        $tc['curr'], $tc['open'], $tc['close'], 
        $tc['expected'] ? "Open" : "Closed", 
        $passed ? "PASS" : "FAIL"
    );
}
echo str_repeat("-", 60) . "\n";
if ($all_passed) {
    echo "SUMMARY: ALL TESTS PASSED!\n";
} else {
    echo "SUMMARY: SOME TESTS FAILED!\n";
}
?>
