<?php


namespace BPModeration\Services;


class LinkGenerator
{
	private $container;

	public function __construct($container)
	{
		$this->container = $container;
	}
}