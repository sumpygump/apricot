<?php
/**
 * Apricot Request Http class file
 *  
 * @package Apricot
 * @version $Id$
 */

namespace Apricot\Http;

use Apricot\Params;

/**
 * Apricot Request for Http context
 * 
 * @uses Apricot_Request
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class Request extends \Apricot\Request
{
    /**
     * Storage for headers
     * 
     * @var mixed
     */
    protected $_headers = null;

    /**
     * Cookies
     *
     * @var mixed
     */
    protected $_cookies = null;

    /**
     * Request The storage for the _POST Request object
     *
     * @var object
     */
    protected $_post = null;

    /**
     * Storage for the raw request_uri
     *
     * @var string
     */
    protected $_rawRequestUri = '';

    /**
     * Web root
     *
     * @var string
     */
    protected $_wwwRoot = '';

    /**
     * Default controller to be used by requests
     *
     * @var string
     */
    protected $_defaultController = 'index';

    /**
     * Constructor
     *
     * @param mixed $params
     * @param mixed $config
     * @return void
     */
    public function __construct($params = null, $config = null)
    {
        parent::__construct($params, $config);

        $this->_rawRequestUri = $this->getEnvironmentParam('request_uri');

        $this->_headers = new Headers();
        $this->_cookies = new Params($_COOKIE);

        if ($this->_config->get('www_root') == '') {
            $this->_config->setDefault('www_root', $this->_detectWwwRoot());
        }

        if (null == $params) {
            $this->_autoPopulate = true;

            $this->populate($_GET);
            $this->parseRequestUri();
        }

        if ($this->isPost()) {
            $this->_post = new Post();
        }

        $this->_config->set('use_pretty_urls', true);
    }

    /**
     * Refresh the request
     * 
     * @return void
     */
    public function refresh()
    {
        $this->parseRequestUri();
    }

    /**
     * Get a headers param
     * 
     * @param string $param Name of param to get
     * @return mixed
     */
    public function getHeaderParam($param)
    {
        $param = strtoupper(trim($param));
        return $this->_headers->get($param);
    }

    /**
     * Get a cookie by name
     *
     * @param string $param Cookie name
     * @param string $default Default value
     * @return string
     */
    public function getCookieParam($param, $default = '')
    {
        return $this->_cookies->get($param, $default);
    }

    /**
     * Get post
     * 
     * @return mixed
     */
    public function getPost()
    {
        if (null === $this->_post) {
            $this->_post = new Post();
        }

        return $this->_post;
    }

    /**
     * Return whether this request is a POST
     *
     * @return bool
     */
    public function isPost()
    {
        return ($this->getEnvironmentParam('request_method') == 'POST');
    }

    /**
     * Return whether this request is an XMLHttpRequest
     *
     * @return bool
     */
    public function isXhr()
    {
        $xhr = $this->_headers->get('X_REQUESTED_WITH');
        if ($xhr == 'XMLHttpRequest') {
            return true;
        }
        return false;
    }

    /**
     * Get the raw request from the web app root
     *
     * @return string
     */
    public function getRawRequestUri()
    {
        return $this->_rawRequestUri;
    }

    /**
     * Parse the request uri
     *
     * @return object self
     */
    public function parseRequestUri()
    {
        $config = $this->_config;

        $requestUri = trim($this->getRawRequestUri());

        if ($config->get('www_root') != '/') {
            $wwwRoot    = $config->get('www_root');
            $requestUri = str_replace($wwwRoot, '', $requestUri);
        }

        // Be sure to strip the first slash if the uri is more that just a 
        // slash
        if (strlen($requestUri) > 0 && substr($requestUri, 0, 1) == '/') {
            $requestUri = substr($requestUri, 1);
        }

        $request_split = explode("?", $requestUri);

        $controller = $config->get('default_controller');
        $action     = null;

        if (count($request_split) && $request_split[0] !== '') {
            $ca         = explode("/", $request_split[0]);
            $controller = isset($ca[0]) ? $ca[0] : $config->get('default_controller');
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
     * Detect the base url
     *
     * @return void
     */
    protected function _detectWwwRoot()
    {
        $baseUrl        = '';
        $filename       = $this->getEnvironment()->get('SCRIPT_FILENAME', '');
        $scriptName     = $this->getEnvironment()->get('SCRIPT_NAME');
        $phpSelf        = $this->getEnvironment()->get('PHP_SELF');
        $origScriptName = $this->getEnvironment()->get('ORIG_SCRIPT_NAME');

        if ($scriptName !== null && basename($scriptName) === $filename) {
            $baseUrl = $scriptName;
        } elseif ($phpSelf !== null && basename($phpSelf) === $filename) {
            $baseUrl = $phpSelf;
        } elseif ($origScriptName !== null && basename($origScriptName) === $filename) {
            // 1and1 shared hosting compatibility.
            $baseUrl = $origScriptName;
        } else {
            // Backtrack up the SCRIPT_FILENAME to find the portion
            // matching PHP_SELF.

            $baseUrl  = '/';
            $basename = basename($filename);
            if ($basename) {
                $path     = ($phpSelf ? trim($phpSelf, '/') : '');
                $baseUrl .= substr($path, 0, strpos($path, $basename)) . $basename;
            }
        }

        // Does the base URL have anything in common with the request URI?
        $requestUri = $this->getRawRequestUri();

        // Full base URL matches.
        if (0 === strpos($requestUri, $baseUrl)) {
            return $baseUrl;
        }

        // Directory portion of base path matches.
        $baseDir = str_replace('\\', '/', dirname($baseUrl));
        if (0 === strpos($requestUri, $baseDir)) {
            return $baseDir;
        }

        $truncatedRequestUri = $requestUri;

        if (false !== ($pos = strpos($requestUri, '?'))) {
            $truncatedRequestUri = substr($requestUri, 0, $pos);
        }

        $basename = basename($baseUrl);

        // No match whatsoever
        if (empty($basename) || false === strpos($truncatedRequestUri, $basename)) {
            return '';
        }

        // If using mod_rewrite or ISAPI_Rewrite strip the script filename
        // out of the base path. $pos !== 0 makes sure it is not matching a
        // value from PATH_INFO or QUERY_STRING.
        if (strlen($requestUri) >= strlen($baseUrl)
            && (false !== ($pos = strpos($requestUri, $baseUrl)) && $pos !== 0)
        ) {
            $baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
        }

        return $baseUrl;
    }
}
