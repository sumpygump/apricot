<?php
/**
 * Apricot Application View Layout
 *
 * @package Apricot
 */

namespace Apricot\Application;

use Apricot\View;

/**
 * Application Application View Layout
 *
 * @uses Apricot\View
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class ViewLayout extends View
{
    /**
     * useLayout
     *
     * @var mixed
     */
    public $useLayout = true;

    /**
     * The layout template name
     * 
     * @var string
     */
    protected $_layout = 'layout.phtml';

    /**
     * Initialize view object
     *
     * @return void
     */
    public function init()
    {
        $kernel = $this->_kernel;
        // TODO: Use www_root from the request instead of from the config
        if ($kernel->getConfig('www_root')) {
            $this->setBaseUrl($kernel->getConfig('www_root'));
            $this->setBase($this->_getDomain(false) . $this->getBaseUrl('/'));
        }
    }

    /**
     * Pre assemble the view
     *
     * @return void
     */
    public function preAssemble()
    {
        //$this->setFavicon($this->getBaseUrl('favicon.ico'));
    }

    /**
     * Create the page layout
     *
     * @param string $content The body content
     * @return void
     */
    public function assemble($content)
    {
        $this->content = $content;

        if ($this->useLayout) {
            $layoutFile = $this->_layout;

            // FIXME: Render partial operates within the view templates 
            // directory so checking if the file exists here will never work. 
            // Need to find out a way to have the fallback functionality of 
            // getting the content from view engine in cases where no layout is 
            // desired.
            $layout = $this->renderPartial($layoutFile);

            if (!$layout) {
                // If nothing resulted from the layout, we need to include the 
                // content manually
                $layout = $this->content;
            }

            $this->_engine->setBody($layout);
        } else {
            $this->_engine->setBody($content);
        }
    }

    /**
     * Set layout template name
     * 
     * @param string $name Name or filename
     * @return self
     */
    public function setLayout($name)
    {
        $this->_layout = $name;

        if (strpos($name, '.phtml') === false) {
            $this->_layout .= '.phtml';
        }

        return $this;
    }

    /**
     * Get layout filename
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    /**
     * Escape characters
     *
     * @param mixed $text Text to escape
     * @return string
     */
    public function escape($text)
    {
        $out = str_replace('"', '&quot;', $text);
        return $out;
    }

    /**
     * Generate an internal url
     *
     * @param mixed $input The string or array of whither to redirect
     * @param bool $reset Whether to reset request parameters
     * @param bool $xhtml Flag to indicate output is xhtml
     * @return string
     */
    public function url($input = null, $reset = false, $xhtml = true)
    {
        if ($input == null) {
            return '';
        }

        $default_params = array(
            'action' => $this->_getDefaultAction()
        );

        return $this->_kernel->getRouter()
            ->generateUrl($input, $default_params, $reset, $xhtml);
    }

    /**
     * Get the domain name for the current site
     *
     * @param boolean $ending_slash Include the ending slash
     * @return string
     */
    protected function _getDomain($ending_slash = false)
    {
        $protocol = 'http';
        if (isset($_SERVER['SERVER_PROTOCOL'])) {
            if (strpos($_SERVER['SERVER_PROTOCOL'], 'HTTPS')) {
                $protocol = 'https';
            }
        }

        $host = "localhost";
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        }

        $out = $protocol . "://" . $host;
        if ($ending_slash) {
            $out .= "/";
        }

        return $out;
    }

    /**
     * Return a fully qualified url for the inputed url
     *
     * todo Add support for https
     *
     * @param string $url The url to fully qualify
     * @return string
     */
    protected function _fullyQualifyUrl($url)
    {
        if (substr($url, 0, 7) != 'http://') {
            $url = 'http://' . $url;
        }

        return $url;
    }
}
