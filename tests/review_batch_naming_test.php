<?php

require_once dirname(__FILE__) . '/../src/ReviewBatchNaming.php';

function assertNamingSame($expected, $actual, $message) {
    if ($expected !== $actual) {
        throw new Exception($message . ' Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
    }
}

try {
    assertNamingSame('emails.REVIEW.20260619-120000', ReviewBatchNaming::defaultReviewTube('emails', '20260619-120000'), 'Default review tube should keep source tube first');
    assertNamingSame('tube_with_spaces.REVIEW.abc_123', ReviewBatchNaming::defaultReviewTube('tube with spaces', 'abc 123'), 'Default review tube should sanitize source and suffix');
    assertNamingSame('tube.name-1_REVIEW', ReviewBatchNaming::sanitizeTubePart('tube.name-1 REVIEW'), 'Tube-name parts should preserve established safe characters');

    echo "ReviewBatchNaming tests passed.\n";
} catch (Exception $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

