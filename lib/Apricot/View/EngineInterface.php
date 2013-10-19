<?php
/**
 * Apricot_View_EngineInterface file
 *
 * @package Apricot\View
 */

namespace Apricot\View;

/**
 * Interface for Apricot_View Engines
 *
 * @package Apricot\View
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
interface EngineInterface
{
    /**
     * Set option
     * 
     * @param string $name Option name
     * @param mixed $value Value of option
     * @return void
     */
    public function setOption($name, $value);

    /**
     * Get option
     * 
     * @param string $name Option name
     * @return mixed
     */
    public function getOption($name);

    /**
     * Set the body content
     * 
     * @param string $content Content
     * @return void
     */
    public function setBody($content);

    /**
     * Display
     *
     * TODO: Change this API to render() instead
     * 
     * @param bool $output Whether to return or echo
     * @return void | string
     */
    public function display($output = false);
}
