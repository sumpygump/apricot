<?php
/**
 * Simple view engine
 *
 * @package Apricot\View
 */

namespace Apricot\View;

/**
 * Simple View Engine
 *
 * @uses \Apricot\View\EngineInterface
 * @package Apricot\View
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class Simple implements EngineInterface
{
    /**
     * The body
     *
     * @var string
     */
    protected $_body = '';

    /**
     * Constructor
     *
     * @param array $options Options for construction
     * @return void
     */
    public function __construct($options = array())
    {
    }

    /**
     * Set option
     *
     * @param string $name Option name
     * @param mixed $value Value
     * @return void
     */
    public function setOption($name, $value)
    {
    }

    /**
     * Get option
     *
     * @param string $name Option name
     * @return mixed
     */
    public function getOption($name)
    {
        return '';
    }

    /**
     * Set body
     *
     * @param string $content Content
     * @return void
     */
    public function setBody($content)
    {
        $this->_body = $content;
    }

    /**
     * Display assembled page
     *
     * @param bool $output Whether to output or return
     * @return void
     */
    public function display($output = false)
    {
        echo $this->_body;
    }

    /**
     * Any methods that aren't implemented should just be ignored
     *
     * @param mixed $method
     * @param mixed $params
     * @return void
     */
    public function __call($method, $params)
    {
        return;
    }
}
