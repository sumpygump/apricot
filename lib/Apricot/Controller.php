<?php
/**
 * Apricot Controller file
 * <pre>
 *     _               _           _
 *    / \   _ __  _ __(_) ___ ___ | |_
 *   / _ \ | '_ \| '__| |/ __/ _ \| __|
 *  / ___ \| |_) | |  | | (_| (_) | |_
 * /_/   \_\ .__/|_|  |_|\___\___/ \__|
 *         |_|
 * </pre>
 *
 * @package Apricot
 * @version $Id: Controller.php 1734 2010-03-16 02:49:55Z jansen $
 */

namespace Apricot;

/**
 * Apricot Controller
 *
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 * @abstract
 */
abstract class Controller
{
    /**
     * The Request object
     *
     * @var object
     * @see Request
     */
    protected $_request;

    /**
     * The Apricot Kernel object
     *
     * @var \Apricot\Kernel
     */
    protected $_kernel;

    /**
     * The Apricot View object
     *
     * @var \Apricot\View
     */
    protected $_view;

    /**
     * The default request vars to be set
     *
     * @var array
     */
    protected $_requestDefaults = array('action' => 'index');

    /**
     * The view buffer, for incrementally setting the page contents
     *
     * @var string
     */
    protected $_vbuf = '';

    /**
     * Whether to use the page cache
     * 
     * @var mixed
     */
    protected $_useCache = false;

    /**
     * Storage for cache object
     * 
     * @var mixed
     */
    protected $_cache = null;

    /**
     * Extension manager
     *
     * @var \Apricot\Controller\ExtensionManager
     */
    public $extension;

    /**
     * Constructor
     *
     * @param \Apricot\Kernel $kernel Apricot Kernel
     * @param \Apricot\Request $request The request object
     * @return void
     */
    public function __construct(Kernel $kernel, Request $request)
    {
        $this->_kernel = $kernel;

        if ($request->action == null) {
            // make sure we use the correct default action
            $request->action = $this->_getDefaultAction();
        }
        $this->_request = $request;

        $this->setUseCache($kernel->getConfig('use_cache'));

        $this->extension = $this->getKernel()->makeControllerExtensionManager($this);

        $this->init();
    }

    /**
     * Initialization (called right after constructor)
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Predispatch logic (called right before dispatching the action)
     * 
     * @return void
     */
    public function preDispatch()
    {
    }

    /**
     * Get Request object
     * 
     * @return object Apricot_Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Set view object
     * 
     * @param View $view The view object
     * @return void
     */
    public function setView(View $view)
    {
        $this->_view = $view;
    }

    /**
     * Execute this controller (process the request)
     *
     * @return mixed
     */
    public function execute()
    {
        $this->doCacheStart();

        $viewOptions = array_merge(
            $this->_requestDefaults,
            array(
                'default_title' => $this->_kernel->getConfig('default_title'),
            )
        );

        // Get the view object
        if (!is_object($this->_view)) {
            $this->setView($this->_kernel->makeView($viewOptions));
        }

        $this->_kernel->assignViewToExtensions($this->_view);

        // Execute logic for predispatchment
        $this->preDispatch();

        // Dispatch the controller->action
        $actionResult = $this->dispatchAction($this->_request->action);

        // Render view
        if (isset($this->_view->shouldRenderAction)
            && $this->_view->shouldRenderAction
        ) {
            $this->vbuf(
                $this->_view->renderPartial(
                    $this->_request->controller . DIRECTORY_SEPARATOR
                    . $this->_request->action . '.phtml'
                )
            );
        }

        // If action result returned false, bypass the preAssembler
        if ($actionResult || $actionResult === null) {
            $this->_view->preAssemble();
        }

        $this->_kernel->preAssembleViewModules();

        $this->_view->assemble($this->getVbuf());
        $this->_view->display();

        $this->doCacheEnd();

        // Just return the result from the action
        return $actionResult;
    }

    /**
     * Dispatch an action
     *
     * This calls the corresponding action method in the current object
     *
     * @param string $action The name of the action to dispatch
     * @return mixed
     */
    public function dispatchAction($action)
    {
        $_action = str_replace('-', '_', $action);
        $method  = 'action__' . $_action;

        $me = new \ReflectionClass(get_class($this));
        if (!$me->hasMethod($method)) {
            throw new ControllerException(
                "The action '$action' does not exist.",
                ControllerException::ERROR_ACTION_NOT_FOUND
            );
        }

        return $this->{$method}();
    }

