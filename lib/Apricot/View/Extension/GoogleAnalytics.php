<?php
/**
 * Google analytics view extension class file
 *
 * @package Apricot\Module
 */

namespace Apricot\View\Extension;

use \Apricot\View\Extension;

/**
 * This module handles outputing appropriate google analytics JS tags
 *
 * @uses Apricot_Module_Abstract
 * @package Apricot\Module
 * @author Jansen Price <jansen.price@gmail.com>
 * @version ApricotVersion: 1.3b
 */
class GoogleAnalytics extends Extension
{
    /**
     * Tracking code
     *
     * @var string
     */
    protected $_trackingCode = '';

    /**
     * Domain name (ga_domain_name)
     *
     * @var string
     */
    protected $_domainName = '';

    /**
     * Allow Linker (ga_allow_linker)
     *
     * @var bool
     */
    protected $_allowLinker = false;

    /**
     * Whether logger is enabled
     * 
     * @var bool
     */
    protected $_enabled = false;

    /**
     * Set enabled status
     * 
     * @param bool $value Value
     * @return void
     */
    public function setEnabled($value = true)
    {
        $this->_enabled = (bool) $value;
    }

    /**
     * Register this module
     *
     * @return void
     */
    public function register()
    {
        if (isset($this->_options->enabled)) {
            $enabled = $this->_options->enabled;
        } else {
            $enabled = false;
        }

        if ($enabled && $enabled !== "false") {
            $this->setEnabled(true);
        }

        $this->setGoogleTrackingCode($this->_options->trackingCode, (array) $this->_options);
    }

    /**
     * Register this module
     * 
     * @return void
     */
    public function preAssemble()
    {
        $this->init();
    }

    /**
     * Direct call
     * 
     * @param mixed $args Arguments
     * @return mixed
     */
    public function direct($args = null)
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'init'), $args);
    }

    /**
     * Special class to set the Google Analytics tracking code for this page
     *
     * @param string $code The Google Analytics tracking code
     * @param array $options Other options to set for Google Analytics
     * @return void
     */
    public function setGoogleTrackingCode($code, $options=array())
    {
        $this->_trackingCode = $code;

        // One domain with multiple subdomains
        if (isset($options['ga_domain_name']) && trim($options['ga_domain_name']) != '') {
            // should be set, for example, to ".domain.com"
            $this->_domainName = $options['ga_domain_name'];
        }

        // For multiple top-level domains
        if (isset($options['ga_allow_linker'])
            && trim($options['ga_allow_linker']) != ''
            && trim($options['ga_allow_linker']) != 'false'
        ) {
            // should be set to 'true' or 'false' or left blank
            $this->_allowLinker = (bool) $options['ga_allow_linker'];
        }
    }

    /**
     * Get tracking code
     *
     * @return void
     */
    public function getTrackingCode()
    {
        return $this->_trackingCode;
    }

    /**
     * Get Domain name
     *
     * @return void
     */
    public function getDomainName()
    {
        return $this->_domainName;
    }

    /**
     * Get allow linker
     *
     * @return void
     */
    public function getAllowLinker()
    {
        return $this->_allowLinker;
    }

    /**
     * Set the appropriate javascript to initialize Google Analytics
     * tracking
     *
     * This will only add the javascript if the code has been set
     * with set_google_tracking_code()
     *
     * @return void
     */
    public function init()
    {
        if (!$this->_enabled) {
            return false;
        }

        if (null == $this->_trackingCode) {
            return false;
        }

        // google analytics
        $this->getView()->setJs(
            'var gaJsHost = (("https:" == document.location.protocol) '
            . '? "https://ssl." : "http://www.");' . "\n"
            . '    document.write(unescape("%3Cscript src=\'" + gaJsHost '
            . '+ "google-analytics.com/ga.js\' '
            . 'type=\'text/javascript\'%3E%3C/script%3E"));',
            'body'
        );

        $js = 'try {' . "\n"
            .'    var pageTracker = _gat._getTracker("'
            . $this->_trackingCode
            . '");';

        if (null !== $this->_domainName) {
            $js .= 'pageTracker._setDomainName("'
                . $this->_domainName
                . '");';
        }

        if ($this->_allowLinker) {
            $js .= 'pageTracker._setAllowLinker(true);';
        }

        $js .= 'pageTracker._trackPageview();' . "\n"
            .'} catch(err) {}';

        $this->getView()->setJs($js, 'body');
    }
}
