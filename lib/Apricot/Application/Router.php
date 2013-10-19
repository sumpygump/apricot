<?php
/**
 * Application Router class file
 *
 * @package Apricot
 */

namespace Apricot\Application;

use Apricot\Config;
use Apricot\Request;

/**
 * Application Router
 *
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class Router
{
    /**
     * Config object
     *
     * @var \Apricot\Config
     */
    protected $_config;

    /**
     * Request object
     *
     * @var \Apricot\Request
     */
    protected $_request;

    /**
     * Constructor
     *
     * @param Config $config Config object
     * @return void
     */
    public function __construct(Config $config)
    {
        $this->_config = $config;
    }

    /**
     * Set request
     *
     * @param Request $request Request object
     * @return Apricot\Application\Router
     */
    public function setRequest(Request $request)
    {
        $this->_request = $request;
    }

    /**
     * Parse the request uri
     *
     * @return object self
     */
    public function parseRequestUri()
    {
        $config = $this->_config;

        $requestUri = $this->getEnvironmentParam('request_uri');

        if ($config->get('www_root') != '/') {
            $wwwRoot    = $config->get('www_root');
            $requestUri = str_replace($wwwRoot, '', $requestUri);
        } else {
            if (substr($requestUri, 0, 1) == '/') {
                $requestUri = substr($requestUri, 1);
            }
        }

        $this->_rawRequestUri = $requestUri;

        $request_split = explode("?", $requestUri);

        $controller = $config->get('default_controller');
        $action     = null;

        if (count($request_split)) {
            $ca         = explode("/", $request_split[0]);
            $controller = $ca[0] ? $ca[0] : $config->get('default_controller');
            $action     = isset($ca[1]) ? $ca[1] : null;
        }

        // Fix the problem if the controller mapped to index.php and
        //  index is not the default controller
        if ($controller == 'index.php'
            && $config->get('default_controller') != 'index'
        ) {
            $controller = $config->get('default_controller');
        }

        // If it turned out to be a php script - convert to controller name
        $controller = str_replace(".php", '', $controller);

        $this->controller = $controller;

        if ($action) {
            $this->action = $action;
        }

        return $this;
    }

    /**
     * Generate an internal url
     *
     * @param string|array $input The parameters to map to a url
     * @param array $default_params The default parameters to use
     * @param bool $reset Whether to reset the vars or merge with current set
     * @param boolean $xhtml Whether the ampersands should be xhtml compatible
     * @return string The url
     */
    public function generateUrl($input, $default_params=array(),
        $reset=false, $xhtml=false)
    {
        $config = $this->_config;

        $_default_params = array_merge(
            array(
                'controller' => $config->get('default_controller'),
                'action'     => 'index',
            ),
            $default_params
        );

        if ($reset || (null === $this->_request)) {
            $default_params = $_default_params;
        } else {
            $default_params = array_merge($_default_params, $this->_request->toArray());
        }

        if ($default_params['action'] == '') {
            $default_params['action'] = $_default_params['action'];
        }

        if ($xhtml) {
            $qs_concat_char = "&amp;";
        } else {
            $qs_concat_char = "&";
        }

        // Convert params to an array if input is string
        if (!is_array($input)) {
            $url_params = array();

            // loop through entries (comma delimited)
            $parts = explode(",", $input);
            foreach ($parts as $part) {
                if (strpos($part, "=")) {
                    $kv = explode("=", $part);

                    $url_params[$kv[0]] = $kv[1];
                } else {
                    // assume action if just a plain entry
                    //$url_params['action'] = $part;
                    // assume controller if just a plain entry
                    $url_params['controller'] = $part;
                }
            }
        } else {
            $url_params = $input;
        }

        $url_params = array_merge($default_params, $url_params);

        if ($config->get('use_pretty_urls')) {
            $url_file = $config->get('www_root');
        } else {
            $url_file = $config->get('www_root') . 'index.php';
        }
        $url_qs      = array();
        $new_request = array();
        $anchor      = '';

        if ($config->get('use_pretty_urls')) {
            // Process the controller parameter
            $defaultController = $config->get('default_controller');
            if ($url_params['controller'] != $defaultController) {
                $new_request['controller'] =
                    urlencode($url_params['controller']);
            }
            unset($url_params['controller']);

            // Process the action parameter
            if ($url_params['action'] != ''
                && $url_params['action'] != 'index'
            ) {
                $new_request['action'] = urlencode($url_params['action']);
            }
            unset($url_params['action']);

            if (isset($url_params['_anchor'])) {
                $anchor = $url_params['_anchor'];
            }
            unset($url_params['_anchor']);
        }

        // Add additional parameters
        foreach ($url_params as $key => $value) {
            $url_qs[] = $key . "=" . urlencode($value);
        }

        // Return the normalized url
        if (count($url_qs) || count($new_request)) {
            if ($config->get('use_pretty_urls')) {
                if (isset($new_request['controller'])) {
                    $url_file .= $new_request['controller'];
                }
                if (isset($new_request['action'])) {
                    // if there is an action but no controller,
                    // add the default controller
                    if (!isset($new_request['controller'])) {
                        $url_file .= $config->get('default_controller');
                    }
                    $url_file .= "/" . $new_request['action'];
                }
            }

            if (count($url_qs)) {
                $final_url = $url_file
                    . "?" . implode($qs_concat_char, $url_qs);
            } else {
                $final_url = $url_file . "/";
            }

            if ($anchor) {
                $final_url .= "#" . $anchor;
            }

            return $final_url;
        } else {
            return $config->get('www_root');
        }
    }
}