    /**
     * Set use cache directive
     * 
     * @param mixed $value Value to set use cache to
     * @return void
     */
    public function setUseCache($value)
    {
        $this->_useCache = (bool) $value;
    }

    /**
     * Get use cache directive
     * 
     * @return bool
     */
    public function getUseCache()
    {
        return $this->_useCache;
    }

    /**
     * Process via the cache
     * 
     * @return void
     */
    public function doCacheStart()
    {
        if (!$this->_useCache) {
            return;
        }

        $this->_cache = $this->_kernel->makeCache();

        // clear cache?
        if ($this->_request->clearcache) {
            $this->_cache->clear();
        }

        $this->_cache->start($this->_request);
    }

    /**
     * Finish up the cache processing
     * 
     * @return void
     */
    public function doCacheEnd()
    {
        if ($this->_useCache && is_object($this->_cache)) {
            $this->_cache->end();
        }
    }

    /**
     * Add text to the view buffer
     *
     * @param string $content The content string to add to the buffer
     * @return void
     */
    protected function vbuf($content='')
    {
        $this->_vbuf .= $content;
    }

    /**
     * Get the view buffer
     * 
     * @return string
     */
    public function getVbuf()
    {
        return $this->_vbuf;
    }

    /**
     * Get the Apricot_Kernel object
     *
     * @return Apricot_Kernel
     */
    public function getKernel()
    {
        return $this->_kernel;
    }

    /**
     * Get session object
     * 
     * @return Qi_Sesspool
     */
    public function getSession()
    {
        return $this->_kernel->getSession();
    }

    /**
     * Get a model repository
     *
     * Calls the getRepository function on the Apricot_Kernel
     * Uses the default db_configuration or can pass in alternate
     * db configuration ($dbCfg)
     *
     * @param string $model The name of the model
     * @param array $dbCfg An alternate db configuration
     * @return \Apricot\Model\Repository
     */
    public function getRepository($model, $dbCfg = null)
    {
        return $this->_kernel->getRepository($model, $dbCfg);
    }

    /**
     * Redirect to a certain controller/action
     *
     * If the input starts with http, the internal url generator
     * is bypassed, and the browser is redirected to that url
     *
     * @param mixed $input The string or array with directive to redirect to
     * @param bool $reset Whether to merge request vars from the current request
     * @return void
     */
    public function redirect($input, $reset=false)
    {
        if (substr($input, 0, 4) == 'http') {
            $url = $input;
        } else {
            $url = $this->generateUrl($input, $reset, false);
        }

        // For debugging
        //file_put_contents('/tmp/a.txt',date('Y-m-d H:i:s') 
        //. " REQUEST_URL:  " .$this->_kernel->getServerParam('REQUEST_URI')
        //. "\n", FILE_APPEND);
        //file_put_contents('/tmp/a.txt',date('Y-m-d H:i:s') 
        //. " REDIRECT URL: " .$url. "\n", FILE_APPEND);

        if ($url
            && $url != $this->_request->getEnvironmentParam('REQUEST_URI')
        ) {
            if (!headers_sent()) {
                header("Location: " . $url);
            }
            $this->_kernel->exitApplication();
        }
    }

    /**
     * Generate an internal url
     *
     * @param mixed $input The string or array with directive to redirect to
     * @param bool $reset Whether to merge request vars from the current request
     * @param bool $xhtml Flag to indicate the output will be xhtml
     * @return void
     */
    public function generateUrl($input, $reset=false, $xhtml=false)
    {
        $default_params = array(
            'action' => $this->_getDefaultAction(),
        );

        return $this->_kernel->getRouter()->generateUrl(
            $input, $default_params, $reset, $xhtml
        );
    }

    /**
     * Get default action
     *
     * @return string
     */
    protected function _getDefaultAction()
    {
        if (isset($this->_requestDefaults['action'])) {
            return $this->_requestDefaults['action'];
        } else {
            return 'index';
        }
    }

    /**
     * Magic get method
     * 
     * @param mixed $val Parameter to retrieve
     * @return mixed
     */
    public function __get($val)
    {
        if ($val == 'view') {
            return $this->_view;
        }

        return false;
    }
}

/**
 * Apricot_ControllerException
 *
 * @uses Exception
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class ControllerException extends \Exception
{
    const ERROR_ACTION_NOT_FOUND = 64;
}
