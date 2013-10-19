<?php
/**
 * Apricot Error Controller
 *
 * @package Apricot
 */

namespace Apricot\Controller;

use \Apricot\Controller;
use \Apricot\Exception\ExceptionHandlerHttp;

/**
 * This is a failsafe error controller
 *
 * @uses \Apricot\Controller
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class ErrorController extends Controller
{
    /**
     * Index action
     *
     * @return void
     */
    public function action__index()
    {
        $this->vbuf('<h2>An Error Occurred</h2>');
        $errorNumber = ExceptionHandlerHttp::logException(
            $this->_request->exception
        );

        if ($this->_kernel->getConfig('env') != 'production') {
            $html = '<h3>' . $this->_request->exception->getMessage() . '</h3>'
                . "<p>HTTP Status Code: " . $this->_request->httpcode . "</p>"
                . "<p>Error logged as number " . $errorNumber . "</p>"
                . ExceptionHandlerHttp::displayException(
                    $this->_request->exception
                );
            $this->vbuf($html);
        } else {
            $this->vbuf(
                "<p>Please report this error to an adminstrator. "
                . " Error number: " . $errorNumber . "</p>"
            );
        }
    }
}
