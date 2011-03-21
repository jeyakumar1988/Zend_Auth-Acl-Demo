<?php
/**
 * Bootstrap class for application.
 * 
 * @author Alex Lintott <alex.lintott@lintal.co.uk>
 * @category Demos
 * @package Zend_Auth/Zend_Acl demo
 */ 
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
	/**
	 * Initialisation of Document Type.
	 * 
	 * @return void
	 */
	protected function _initDoctype() 
	{
		$this->bootstrap('view');
		$view = $this->getResource('view');
		$view->doctype('XHTML1_STRICT'); 
	}
	
	/**
	 * Initialisation of autoloader for default modules and
	 * custom namespaces.
	 * 
	 * @return Zend_Application_Module_Autoloader
	 */
	protected function _initAutoload() 
	{
		$autoloader = new Zend_Application_Module_Autoloader(
			array(
				'namespace'	=>	'Default_',
				'basePath'	=>	dirname(__FILE__),
			)
		);
		$autoloader->addResourceType('dbTable', 'models/dbtable/', 'Model_DbTable');
		return $autoloader;
	}
	
	/**
	 * Initialisation of default database connection
	 * 
	 * @return void
	 */
	protected function _initDb() 
	{
		Zend_Db_Table::setDefaultAdapter($this->getPluginResource('db')->getDbAdapter());
		Zend_Registry::set('db', $this->getPluginResource('db')->getDbAdapter());
	}
	
	/**
	 * Initialisation of localisation.
	 * 
	 * @return void
	 */
	protected function _initLocale() 
	{
		$locale = new Zend_Locale('en_GB');
		Zend_Registry::set('Zend_Locale', $locale);
	}
	
	/**
	 * Initialisation of head-title for view-helper.
	 * 
	 * @return Zend_View
	 */
	protected function _initHeadTitle() 
	{
		$view = $this->getResource('view');
		$view->headTitle()->prepend('ZF ACL/Auth Demo by Lintal');
		$view->headTitle()->setSeparator(' - ');
		
		return $view;
	}
	
	/**
	 * Initialisation of the ACL singleton class with injected dependencies.
	 * 
	 * @return void
	 */
	protected function _initAcl() 
	{	
		$roleConfigPath = APPLICATION_PATH . '/configs/acl/roles.xml';
		$resourceConfigPath = APPLICATION_PATH . '/configs/acl/resources.xml';
		
		$acl = Default_Model_Acl::getInstance();
		$acl->setRoles(new Zend_Config_Xml($roleConfigPath, 
								Default_Model_Acl::ROLES_CONFIG_CHILDROLES_IDENTIFIER))
			->setResources(new Zend_Config_Xml($resourceConfigPath, 
								Default_Model_Acl::RESOURCES_CONFIG_CHILDRESOURCES_IDENTIFIER));
								
//		$this->registerPluginResource(new Default_Model_Acl_Plugin());
	}
}
?>