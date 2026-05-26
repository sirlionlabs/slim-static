<?php

declare(strict_types=1);

namespace Statical\SlimStatic\Settings;

/**
 * @since 4.0.0 
 * Inspired by Slim v4 Skeleton's \Settings\SettingsInterface.
 * Use in conjunction with your own \Settings\Settings class.
 * 
 * @see https://github.com/slimphp/Slim-Skeleton/blob/main/src/Application/Settings/SettingsInterface.php
 * @see https://discourse.slimframework.com/t/di-container-and-settings/5770/11
 */
interface SettingsInterface
{
    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key = '');

    /**
     * @param string $key
     * @param string $value
     * @return void
     */
    public function __set(string $key, string $value):void;

}