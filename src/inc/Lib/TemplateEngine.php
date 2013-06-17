<?php


namespace BPModeration\Lib;


/**
 * Class TemplateEngine
 *
 * minimal php template engine inspired by Symfony\Component\Templating\PhpEngine
 */
class TemplateEngine
{
	private $viewsDir;

	private $current;

	private $parents;

	private $globals = array();

	private $viewFile;
	private $viewVars;

	public function __construct($viewsDir)
	{
		$this->viewsDir = $viewsDir;
	}

	public function setGlobal($name, $value)
	{
		$this->globals[$name] = $value;
	}

	public function render($view, $vars)
	{
		$this->_render($view, $vars);
	}

	private function _render($view, $vars, $slots = array())
	{
		$file = $this->locate($view);

		$this->current = $file;

		ob_start();

		$this->load($file, array_replace($this->globals, $vars, array('slots' => $slots)));

		if ($this->parents[$file]) {
			$slots['_content'] = ob_get_clean();
			$this->_render($this->parents[$file], $vars, $slots);
		} else {
			ob_end_flush();
		}
	}

	private function locate($view)
	{
		return $this->viewsDir . DIRECTORY_SEPARATOR . str_replace(':', DIRECTORY_SEPARATOR, $view);
	}

	private function load($file, $vars)
	{
		$this->viewFile = $file;
		$this->viewVars = $vars;
		unset($file, $vars);
		extract($this->viewVars);
		$view = $this;
		require $this->viewFile;
	}

	public function extend($view)
	{
		$this->parents[$this->current] = $view;
	}
}