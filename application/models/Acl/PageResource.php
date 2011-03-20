<?php
/**
 * @author Alex Lintott <alex.lintott@lintal.co.uk>
 * @category Demos
 * @package Zend_Auth/Zend_Acl demo
 * @subpackage Default_Model_Acl_PageResource
 * @copyright Alex Lintott - Mar 20, 2011 
 */

/**
 * Class designed to encapsulate a page served in the module-controller-
 * action format as an ACL resource.
 */
class Default_Model_Acl_PageResource extends Zend_Acl_Resource {
	
	/**
	 * Variable stores the module name.
	 * 
	 * @var string
	 */
	protected $module;
	
	/**
	 * Variable stores the controller name.
	 * 
	 * @var string
	 */
	protected $controller;
	
	/**
	 * Variable stores the action name.
	 * 
	 * @var string
	 */
	protected $action;
	
	const RESOURCE_IDENTIFIER_MODULE = 'm';
	const RESOURCE_IDENTIFIER_CONTROLLER = 'c';
	const RESOURCE_IDENTIFIER_ACTION = 'a';
	
	/**
	 * Constructor function for page resource.
	 * 
	 * @param string $module
	 * @param string|null $controller
	 * @param string|null $action
	 */
	public function __construct($module, $controller = null, $action = null) 
	{
		if (0 == preg_match('/^m:/', $module)) {
			$this->setModule($module);
			
			if (!empty($controller)) 
				$this->setController($controller);
				
			if (!empty($action))
				$this->setAction($action);
		} else {
			$this->loadFromId($module);
		}
		
		parent::__construct($this->createResourceId());
	}
	
	/**
	 * Function loads module|controller|action parameters from formatted
	 * resource name.
	 * 
	 * @param string $resourceId
	 * @return void
	 * @throws Zend_Acl_Exception
	 */
	protected function loadFromId($resourceId) 
	{
		preg_match_all('/[acm]{1}:(.[a-z]+)/', $resourceId, $matches);
		foreach ($matches[1] as $key => $match) {
			switch (substr($matches[0][$key], 0, 1)) 
			{
				case self::RESOURCE_IDENTIFIER_MODULE:
					$this->setModule($match);
					break;
				case self::RESOURCE_IDENTIFIER_CONTROLLER:
					$this->setController($match);
					break;
				case self::RESOURCE_IDENTIFIER_ACTION:
					$this->setAction($match);
					break;
				default: 
					throw new Zend_Acl_Exception('An invalid string format was passed into the constructor. 
												  This class is intended for module-controller-action resources only.');
			}
		}
	}
	
	/**
	 * Function returns the name of the module associated
	 * with the resource.
	 * 
	 * @return string
	 */
	public function getModule() 
	{
		return $this->module;
	}
	
	/**
	 * Function sets the module name.
	 * 
	 * @param string $module
	 * @return void
	 */
	protected function setModule($module) 
	{
		$this->module = (string) $module;
	}
	
	/**
	 * Function returns the name of the controller associated
	 * with the resource if association has been made.
	 * 
	 * @return string|null
	 */
	public function getController()
	{
		return $this->controller;
	}
	
	/**
	 * Function sets the controller name.
	 * 
	 * @param string $controller
	 * @return void
	 */
	protected function setController($controller)
	{
		$this->controller = (string) $controller;
	}
	
	/**
	 * Function returns the name of the action associated
	 * with the resource if association has been made.
	 * 
	 * @return string|null
	 */
	public function getAction()
	{
		return $this->action;
	}
	/**
	 * Function sets the action name.
	 * 
	 * @param string $action
	 * @return void
	 */
	protected function setAction($action)
	{
		$this->action = (string) $action;
	}
	
	/**
	 * Function formulates the resource identifier
	 * required by the parent.
	 * 
	 * @see Zend_Acl_Resource::__construct()
	 * @return string
	 */
	protected function createResourceId() 
	{
		$resourceId = sprintf("%s:%s", self::RESOURCE_IDENTIFIER_MODULE,
									   $this->module);
		
		if (!empty($this->controller))
			$resourceId .= sprintf("|%s:%s", self::RESOURCE_IDENTIFIER_CONTROLLER,
											 $this->controller);
			
		if (!empty($this->action))
			$resourceId .= sprintf("|%s:%s", self::RESOURCE_IDENTIFIER_ACTION,
											 $this->action);
			
		return $resourceId;
	}
	
	/**
	 * Function returns the parent page-resource or null if no parent 
	 * exists.
	 * 
	 * @return Default_Model_Acl_PageResource|null
	 * @example If the page-resource is specified down to the action-level,
	 * 			a controller-level resource will be created and returned.
	 */
	public function getParent() 
	{ 
		if (!empty($this->action))
			return new self($this->module, $this->controller);
		
		else if (!empty($this->controller))
			return new self($this->module);
			
		return null;
	}
}