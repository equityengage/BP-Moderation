<?php


namespace BPModeration\Lib;


abstract class Controller
{
	protected $container;

	public function __construct($container)
	{
		$this->container = $container;
	}

	abstract public function run();

	protected function render($view, $args)
	{
		$this->container['templating']->render($view, $args);
	}
}