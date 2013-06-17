<?php


namespace BPModeration;

use BPModeration\Lib\SimpleContainer;
use BPModeration\Lib\TemplateEngine;
use BPModeration\Services\LinkGenerator;
use BPModeration\Services\Installer;
use BPModeration\Models\PluginOptions;

/**
 * Class BPModeration
 * @package BPModeration
 */
class BPModeration extends SimpleContainer
{
	/**
	 *
	 */
	const VERSION = '0.1.7';

	/**
	 * @var string
	 */
	public $pluginFile;

	/**
	 * @var string
	 */
	public $pluginDir;

	/**
	 * @var
	 */
	public $pluginBasename;

	/**
	 * @var
	 */
	public $pluginUrl;

	/**
	 * @var Models\PluginOptions
	 */
	public $options;

	/**
	 * stuff without dependencies
	 */
	public function __construct()
	{
		$this->pluginFile = BASE_FILE;
		$this->pluginDir = dirname(BASE_FILE);
		$this->pluginBasename = plugin_basename(dirname(BASE_FILE));
		$this->pluginUrl = plugin_url('', BASE_FILE);

		$this->options = new PluginOptions();

		$this->setupServices();

		$this->loadLocalization();

		register_activation_hook(BASE_FILE, $this['activation-routine']);
		register_deactivation_hook(BASE_FILE, $this['deactivation-routine']);

		do_action('bp_moderation_loaded', $this);

		add_action('init', array($this, 'init'));
	}

	/**
	 * setup main services
	 */
	private function setupServices()
	{
		$this->values['textdomain'] = 'bp-moderation';

		$bpmod = $this;
		$this->values['templating'] = self::share(function ($c) use ($bpmod) {
			$templating = new TemplateEngine(__DIR__ . DIRECTORY_SEPARATOR . 'views');
			$templating->setGlobal('bpmod', $bpmod);
		});

		$this->values['links'] = self::share(function ($c) {
			return new LinkGenerator($c);
		});

		$this->values['min-wp'] = '3.5';
		$this->values['min-bp'] = '1.7';
		$this->values['db-vers'] = -100;

		$this->values['installer'] = self::share(function ($c) {
			return new Installer($c);
		});
		$this->values['activation-routine'] = self::protect(function ($c) {
			return $c['installer']->activate();
		});
		$this->values['activation-routine'] = self::protect(function ($c) {
			return $c['installer']->deactivate();
		});

		$this->values['ajax-action'] = 'bpmod-ajax';
		$this->values['req-param'] = 'bpmod';
	}

	private function loadLocalization()
	{
		return load_plugin_textdomain($this->values['textdomain'])
		|| load_plugin_textdomain($this->values['textdomain'], false, $this->basename . '/lang');
	}

	/**
	 * Check dependencies and do stuff that need them
	 */
	public function init()
	{
		if (!$this->checkDependencies()) return;

		$this->checkUpgrades();

		$bp = buddypress();
		$bp->moderation = $this;

		do_action('bp_moderation_init', $this);

		$this->route();
	}


	/**
	 * check wp and bp versions
	 * add admin notices if not compatible
	 *
	 * @return bool dependencies are met
	 */
	private function checkDependencies()
	{
		$wpVersion = $GLOBALS['wp_version'];
		$bpVersion = function_exists('buddypress') ? buddypress()->version : 0;

		$errors = array();
		$base_msg = _('BP Moderation %s depends on %s');

		if (version_compare($wpVersion, $this['min-wp'], '<')) {
			sprintf($base_msg, self::VERSION, 'WordPress ' . $this->values['min-wp']);
		}
		if (!$bpVersion || version_compare($bpVersion, $this['min-bp'], '<')) {
			sprintf($base_msg, self::VERSION, 'BuddyPress ' . $this->values['min-bp']);
		}

		if (empty($errors)) {
			return true;
		} else {
			if (is_admin() && current_user_can('activate_plugins')) {
				add_action('admin_notices', function () use ($errors) {
					echo '<div class="error"><p>' . join('<br/>', $errors) . '</p></div>';
				});
			}
			return false;
		}
	}

	/**
	 * check if the installed version is the latest available or do installation
	 */
	private function checkUpgrades()
	{
		if (version_compare($this->options['db-vers'], $this->values['db-vers'], '<')) {
			$this['installer']->install();
		}
	}

	/**
	 *
	 */
	private function route()
	{

		if (!empty($_REQUEST[$this->values['req-param']['resource']]) &&
			'flag' == $_REQUEST[$this->values['req-param']['resource']]
		) {
			$this->runController('Flagging');
		} elseif (is_admin()) {
			$this->runController('Admin');
		}
	}

	private function runController($controller)
	{
		$class = __NAMESPACE__ . '\\Controllers\\' . $controller . 'Controller';
		$controller = new $class($this);
		$controller->run();
	}

	private function requestParam($action)
	{
		return defined('DOING_AJAX') && DOING_AJAX && $action === $_REQUEST['action'];
	}
}