<?php
/**
 * @author Alex Lintott <alex.lintott@lintal.co.uk>
 * @category Demos
 * @package Zend_Auth/Zend_Acl demo
 * @subpackage IndexController
 */
class IndexController extends Zend_Controller_Action {
	
	public function indexAction(){
		$this->view->headTitle('Home');
		
		$acl = Default_Model_Acl::getInstance();
		Zend_Debug::dump($acl);
	}
}

