<?php
/**
 * Error controller class file
 *
 * @package Apricot
 */

use \Apricot\Application\Controller;
use \Apricot\ExceptionHandler;

/**
 * Error controller class
 *
 * @uses Apricot_Controller
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version $Id$
 */
class ErrorController extends Controller
{
    protected static $_headings = array(
        '404' => '404 Not Found',
        '500' => 'Ooops! Something went wrong.',
    );

    /**
     * Initialize
     * 
     * @return void
     */
    public function init()
    {
    }

    /**
     * Index action
     *
     * @return void
     */
    public function action__index()
    {
        $heading = 'Ooops! Something went wrong.';

        if (isset(self::$_headings[$this->_request->httpcode])) {
            $heading = self::$_headings[$this->_request->httpcode];
        }

        if ($this->_request->exception) {
            $errorNumber = ExceptionHandler::logException($this->_request->exception);
        }

        if ($this->_kernel->getConfig('display_errors') == '1') {
            $shouldDisplayException = true;
            $shortMessage = '';
        } else {
            $shouldDisplayException = false;
            $shortMessage = "Please report this error to an adminstrator. Error number: $error_number";
        }

        $this->_view->heading = $heading;
        $this->_view->shouldDisplayException = $shouldDisplayException;
        $this->_view->exception = $this->_request->exception;
        $this->_view->errorNumber = $errorNumber;
        $this->_view->httpCode = $this->_request->httpcode;
        $this->_view->shortMessage = $shortMessage;
    }
}
