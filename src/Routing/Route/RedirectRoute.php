<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP(tm) v 2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Cake\Routing\Route;

use Cake\Network\Response;
use Cake\Routing\Router;
use Cake\Routing\Route\Route;

/**
 * Redirect route will perform an immediate redirect. Redirect routes
 * are useful when you want to have Routing layer redirects occur in your
 * application, for when URLs move.
 *
 */
class RedirectRoute extends Route {

/**
 * A Response object
 *
 * @var Cake\Network\Response
 */
	public $response = null;

/**
 * The location to redirect to. Either a string or a CakePHP array URL.
 *
 * @var mixed
 */
	public $redirect;

/**
 * Flag for disabling exit() when this route parses a URL.
 *
 * @var boolean
 */
	public $stop = true;

/**
 * Constructor
 *
 * @param string $template Template string with parameter placeholders
 * @param array $defaults Array of defaults for the route.
 * @param array $options Array of additional options for the Route
 */
	public function __construct($template, $defaults = array(), $options = array()) {
		parent::__construct($template, $defaults, $options);
		$this->redirect = (array)$defaults;
	}

/**
 * Parses a string URL into an array. Parsed URLs will result in an automatic
 * redirection
 *
 * @param string $url The URL to parse
 * @return boolean False on failure
 */
	public function parse($url) {
		$params = parent::parse($url);
		if (!$params) {
			return false;
		}
		if (!$this->response) {
			$this->response = new Response();
		}
		$redirect = $this->redirect;
		if (count($this->redirect) === 1 && !isset($this->redirect['controller'])) {
			$redirect = $this->redirect[0];
		}
		if (isset($this->options['persist']) && is_array($redirect)) {
			$redirect += array('pass' => $params['pass'], 'url' => array());
			if (is_array($this->options['persist'])) {
				foreach ($this->options['persist'] as $elem) {
					if (isset($params[$elem])) {
						$redirect[$elem] = $params[$elem];
					}
				}
			}
			$redirect = Router::reverse($redirect);
		}
		$status = 301;
		if (isset($this->options['status']) && ($this->options['status'] >= 300 && $this->options['status'] < 400)) {
			$status = $this->options['status'];
		}
		$this->response->header(array(
			'Location' => Router::url($redirect, true)
		));
		$this->response->statusCode($status);
		$this->response->send();
		$this->_stop();
	}

/**
 * There is no reverse routing redirection routes
 *
 * @param array $url Array of parameters to convert to a string.
 * @param array $context Array of request context parameters.
 * @return mixed either false or a string url.
 */
	public function match($url, $context = array()) {
		return false;
	}

/**
 * Stop execution of the current script. Wraps exit() making
 * testing easier.
 *
 * @param integer|string $status see http://php.net/exit for values
 * @return void
 */
	protected function _stop($code = 0) {
		if ($this->stop) {
			exit($code);
		}
	}

}