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
class Settings implements SettingsInterface
{
    private array $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return mixed
     */
    public function get(string $key = '')
    {
        return (empty($key)) ? $this->settings : $this->settings[$key];
        // ?? throw new \Exception(strtoupper($key).' is not a defined in config setting.');
    }

    /**
     * @return void 
     */
    public function __set($key, $value):void 
    {
        $this->settings[$key] = $value;
    }
}