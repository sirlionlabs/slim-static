<?php
namespace Statical\SlimStatic;

class Config extends SlimSugar
{
	/**
	 * @since 4.0.0 Get settings from the container, utilizing included SettingsInterface
	 * @todo decouple from SettingsInterface, for now it's useful for me.
	 * @param string $key Supports 1-level dot notation.
	 */
	public static function get(string $key = '')
	{
		# Convert to array
		$key = str_contains($key, '.') ? $key = explode('.', $key, 2) : [$key];

		# Key is always array[0]
		$setting = static::$slim->getContainer()->get('config')->get($key[0]);

		# Dot notation if array[1] exists
		return isset($key[1]) ? $setting[$key[1]] : $setting;
	}

	/**
	 * @since 4.0.0 Updated to container definition since Slim4 no longer has config()
	 */
	public static function set(string $key, string $value)
	{
		static::$slim->getContainer()->get('config')->{$key} = $value;
	}

}
