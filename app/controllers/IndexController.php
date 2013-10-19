<?php
/**
 * Index Controller class file
 *  
 * @package Apricot
 */

namespace Apricot1_3\Controller;

use Apricot\Application\Controller;

/**
 * Index Controller class
 * 
 * @uses Apricot_Controller
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @version $Id$
 */
class IndexController extends \Apricot\Application\Controller
{
    /**
     * Initialize object
     * 
     * @return void
     */
    public function init()
    {
        //$users = $this->getModel('users');
    }

    /**
     * Index action
     * 
     * @return void
     */
    public function action__index()
    {
        $this->_view->setAuthor('Jansen Price');
        $this->_view->name = 'VNAME';
        return true;
    }
}
