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
        'node_binary' => env('BROWSERSHOT_NODE_BINARY', PHP_OS_FAMILY === 'Windows' ? 'C:\\Program Files\\nodejs\\node.exe' :
            (PHP_OS_FAMILY === 'Darwin' ? '/usr/local/bin/node' : '/usr/bin/node')),

        /*
        |--------------------------------------------------------------------------
        | NPM Binary Path
        |--------------------------------------------------------------------------
        |
        | The path to the NPM binary. This is used to determine the node_modules path.
        | If not set, Browsershot will try to find NPM in the system PATH.
        |
        */
        'npm_binary' => env('BROWSERSHOT_NPM_BINARY', PHP_OS_FAMILY === 'Windows' ? 'C:\\Program Files\\nodejs\\npm.cmd' : '/usr/bin/npm'),

        /*
        |--------------------------------------------------------------------------
        | Include Path
        |--------------------------------------------------------------------------
        |
        | Additional paths to include in the PATH environment variable when
        | executing Node.js commands. This can help resolve binary location issues.
        |
        */
        'include_path' => env('BROWSERSHOT_INCLUDE_PATH', PHP_OS_FAMILY === 'Windows' ? 'C:\\Program Files\\nodejs;C:\\Windows\\System32' :
            (PHP_OS_FAMILY === 'Darwin' ? '/usr/local/bin:/opt/homebrew/bin' : '/usr/bin:/usr/local/bin')),

        /*
        |--------------------------------------------------------------------------
        | Chrome/Chromium Path
        |--------------------------------------------------------------------------
        |
        | The path to the Chrome or Chromium binary. If not set, Browsershot will
        | try to find Chrome/Chromium automatically.
        |
        */
        'chrome_path' => env('BROWSERSHOT_CHROME_PATH', '/usr/bin/chromium-browser'),

        /*
        |--------------------------------------------------------------------------
        | Node Modules Path
        |--------------------------------------------------------------------------
        |
        | The path to the node_modules directory. If not set, Browsershot will
        | try to determine this automatically using NPM.
        |
        */
        'node_modules_path' => env('BROWSERSHOT_NODE_MODULES_PATH', PHP_OS_FAMILY === 'Windows' ? 'C:\\Program Files\\nodejs\\node_modules' : '/usr/lib/node_modules'),

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

        /*
        |--------------------------------------------------------------------------
        | Default Chrome Arguments
        |--------------------------------------------------------------------------
        |
        | Default arguments to pass to Chrome/Chromium for all PDF generations.
        | These help solve common permission and memory issues in server environments.
        |
        */
        'default_chrome_args' => [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-gpu',
            '--disable-web-security',
            '--disable-features=VizDisplayCompositor',
            '--run-all-compositor-stages-before-draw',
            '--disable-backgrounding-occluded-windows',
            '--disable-renderer-backgrounding',
            '--disable-field-trial-config',
            '--disable-ipc-flooding-protection',
            '--memory-pressure-off',
            '--disable-seccomp-filter-sandbox',
            '--disable-software-rasterizer',
            '--disable-extensions',
            '--disable-plugins',
            '--single-process',
            '--no-zygote',
            '--disable-namespace-sandbox'
        ],
    ],
];