<?php
/**
 * Created by PhpStorm.
 * User: nick
 * Date: 13.08.15
 * Time: 10:15
 */

class LandingController extends Zend_Controller_Action {
    public function indexAction(){
        $this->_helper->layout()->disableLayout();

    }
}