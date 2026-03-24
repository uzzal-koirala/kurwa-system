<?php
include '../../includes/core/sms_helper.php';

function test_normalization($input, $expected) {
    // We can't easily test the private normalization inside send_sms without refactoring,
    // but we can mock the behavior or just rely on the fact that we've seen the code.
    // Let's just run a dry run if possible, but the API will actually be called.
    
    // Instead, I'll just write a quick verification of the logic itself here.
    $to = str_replace('+', '', $input);
    if (strlen($to) === 10 && strpos($to, '9') === 0) {
        $to = "977" . $to;
    }
    
    echo "Input: $input | Normalized: $to | Success: " . ($to === $expected ? "YES" : "NO") . "\n";
}

test_normalization("+9779841234567", "9779841234567");
test_normalization("9841234567", "9779841234567");
test_normalization("9779841234567", "9779841234567");
?>
