<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Browsershot Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure the Browsershot settings for PDF generation.
    | These settings will be applied automatically to all PDF generations.
    |
    */

    'browsershot' => [
        /*
        |--------------------------------------------------------------------------
        | Node.js Binary Path
        |--------------------------------------------------------------------------
        |
        | The path to the Node.js binary. This is required for Browsershot to work.
        | If not set, Browsershot will try to find Node.js in the system PATH.
        |
        */
        'node_binary' => env('BROWSERSHOT_NODE_BINARY', 'C:\\Program Files\\nodejs\\node.exe'),

        /*
        |--------------------------------------------------------------------------
        | NPM Binary Path
        |--------------------------------------------------------------------------
        |
        | The path to the NPM binary. This is used to determine the node_modules path.
        | If not set, Browsershot will try to find NPM in the system PATH.
        |
        */
        'npm_binary' => env('BROWSERSHOT_NPM_BINARY', 'C:\\Program Files\\nodejs\\npm.cmd'),

        /*
        |--------------------------------------------------------------------------
        | Include Path
        |--------------------------------------------------------------------------
        |
        | Additional paths to include in the PATH environment variable when
        | executing Node.js commands. This can help resolve binary location issues.
        |
        */
        'include_path' => env('BROWSERSHOT_INCLUDE_PATH', 'C:\\Program Files\\nodejs;C:\\Windows\\System32'),

        /*
        |--------------------------------------------------------------------------
        | Chrome/Chromium Path
        |--------------------------------------------------------------------------
        |
        | The path to the Chrome or Chromium binary. If not set, Browsershot will
        | try to find Chrome/Chromium automatically.
        |
        */
        'chrome_path' => env('BROWSERSHOT_CHROME_PATH'),

        /*
        |--------------------------------------------------------------------------
        | Node Modules Path
        |--------------------------------------------------------------------------
        |
        | The path to the node_modules directory. If not set, Browsershot will
        | try to determine this automatically using NPM.
        |
        */
        'node_modules_path' => env('BROWSERSHOT_NODE_MODULES_PATH', 'C:\\Program Files\\nodejs\\node_modules'),

        /*
        |--------------------------------------------------------------------------
        | Bin Path
        |--------------------------------------------------------------------------
        |
        | The path to the directory containing the Browsershot binary files.
        | Usually this is vendor/spatie/browsershot/bin.
        |
        */
        'bin_path' => env('BROWSERSHOT_BIN_PATH'),

        /*
        |--------------------------------------------------------------------------
        | Temporary Path
        |--------------------------------------------------------------------------
        |
        | Custom temporary directory for Browsershot operations.
        | If not set, the system temporary directory will be used.
        |
        */
        'temp_path' => env('BROWSERSHOT_TEMP_PATH'),

        /*
        |--------------------------------------------------------------------------
        | Write Options to File
        |--------------------------------------------------------------------------
        |
        | Whether to write Browsershot options to a temporary file instead of
        | passing them as command line arguments. This can help with long
        | command lines that exceed system limits.
        |
        */
        'write_options_to_file' => env('BROWSERSHOT_WRITE_OPTIONS_TO_FILE', false),
    ],
];