<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Reject to QC Permissions
    |--------------------------------------------------------------------------
    |
    | Define the permission action on the "Checksheet Approvals" menu in
    | Promise Admin (Override Permissions) that authorizes a user to view and click
    | the "Reject to QC" button.
    |
    | Default: 'delete' (Administrator has this by default; other users can be
    | granted this via Override Permissions on Checksheet Approvals in Promise Admin).
    |
    */
    'reject_to_qc' => [
        'action' => env('REJECT_TO_QC_ACTION', 'delete'),
    ],
];
