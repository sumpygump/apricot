<?php
/**
 * Apricot View file
 *
 * <pre>
 *     _               _           _
 *    / \   _ __  _ __(_) ___ ___ | |_
 *   / _ \ | '_ \| '__| |/ __/ _ \| __|
 *  / ___ \| |_) | |  | | (_| (_) | |_
 * /_/   \_\ .__/|_|  |_|\___\___/ \__|
 *         |_|
 * </pre>
 *
 * @package Apricot\View
 * @version $Id$
 */

namespace Apricot\View;

/**
 * Apricot\View\HtmlDoc
 *
 * @uses \Apricot\View\EngineInterface
 * @package Apricot\View
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class Htmldoc implements EngineInterface
{
    /**#@+
     * Constants
     *
     * @var string
     */
    const HTML_HEAD     = 'head';     // The html head
    const HTML_BODY     = 'body';     // The html body
    const HTML_INLINE   = 'inline';   // Inline
    const HTML_DEFERRED = 'deferred'; // Load js file onload
    /**#@-*/

    /**
     * The body of the response that will be output
     *
     * @var string
     */
    protected $_body      = '';

    /**
     * The base URL for this web page
     *
     * @var string
     */
    protected $_baseurl   = '';

    /**
     * The base (HTML element) to use for this page
     *
     * @var string
     */
    protected $_base      = '';

    /**
     * The title of this web page
     *
     * @var string
     */
    protected $_title     = '';

    /**
     * Any extra head meta tags
     *
     * @var string
     */
    protected $_headExtra = '';

    /**
     * Storage for the meta tags for this web page
     *
     * @var array
     */
    protected $_meta    = array();

    /**
     * An array of the CSS entries for this web page
     *
     * @var array
     */
    protected $_css     = array();

    /**
     * An array of the Javascript entries for this web page
     *
     * @var array
     */
    protected $_js      = array();

    /**
     * An array of the head link tags for this web page
     *
     * @var array
     */
    protected $_links   = array();

    /**
     * An array of options applied to this object
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Constructor
     *
     * @param array $options Array of options
     * @return void
     */
    public function __construct($options = array())
    {
        $this->reset();

        $this->_parseOptions($options);

        $this->setTitle('');
        if ($this->getOption('autodate')) {
            $this->setDate();
        }

        $this->init();
    }

    /**
     * Reset object (set defaults)
     *
     * @return void
     */
    public function reset()
    {
        // Set defaults
        $this->_meta = array();

        $this->_css = array(
            self::HTML_HEAD => array(),
            self::HTML_BODY => array(),
        );

        $this->_js = array(
            self::HTML_HEAD     => array(),
            self::HTML_BODY     => array(),
            self::HTML_DEFERRED => array(),
        );

        $this->_headExtra = '';

        $this->_options = array(
            'autodate'      => true,
            'base'          => null,
            'bodyclass'     => null,
            'charset'       => 'utf-8', // also could be 'ISO-8859-1'
            'doctype'       => 'html5',
            'default_title' => '',
            'jsfilemask'    => '',
            'lang'          => 'en',
            'title'         => '',
            'xhtml'         => true,
        );
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
     * Stack titles
     *
     * Prepends values to a string that represents the title of the
     * webpage. Delimited by "|"
     *
     * @param string $title The title (section) to add
     * @param boolean $reset Flag to reset title (reset stacking)
     * @return void
     */
    public function setTitle($title = '', $reset = false)
    {
        if ($reset) {
            $this->_title = $title;
        } else {
            $defaultTitle = $this->getOption('default_title');
            if ($title == '' || $title == array()) {
                $this->_title = $defaultTitle;
            } else {
                if ($this->_title) {
                    $this->_title = $title . ' | ' . $this->_title;
                } else {
                    $this->_title = $title . ' | ' . $defaultTitle;
                }
            }
        }
    }

    /**
     * Get title
     *
     * @return void
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Parse the options string
     *
     * @param array $options Array of options
     * @return void
     */
    private function _parseOptions($options)
    {
        if ($options == null) {
            return;
        }

        foreach ($options as $key => $option) {
            switch($key) {
            case 'date':
                if ($option) {
                    $this->setDate($option);
                }
                break;
            case 'base':
                if ($option) {
                    $this->setBase($option);
                }
                break;
            case 'base_url':
                if ($option) {
                    $this->setBaseUrl($option);
                    $this->setBase($this->getBaseUrl());
                }
                break;
            default:
                $this->setOption($key, $option);
                break;
            }
        }
    }

    /**
     * Set the baseurl (from root)
     *
     * @param string $baseurl The baseurl
     * @return void
     */
    public function setBaseUrl($baseurl)
    {
        $this->_baseurl = $baseurl;
    }

    /**
     * Get the baseurl
     *
     * @param string $path Path to append to base url
     * @return string
     */
    public function getBaseUrl($path = '')
    {
        if ($path == '/') {
            $path = '';
        }

        return $this->_baseurl . $path;
    }

    /**
     * Set the base element
     *
     * If no param, the current site information will be used
     *
     * @param string $href The base url
     * @return void
     */
    public function setBase($href = null)
    {
        if (null === $href || $href === 'auto') {
            $scheme = 'http';
            if (isset($_SERVER['SERVER_PROTOCOL'])) {
                if (strpos($_SERVER['SERVER_PROTOCOL'], 'HTTPS') !== false) {
                    $scheme = 'https';
                }
            }
            $host = "";
            if (isset($_SERVER['HTTP_HOST'])) {
                $host = $_SERVER['HTTP_HOST'];
            }
            $href = $scheme . "://" . $host . "/";
        }
        $this->_base = $href;
    }

    /**
     * get_base
     *
     * @return string
     */
    public function getBase()
    {
        return $this->_base;
    }

    /**
     * Unset the base element, for cases where we want to remove the base.
     *
     * @return void
     */
    public function unsetBase()
    {
        $this->_base = '';
    }

    /**
     * Set an option
     *
     * @param mixed $key The name of the option
     * @param mixed $value The value of the option
     * @return void
     */
    public function setOption($key, $value)
    {
        $this->_options[$key] = $value;
    }

    /**
     * Get option
     *
     * @param string $name The name of the option
     * @param mixed $default Default value if name doesn't exist
     * @return mixed
     */
    public function getOption($name, $default=null)
    {
        if (isset($this->_options[$name])) {
            return $this->_options[$name];
        }
        return $default;
    }

    /**
     * Get the options array
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Set a list of meta elements with an array
     *
     * @param arrary $options Named value array (name=>content)
     * @return bool
     */
    public function setMetaInformation($options = array())
    {
        if (!is_array($options)) {
            return false;
        }

        foreach ($options as $name => $content) {
            switch ($name) {
            case 'keywords':
                $this->setKeywords($content);
                break;
            case 'date':
                $this->setDate($content);
                break;
            case 'description':
                $this->setDescription($content);
                break;
            case 'author':
            case 'copyright':
            default:
                $this->setMeta($name, $content);
                break;
            }
        }

        return true;
    }

    /**
     * Set the author of the document
     *
     * @param string $author The author name
     * @return void
     */
    public function setAuthor($author)
    {
        $this->setMeta('author', $author);
    }

    /**
     * Set the copyright text for the document
     *
     * @param string $copyrightText The copyright text
     * @return void
     */
    public function setCopyright($copyrightText)
    {
        $this->setMeta('copyright', $copyrightText);
    }

    /**
     * Set the date of the document
     *
     * Can be various formats, defaults to the current datetime
     *
     * @param string $date The date string
     * @return void
     */
    public function setDate($date = null)
    {
        if (null === $date) {
            //defaults to the current nanosecond
            $date = time();
        }

        if (!is_numeric($date)) {
            $date = strtotime($date);
        }

        $iso8601Date = date('c', $date);
        $this->setMeta('date', $iso8601Date);
    }

    /**
     * Set the keywords of the document
     *
     * $keywords can be an array or a comma separated list
     *
     * @param mixed $keywords The keywords
     * @return void
     */
    public function setKeywords($keywords)
    {
        if (is_array($keywords)) {
            $keywords_list = implode(',', $keywords);
        } else {
            $keywords_list = $keywords;
        }
        $this->setMeta('keywords', $keywords_list);
    }

    /**
     * Set description of the document
     *
     * @param string $description The description
     * @return void
     */
    public function setDescription($description)
    {
        $this->setMeta('description', strip_tags($description));
    }

    /**
     * Add to list of meta elements
     *
     * @param mixed $name The meta name to add
     * @param mixed $content The meta content to add
     * @return void
     */
    public function setMeta($name, $content)
    {
        // PHP 5.2.3
        if (version_compare(PHP_VERSION, '5.2.3') === 1) {
            $this->_meta[$name] = htmlentities($content, null, null, false);
        } else {
            // inferior support for this method -- possible breakage
            $this->_meta[$name] = htmlentities($content, null, null);
        }
    }

    /**
     * Set the rss feed alternate content link element
     *
     * @param string $type The content type string
     * @param string $title The title
     * @param string $href The url to the feed file
     * @return void
     */
    public function setFeed($type='application/rss+xml', $title='', $href='')
    {
        if (null == $type) {
            $type = 'application/rss+xml';
        }
        $this->setLink(
            'alternate',
            array(
                'type'  => $type,
                'title' => $title,
                'href'  => $href,
            )
        );
    }

    /**
     * Set the favicon link element
     *
     * @param string $href The url to the favicon file
     * @return void
     */
    public function setFavicon($href)
    {
        $this->setLink(
            'shortcut icon',
            array(
                'href' => $href,
                'type' => 'image/x-icon',
            )
        );
    }

    /**
     * Set a canonical link element
     *
     * @param mixed $href The canonical url
     * @return void
     */
    public function setCanonical($href)
    {
        $this->setLink('canonical', array('href'=>$href));
    }

    /**
     * Call method to handle creating head links
     *
     * each function should be of the form set_start($href, $title)
     *
     * @param string $name The name of the method to call
     * @param array $params Parameters to pass to the method
     * @method void setStart() setStart(href, title)
     * @method void setPrev() setPrev(href, title)
     * @method void setNext() setNext(href, title)
     * @method void setContents() setContents(href, title)
     * @method void setIndex() setIndex(href, title)
     * @method void setGlossary() setGlossary(href, title)
     * @method void setChapter() setChapter(href, title)
     * @method void setSection() setSection(href, title)
     * @method void setSubsection() setSubsection(href, title)
     * @method void setAppendix() setAppendix(href, title)
     * @method void setHelp() setHelp(href, title)
     * @method void setBookmark() setBookmark(href, title)
     * @return void
     */
    public function __call($name, $params)
    {
        switch($name) {
        case 'setStart':
        case 'setPrev':
        case 'setNext':
        case 'setContents':
        case 'setIndex':
        case 'setGlossary':
        case 'setChapter':
        case 'setSection':
        case 'setSubsection':
        case 'setAppendix':
        case 'setHelp':
        case 'setBookmark':
            $rel = strtolower(str_replace('set', '', $name));

            if (!isset($params[0])) {
                $params[0] = '';
            }

            if (!isset($params[1])) {
                $params[1] = '';
            }
            $this->setLink(
                $rel, array('href' => $params[0], 'title' => $params[1])
            );
            break;
        default:
            throw new \Apricot\View\HtmldocException("Call to invalid method '$name'");
            break;
        }
    }

    /**
     * Create a link element and add to the list of link elements
     *
     * @param string $rel The rel attribute
     * @param array $options A key-value array with attributes for the element
     * @return void
     */
    public function setLink($rel, $options = array())
    {
        $rel  = str_replace('"', '&quot;', $rel);
        $link = "<link rel=\"$rel\"";

        if (!is_array($options)) {
            $options = array('href' => $options);
        }

        foreach ($options as $key => $option) {
            if (!empty($option)) {
                $key    = preg_replace('/[^A-Za-z0-9_-]/', '', $key);
                $option = str_replace('"', '&quot;', $option);

                $link .= " $key=\"$option\"";
            }
        }
        $link .= " />";

        $this->_links[] = $link;
    }

    /**
     * Set a css file
     *
     * @param string $cssfilename The name of the css file
     * @param string $media The media type
     * @param string $title The title attribute
     * @param boolean $alternate Flag whether is alternate css file
     * @return void
     */
    public function setCssfile($cssfilename = '', $media = 'screen',
        $title = '', $alternate = false)
    {
        if ($title) {
            $titleText = " title=\"$title\"";
        } else {
            $titleText = "";
        }

        if ($alternate) {
            $styleAlt = "alternate ";
        } else {
            $styleAlt = "";
        }

        $this->_css[self::HTML_HEAD][] =
            "<link rel=\"" . $styleAlt . "stylesheet\" "
            . "href=\"$cssfilename\" "
            . "type=\"text/css\" "
            . "media=\"$media\"$titleText />";
    }

    /**
     * Set a conditional css file.
     *
     * Example conditions include
     * "IE 6", "IE 5", "IE 5.5000", "gte IE 6", "!(IE 6)", "!IE"
     *
     * @param string $condition The condition
     * @param string $cssfilename The name of the css file
     * @param string $media The media type
     * @return void
     */
    public function setConditionalCssfile($condition, $cssfilename='',
        $media='screen')
    {
        $this->_css[self::HTML_HEAD][] = "<!--[if $condition]>\n"
            . "<link rel=\"stylesheet\" type=\"text/css\" "
            . "href=\"$cssfilename\" media=\"$media\" />\n"
            . "<![endif]-->";
    }

    /**
     * Set some inline css.
     *
     * @param string $csstext String of css rules
     * @return void
     */
    public function setCss($csstext='')
    {
        $this->_css[self::HTML_INLINE][] = "\t" . $csstext;
    }

    /**
     * Set a filename to be included as a js file.(set as an config option).
     *
     * The jsfilemask will prepend to the .js extension.
     * Example: If jsfilemask was set to "min", and the filename was "file.js",
     * it would attempt to add "file.min.js"
     *
     * @param string $jsfilename The path+name of the file
     * @param string $placement Placement in the html document (head or body)
     * @param boolean $ignoreJsfilemask Flag to ignore the jsfilemask
     * @return void
     */
    public function setJsfile($jsfilename, $placement = self::HTML_HEAD,
        $ignoreJsfilemask = false)
    {
        $index      = $this->_getValidJsPlacement($placement);
        $jsfilemask = $this->getOption('jsfilemask');

        // modify filename based on jsfilemask
        if ($jsfilemask !== '' && !$ignoreJsfilemask) {
            if (strpos($jsfilename, $jsfilemask . ".") === false) {
                $jsfilename = str_replace(
                    ".js", $jsfilemask . ".js", $jsfilename
                );
            }
        }

        // Load into correct js bucket
        if ($index == self::HTML_DEFERRED) {
            $this->_js[$index][] = $jsfilename;
        } else {
            $this->_js[$index][] = "<script src=\"$jsfilename\" "
                . "type=\"text/javascript\"></script>";
        }
    }

    /**
     * Set some js text to be included in the html document.
     *
     * @param string $jstext A string of javascript
     * @param string $placement Placement in the html document (head or body)
     * @return void
     */
    public function setJs($jstext = '', $placement = self::HTML_HEAD)
    {
        $index = $this->_getValidJsPlacement($placement);

        $this->_js[$index][] = "<script type=\"text/javascript\">\n"
            . "<!--\n$jstext\n//-->\n</script>";
    }

    /**
     * Method to restrict placement to predefined constants
     *
     * @param string $placement Placement in the html document (head or body)
     * @return string The correct placement string
     */
    private function _getValidJsPlacement($placement)
    {
        switch ($placement) {
        case 'deferred':
        case self::HTML_DEFERRED:
            $index = self::HTML_DEFERRED;
            break;
        case 'body':
        case self::HTML_BODY:
            $index = self::HTML_BODY;
            break;
        case 'head':
        case self::HTML_HEAD:
        default:
            $index = self::HTML_HEAD;
            break;
        }
        return $index;
    }

    /**
     * Method to get a properly formatted inline js script element.
     *
     * @param string $jstext A string of javascript
     * @return string The js enclosed in html script tags
     */
    public function inlineJs($jstext)
    {
        return "\n<script type=\"text/javascript\">\n"
            . "<!--\n$jstext\n//-->\n</script>\n";
    }

    /**
     * Method to input raw html (custom) into the head.
     *
     * @param string $addl_text Raw html to be added to head element
     * @return void
     */
    public function setHeadExtra($addl_text='')
    {
        $this->_headExtra = $addl_text;
    }

    /**
     * Set the body of the document
     *
     * @param string $body String of html markup
     * @return void
     */
    public function setBody($body)
    {
        $this->_body = $body;
    }

    /**
     * Set the charset of the document
     *
     * @param string $charset The charset to use for the document
     * @return void
     */
    public function setCharset($charset="utf-8")
    {
        $this->setOption('charset', $charset);
    }

    /**
     * Set the language of the document
     *
     * @param string $lang The language
     * @return void
     */
    public function setLang($lang="en")
    {
        $this->setOption('lang', $lang);
    }

    /**
     * Display the rendered html document.
     *
     * @param bool $output Whether to return a string
     * @return mixed
     */
    public function display($output = false)
    {
        $htmlOut  = $this->_htmlStart();
        $htmlOut .= $this->_htmlHead();
        $htmlOut .= $this->_htmlBody();
        $htmlOut .= $this->_htmlEnd();

        if ($output) {
            return $htmlOut;
        } else {
            echo $htmlOut;
        }
    }

    /**
     * Create the start of the html document (doctype, html element)
     *
     * @return string
     */
    protected function _htmlStart()
    {
        $doctype = $this->getOption('doctype', 'html5');
        $xhtml   = $this->getOption('xhtml', true);

        switch ($doctype) {
        case 'strict':
            if ($xhtml) {
                $html_out = "<!DOCTYPE html PUBLIC "
                    . "\"-//W3C//DTD XHTML 1.0 Strict//EN\"\n"
                    . " \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">"
                    . "\n";
            } else {
                $html_out = "<!DOCTYPE HTML PUBLIC "
                    . "\"-//W3C//DTD HTML 4.01//EN\"\n"
                    . " \"http://www.w3.org/TR/html4/strict.dtd\">"
                    . "\n";
            }
            break;
        default:
            // html 5
            $html_out = "<!DOCTYPE html>\n";
            break;
        }

        if ($doctype == 'html5') {
            $html_out .= "<html>\n";
        } else {
            $html_out .= "<html xmlns=\"http://www.w3.org/1999/xhtml\" "
                . "lang=\"" . $this->getOption('lang') . "\">\n";
        }

        return $html_out;
    }

    /**
     * Create the head element of the document
     *
     * @return string
     */
    private function _htmlHead ()
    {
        $htmlOut  = "<head>\n";
        $htmlOut .= "<meta http-equiv=\"content-type\" content=\"text/html; "
            . "charset=" . $this->getOption('charset') . "\" />\n";
        if ($this->getBase()) {
            $htmlOut .= "<base href=\"" . $this->getBase() . "\" />\n";
        }
        $htmlOut .= "<title>" . $this->getTitle() . "</title>\n";
        $htmlOut .= $this->_renderMetaElements();
        $htmlOut .= $this->_renderCss();
        $htmlOut .= $this->_renderList($this->_links);
        $htmlOut .= $this->_renderList($this->_js[self::HTML_HEAD]);

        if ($this->_js[self::HTML_DEFERRED]) {
            $htmlOut .= $this->_renderJsDeferred(
                $this->_js[self::HTML_DEFERRED]
            );
        }

        $htmlOut .= $this->_headExtra;
        $htmlOut .= "</head>\n";

        return $htmlOut;
    }

    /**
     * Create the body element of the document
     *
     * @return string
     */
    private function _htmlBody ()
    {
        if ($this->getOption('bodyclass')) {
            $htmlOut = "<body class=\""
                . $this->getOption('bodyclass')
                . "\">\n";
        } else {
            $htmlOut = "<body>\n";
        }
        $htmlOut .= $this->_body."\n";

        if ($this->_js[self::HTML_BODY]) {
            $htmlOut .= $this->_renderList($this->_js[self::HTML_BODY]);
        }

        $htmlOut .= "</body>\n";

        return $htmlOut;
    }

    /**
     * Create the end of the document (close html element)
     *
     * @return void
     */
    private function _htmlEnd ()
    {
        $htmlOut = "</html>";

        return $htmlOut;
    }

    /**
     * Create the meta tags
     *
     * @return string
     */
    private function _renderMetaElements()
    {
        $out = '';
        ksort($this->_meta);

        foreach ($this->_meta as $name=>$content) {
            $out .= "<meta name=\"$name\" content=\"$content\" />\n";
        }
        return $out;
    }

    /**
     * Create the html for the css
     *
     * @return void
     */
    private function _renderCss()
    {
        $out = '';

        $out .= $this->_renderList($this->_css[self::HTML_HEAD]);

        if (isset($this->_css[self::HTML_INLINE])) {
            $out .= "<style type=\"text/css\">\n"
                . $this->_renderList($this->_css[self::HTML_INLINE])
                . "</style>\n";
        }
        return $out;
    }

    /**
     * Helper function to generate a list of items separated by \n
     *
     * @param array $array Array of items to be listed
     * @return string
     */
    private function _renderList($array)
    {
        $out = '';
        if ($array) {
            foreach ($array as $item) {
                $out .= $item . "\n";
            }
        }

        return $out;
    }

    /**
     * Generate html (js) to load js files onload
     *
     * @param mixed $array A list of filenames
     * @return string Html
     */
    private function _renderJsDeferred($array)
    {
        $out = '<script type="text/javascript">';

        // create js function to download js onload
        $out .= "function _dljsol(f)
    {"
            . "var e=document.createElement('script');"
            . "e.src=f;"
            . "document.body.appendChild(e);"
            . "}";

        // create function to load js
        $out .= "function _ljs()
    {";
        foreach ($array as $file) {
            $out .= "_dljsol(\"$file\");";
        }
        $out .= "}";

        // attach event listener for onload
        $out .= "if (window.addEventListener)"
            . "window.addEventListener(\"load\", _ljs, false);"
            . "else if (window.attachEvent)"
            . "window.attachEvent(\"onload\", _ljs);"
            . "else window.onload = _ljs;";

        $out .= "</script>\n";

        return $out;
    }
}

/**
 * Apricot\View\HtmldocException
 *
 * @uses Exception
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @version $Id$
 */
class HtmldocException extends \Exception
{
}
