<?php

define('LICENSE_PATH', '/opt/project/license/license.txt');
define('UPLOAD_LICENSE_ENDPOINT', '/ioncube.php');
define('PERMIT_ACCESS_THIS_FILE', false);

if (php_sapi_name() !== 'cli' && !empty($_FILES)) {
    $license = $_FILES['license'];

    if (empty($license)) {
        forceIoncubeError(6);
    }

    try {
        if (!isset($license['tmp_name']) || empty($license['tmp_name']) || !is_uploaded_file($license['tmp_name'])) {
            forceIoncubeError(15);
        }

        passthru("sudo mv {$license['tmp_name']} " . LICENSE_PATH);
    } catch (\Throwable $th) {
        forceIoncubeError(15);
    }

    // Runs database migration after upload a license
    shell_exec('php artisan down');
    shell_exec(__DIR__ . '/../storage/migrate.sh');

    // Redirect the user after upload a license
    header('Location: /');
} elseif (php_sapi_name() !== 'cli' && isset($_SERVER) && $_SERVER['REQUEST_URI'] === UPLOAD_LICENSE_ENDPOINT) {
    if (PERMIT_ACCESS_THIS_FILE === true) {
        forceIoncubeError(6);
    }

    // Redirect the user when enters direct in browser
    header('Location: /');
}

/**
 * IonCube error handler for moments with no valid license
 *
 * @param int $errno The IonCube error number
 * @param array $context The context of the error, path to file, path to license, etc...
 * @return void
 */
function ioncube_event_handler($errno, $context)
{
    // Default template
    $template = ioncube_read_file(__DIR__ . '../resources/views/vendor/license/license.blade.php');

    /**
     * Dict extracted from ioncube manual
     *
     * @param array<int,string>
     * @see https://www.ioncube.com/sa/USER-GUIDE.pdf
     */
    $dict = [
        1 => 'corrupt-file',
        2 => 'expired-file',
        3 => 'no-permissions',
        4 => 'clock-skew',
        6 => 'license-not-found',
        7 => 'license-corrupt',
        8 => 'license-expired',
        9 => 'license-property-invalid',
        10 => 'license-header-invalid',
        11 => 'license-server-invalid',
        12 => 'unauth-including-file',
        13 => 'unauth-included-file',
        14 => 'unauth-append-prepend-file',
        // User defined
        15 => 'license-upload-failed'
    ];

    // Retrieve from dict the error message
    $loaderEvent = $dict[$errno] ?? 'unrecognized';

    switch ($errno) {
        // ION_NO_PERMISSIONS
        case 3:
            $title = 'Without permission';
            $subtitle = 'No read permission in the license...';
            break;

        // ION_LICENSE_NOT_FOUND
        case 6:
            $title = 'Missing license';
            $subtitle = 'Submit a valid license to continue...';
            break;

        // ION_LICENSE_CORRUPT
        case 7:
            $title = 'Corrupted';
            $subtitle = 'Your license is not valid...';
            break;

        // ION_LICENSE_EXPIRED
        case 8:
            $title = 'Expired';
            $subtitle = 'Your license has expired...';
            break;

        /**
         * ION_CORRUPT_FILE
         * ION_EXPIRED_FILE
         * ION_CLOCK_SKEW
         * ION_LICENSE_PROPERTY_INVALID
         * ION_LICENSE_HEADER_INVALID
         * ION_LICENSE_SERVER_INVALID
         * ION_UNAUTH_INCLUDING_FILE
         * ION_UNAUTH_INCLUDED_FILE
         * ION_UNAUTH_APPEND_PREPEND_FILE
         */
        default:
            $title = 'Invalid';
            $subtitle = 'An error occurred while trying to read the license...';
            break;
    }

    // Substitute the variables by error messages
    $template = strtr($template, [
        '{{ $title }}' => $title,
        '{{ $subtitle }}' => $subtitle,
        '{{ $server }}' => \ioncube_server_data() ?? '-'
    ]);

    // Show to front-end the template only if is running in apache2 module
    if (php_sapi_name() !== 'cli') {
        if (isset($_SERVER) && $_SERVER['REQUEST_URI'] === UPLOAD_LICENSE_ENDPOINT && PERMIT_ACCESS_THIS_FILE !== true) {
            // Redirect the user when enters direct in browser
            header('Location: /');
            exit;
        }

        shell_exec('php artisan up');

        // Show the template in web browser
        echo $template;
        exit;
    } else {
        // Show in log file the error message only in cli mode
        // error_log("IonCube license error: [{$errno}] {$loaderEvent}");
        exit($errno);
    }
}

/**
 * IonCube error handler for moments with no valid license
 *
 * @param int $errno The IonCube error number
 * @return void
 */
function forceIoncubeError(int $errno)
{
    ioncube_event_handler($errno, []);
}
