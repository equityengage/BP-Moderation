<?php


namespace BPModeration\Lib;


/**
 * Class SimpleContainer
 *
 * A minimal dependency injection container
 *
 * inspired by Pimple, but even simpler
 */
class SimpleContainer implements \ArrayAccess
{
	protected $values = array();

	public function offsetSet($id, $value)
	{
		$this->values[$id] = $value;
	}

	public function offsetGet($id)
	{
		if (!array_key_exists($id, $this->values)) {
			throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
		}
		return ($this->values[$id] instanceof \Closure) ? $this->values[$id]($this) : $this->values[$id];
	}

	public function offsetExists($id)
	{
		return array_key_exists($id, $this->values);
	}

	public function offsetUnset($id)
	{
		unset($this->values[$id]);
	}

	public static function share(\Closure $callable)
	{
		return function ($c) use ($callable) {
			static $object;
			return $object ? : $object = $callable($c);
		};
	}

	public static function protect(\Closure $callable)
	{
		return function ($c) use ($callable) {
			return function () use ($c, $callable) {
				$callable($c);
			};
		};
	}
}