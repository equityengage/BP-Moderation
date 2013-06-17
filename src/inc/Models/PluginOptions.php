<?php

namespace BPModeration\Models;


class PluginOptions implements \ArrayAccess
{

	const OPT_NAME = 'bp_moderation_options';

	private static $options;

	public function __construct()
	{
		$this->load();
	}

	public function load()
	{
		self::$options = get_site_option(self::OPT_NAME) ? : array();
	}

	public function save()
	{
		return update_site_option(self::OPT_NAME, self::$options);
	}

	public function offsetSet($offset, $value)
	{
		self::$options[$offset] = $value;
	}

	public function offsetExists($offset)
	{
		return array_key_exists($offset, self::$options);
	}

	public function offsetUnset($offset)
	{
		unset(self::$options[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset(self::$options[$offset]) ? self::$options[$offset] : null;
	}
}