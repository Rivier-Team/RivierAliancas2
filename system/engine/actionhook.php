<?php
/**
 * @package     OpenCart
 * @author      Daniel Kerr
 * @copyright   Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license     https://opensource.org/licenses/GPL-3.0
 * @link        https://www.opencart.com
 */

/**
 * Action class
 */
class ActionHook {
	private $id;
	private $route;
	private $method = 'index';

    /**
     * Constructor
     *
     * @param    string $route
     */
	public function __construct($route) {
		$this->id = $route;

		$parts = explode('/', preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route));

		// Break apart the route
		while ($parts) {
			$file = DIR_WEBHOOK . 'controller/' . implode('/', $parts) . '.php';

			if (is_file($file)) {
				$this->route = implode('/', $parts);

				break;
			} else {
				$this->method = array_pop($parts);
			}
		}
	}

    /**
     *
     *
     * @return    string
     *
     */
	public function getId() {
		return $this->id;
	}

    /**
     *
     *
     * @param    object $registry
     * @param    array $args
     */
	public function execute($registry, array $args = array()) {
		// Stop any magical methods being called
		if (substr($this->method, 0, 2) == '__') {
			return new \Exception('Erro: Métodos mágicos não são permitidos!');
		}

		$file = DIR_WEBHOOK . 'controller/' . $this->route . '.php';
		$class = 'ControllerWebHook' . preg_replace('/[^a-zA-Z0-9]/', '', $this->route);

		// Initialize the class
		if (is_file($file)) {
			include_once($file);

			$controller = new $class($registry);
		} else {
			return new \Exception('Erro: Não foi possível chamar ' . $this->route . '/' . $this->method . '!');
		}

		$reflection = new ReflectionClass($class);

		if ($reflection->hasMethod($this->method) && $reflection->getMethod($this->method)->getNumberOfRequiredParameters() <= count($args)) {
			return call_user_func_array(array($controller, $this->method), $args);
		} else {
			return new \Exception('Erro: Não foi possível chamar ' . $this->route . '/' . $this->method . '!');
		}
	}
}
