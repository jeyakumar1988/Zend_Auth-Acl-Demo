<?php
class ErrorController extends Zend_Controller_Action {
	
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        
        $this->view->exception = $errors->exception;
        $this->view->request   = $errors->request;
    }


}

