<?php
/**
 * Helpers.php.
 *
 * @author Edwin Dayot
 */

use Shorty\Framework\Exceptions\FileNotFoundException;

if (!function_exists('app')) {
    function app()
    {
        return \Shorty\Framework\Application::getInstance();
    }
}

if (!function_exists('base_dir')) {
    function base_dir(string $path): string
    {
        return getcwd() . '/' . trim($path, '/');
    }
}

if (!function_exists('config')) {
    function config(string $name)
    {
        $path = base_dir('../config/' . $name . '.php');

        if (!file_exists($path)) {
            throw new FileNotFoundException('Config file [' . $name . '] does not exists.');
        }

        return require $path;
    }
}
