<?php


namespace BPModeration\Controllers;


use BPModeration\Lib\Controller;

class AdminController extends Controller
{

	public function run()
	{
		if(!$this->checkPermissions()) {
			return;
		}
	}

	private function checkPermissions()
	{
		return is_super_admin();
	}
}