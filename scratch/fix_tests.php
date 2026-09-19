<?php

$files = [
    'tests/Feature/ApprovalWorkflowHardeningTest.php',
    'tests/Feature/ApprovalWorkflowStateMachineTest.php',
    'tests/Feature/ProjectApprovalBladeViewTest.php',
    'tests/Feature/ProjectApprovalControllerTest.php',
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    // Find User::withoutGlobalScopes()->create arrays and add organization_type => 'internal'

    // Simplest replacement: find 'status' => 'Active',
    // and replace with 'status' => 'Active', 'organization_type' => 'internal',
    $content = str_replace("'status' => 'Active',", "'status' => 'Active',\n            'organization_type' => 'internal',", $content);

    file_put_contents($file, $content);
}
echo 'Done replacing.';
