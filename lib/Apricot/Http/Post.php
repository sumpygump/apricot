<?php
/**
 * Apricot Post class file
 *
 * @package Apricot
 * @version $Id$
 */

namespace Apricot\Http;

use Apricot\Params;

/**
 * Apricot Post
 * 
 * @uses Apricot_Params
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class Post extends Params
{
    /**
     * Constructor
     * 
     * @param array $data Data to import
     * @param array $options Options
     * @return void
     */
    public function __construct($data = null, $options = array())
    {
        if (null === $data) {
            $this->populate($_POST);
        }

        parent::__construct($data, $options);
    }
}
