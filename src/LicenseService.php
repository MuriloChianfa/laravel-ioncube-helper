<?php

declare(strict_types=1);

namespace MuriloChianfa\LaravelIoncubeHelper;

use DateTime;
use Illuminate\Support\Facades\Route;
use MuriloChianfa\LaravelIoncubeHelper\Exceptions\LicenseNotFoundException;
use MuriloChianfa\LaravelIoncubeHelper\Exceptions\LicenseHasExpiredException;
use MuriloChianfa\LaravelIoncubeHelper\Exceptions\LicenseNotMatchedException;
use MuriloChianfa\LaravelIoncubeHelper\Exceptions\LicenseNotReadableException;

/**
 * LicenseService class...
 *
 * @since 0.1.0
 * @version 1.0.0
 * @author Murilo Chianfa <github.com/MuriloChianfa>
 * @package Services
 *
 * @method bool validate(bool $validate)
 * @method bool exists()
 * @method bool readable()
 * @method bool hasExpired()
 * @method bool serverIsAllowed()
 * @method array getProperties(bool $validate)
 * @method mixed getProperty(string $property, mixed $default, bool $validate)
 * @method string applyDate(bool $validate)
 * @method string expireDate(bool $validate)
 * @method int daysToExpire(bool $validate)
 */
abstract readonly class LicenseService
{
    /** @var string LICENSE_PATH The path license file */
    const LICENSE_PATH = '/opt/project/license/license';

    /** @var string DATE_FORMAT Date format from license file */
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /** @var array SERVERS_WHITELIST Servers thats have permissions without license */
    const SERVERS_WHITELIST = [];

    /**
     * Validade if license is valid
     *
     * @param bool $validate
     * @return bool
     * @static
     */
    public static function validate(bool $validate = true): bool
    {
        if (!self::exists()) {
            throw new LicenseNotFoundException();
        }

        if (!self::readable()) {
            throw new LicenseNotReadableException();
        }

        if (self::hasExpired()) {
            throw new LicenseHasExpiredException();
        }

        if ($validate && !self::serverIsAllowed() && php_sapi_name() !== 'cli') {
            throw new LicenseNotMatchedException();
        }

        return true;
    }

    /**
     * Check if license file exists
     *
     * @return bool
     * @static
     */
    private static function exists(): bool
    {
        return file_exists(static::LICENSE_PATH);
    }

    /**
     * Check if license file is readable from filesystem
     *
     * @return bool
     * @static
     */
    private static function readable(): bool
    {
        return is_readable(static::LICENSE_PATH);
    }

    /**
     * Verify if license has expired
     *
     * @return bool
     * @static
     */
    private static function hasExpired(): bool
    {
        return \ioncube_license_has_expired();
    }

    /**
     * Check if access client is allowed to run for this license
     *
     * @return bool
     * @static
     */
    private static function serverIsAllowed(): bool
    {
        if (in_array(Route::currentRouteName(), static::SERVERS_WHITELIST, true)) {
            return true;
        }

        $allowedServers = \ioncube_licensed_servers();

        // No servers configured
        if (empty($allowedServers)) {
            return true;
        }

        $allowedServers = explode(',', $allowedServers[2]);
        foreach ($allowedServers as $allowedServer) {
            $allowedServer = trim($allowedServer);
            $remoteRequestHttpHost = request()->getHttpHost();

            // Removes the port from incoming requested remote host
            if (strpos($remoteRequestHttpHost, ':') !== false) {
                if (strpos($remoteRequestHttpHost, '[') !== false) {
                    [$remoteRequestHost,] = explode(']:', $remoteRequestHttpHost, 2);
                    $remoteRequestHost = $remoteRequestHost . ']';
                } else {
                    [$remoteRequestHost,] = explode(':', $remoteRequestHttpHost, 2);
                }
            } else {
                $remoteRequestHost = $remoteRequestHttpHost;
            }

            // Allowed servers by license validation
            if ($allowedServer === $remoteRequestHost) {
                return true;
            }

            // Allowed servers by whitelisted routes
            if (in_array(Route::currentRouteName(), static::SERVERS_WHITELIST, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * All custom properties of current license
     *
     * @param bool $validate
     * @return array
     * @static
     */
    public static function getProperties(bool $validate = true): array
    {
        static::validate($validate);

        $properties = \ioncube_license_properties();
        if (empty($properties)) {
            return [];
        }

        return $properties;
    }

    /**
     * Get a custom property of current license
     *
     * @param string $property
     * @param string $string
     * @param bool $validate
     * @return string
     */
    public static function getProperty(string $property, $default = '', bool $validate = true): string
    {
        $properties = (object) self::getProperties($validate);

        if (empty($properties) || !isset($properties->{$property}) || !isset($properties->{$property}['value'])) {
            return $default;
        }

        return $properties->{$property}['value'] ?? $default;
    }

    /**
     * The apply date of current license
     *
     * @param bool $validate
     * @return string
     * @static
     */
    public static function applyDate(bool $validate = true): string
    {
        if (!file_exists(static::LICENSE_PATH)) {
            return date(self::DATE_FORMAT, time());
        }

        return date(self::DATE_FORMAT, filemtime(static::LICENSE_PATH) ?? time());
    }

    /**
     * The expiration date of current license
     *
     * @param bool $validate
     * @return string
     * @static
     */
    public static function expireDate(bool $validate = true): string
    {
        if (!($fileInfo = ioncube_file_info())) {
            return now()->format(self::DATE_FORMAT);
        }

        return date(self::DATE_FORMAT, $fileInfo['FILE_EXPIRY']);
    }

    /**
     * Number of days to expire current license
     *
     * @param bool $validate
     * @return int
     * @static
     */
    public static function daysToExpire(bool $validate = true): int
    {
        $expiryDate = self::expireDate($validate);
        $nowsaday = date(self::DATE_FORMAT, time());

        $expiryDate = DateTime::createFromFormat(self::DATE_FORMAT, $expiryDate);
        $nowsaday = DateTime::createFromFormat(self::DATE_FORMAT, $nowsaday);

        $difference = date_diff($expiryDate, $nowsaday);
        return (int) $difference->format('%a');
    }
}
