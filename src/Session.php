<?php
/**
 * This file is part of the EasyCore package.
 *
 * (c) Marcin Stodulski <marcin.stodulski@devsprint.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace mstodulski\session;

class Session {

    public static bool $sessionStarted = false;

    private static function getPath($pathElement, array $sessionDataPathArray = []): ?string
    {
        if (isset($sessionDataPathArray[$pathElement]))
        {
            if (isset($_SERVER['HTTP_HOST'])) {
                $path = str_replace('.', '_', $_SERVER['HTTP_HOST']) . '/' . $sessionDataPathArray[$pathElement];
            }
            else {
                $path = $sessionDataPathArray[$pathElement];
            }

            return $path;
        }
        else {
            return null;
        }
    }

    public static function run(string $sessionFilesPath = null) : bool
    {
        if (null != $sessionFilesPath) {
            if (!file_exists(getcwd() . '/..' . $sessionFilesPath)) {
                mkdir(getcwd() . '/..' . $sessionFilesPath, 0700, true);
            }

            $sessionFilesDir = '/..' . $sessionFilesPath;
            if (!file_exists(getcwd() . $sessionFilesDir . '/.htaccess')) {
                file_put_contents(getcwd() . $sessionFilesDir . '/.htaccess', "order allow,deny\r\ndeny from all");
            }

            ini_set('session.save_path', getcwd() .  $sessionFilesDir);
        }

        if (!self::$sessionStarted) {
            self::$sessionStarted = true;

            return session_start();
        } else {
            return true;
        }
    }

    public static function finish() : bool
    {
        self::$sessionStarted = false;
        return session_write_close();
    }

    public static function saveValueToSession($pathElement, $value, $sessionDataPathArray)
    {
        $path = self::getPath($pathElement, $sessionDataPathArray);

        $path = trim($path, " /\t\n\r\0\x0B\x2F");
        $pathParts = explode('/', $path);

        $current = &$_SESSION;
        foreach ($pathParts as $key) {
            $current = &$current[$key];
        }

        $current = $value;
    }

    public static function getValueFromSession($pathElement, $sessionDataPathArray)
    {
        $path = self::getPath($pathElement, $sessionDataPathArray);

        $path = trim($path, " /\t\n\r\0\x0B\x2F");
        $pathParts = explode('/', $path);

        $current = &$_SESSION;
        foreach ($pathParts as $key) {
            $current = &$current[$key];
        }

        return $current;
    }

    public static function removeValueFromSession($pathElement, $sessionDataPathArray)
    {
        $path = self::getPath($pathElement, $sessionDataPathArray);

        $path = trim($path, " /\t\n\r\0\x0B\x2F");
        $pathParts = explode('/', $path);

        $lastValue = end($pathParts);
        $arrayKeys = array_keys($pathParts);
        $lastKey = end($arrayKeys);

        unset($pathParts[$lastKey]);

        $current = &$_SESSION;
        foreach ($pathParts as $key) {
            $current = &$current[$key];
        }

        unset($current[$lastValue]);
    }
}
