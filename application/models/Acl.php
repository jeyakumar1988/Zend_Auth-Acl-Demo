<?php
/**
 * @author Alex Lintott <alex.lintott@lintal.co.uk>
 * @category Demos
 * @package Zend_Auth/Zend_Acl demo
 * @subpackage Default_Model_Acl
 * @copyright Alex Lintott - Mar 19, 2011 
 */
class Default_Model_Acl extends Zend_Acl 
{
	/**
	 * Roles-Config: Identifier for role node.
	 */
	const ROLES_CONFIG_ROLE_IDENTIFIER = 'role';
	
	/**
	 * Roles-Config: Identifier for role's name node.
	 */
	const ROLES_CONFIG_ROLENAME_IDENTIFIER = 'name';
	
	/**
	 * Roles-Config: Identifier for base-level role node.
	 */
	const ROLES_CONFIG_BASEROLE_IDENTIFIER = 'baseRoles';
	
	/**
	 * Roles-Config: Identifier for child roles node.
	 */
	const ROLES_CONFIG_CHILDROLES_IDENTIFIER = 'roles';
	
	/**
	 * Roles-Config: Identifier for allow-all permissions flag node.
	 */
	const ROLES_CONFIG_ALLOWALL_IDENTIFIER = 'allowAll';
	
	protected function __construct() {}
	protected function __clone() {}
	
	/**
	 * Singleton instance of class.
	 * @var Default_Model_Acl
	 */
	protected static $instance;
	
	/**
	 * Singleton access function to ACL class.
	 * 
	 * @return Default_Model_Acl
	 */
	public static function getInstance() 
	{
		if (is_null(self::$instance))
			self::$instance = new self();
			
		return self::$instance;
	}
	
	/**
	 * Function overrides all roles and adds from either a
	 * Zend_Config object, or an appropriately formed array.
	 * 
	 * @uses Default_Model_Acl::addRoles()
	 * @param Zend_Config|array $roles
	 * @return Default_Model_Acl
	 */
	public function setRoles($roles) 
	{
		$this->removeRoleAll();
		$this->addRoles($roles);
		
		return $this;
	}
	
	/**
	 * Function adds roles from either a Zend_Config object,
	 * or an appropriately formed array.
	 * 
	 * @param Zend_Config|array $roles
	 * @return Default_Model_Acl
	 * @throws Zend_Acl_Exception
	 */
	public function addRoles($roles) 
	{
		if ($roles instanceof Zend_Config) 
			$roles = $roles->toArray();
		
		if (!is_array($roles)) 
			throw new Zend_Acl_Exception("An unknown list of roles was provided.");
		
		$this->processRolesArray($roles);
		
		//If base-roles are provided, set-up roles and allow all existing roles to inherit.
		if (key_exists(self::ROLES_CONFIG_BASEROLE_IDENTIFIER, $roles)) 
			$this->processRolesArray($roles[self::ROLES_CONFIG_BASEROLE_IDENTIFIER], $this->getRoles());
								 	
		return $this;
	}
	
	/**
	 * Function processes a roles configuration array and adds
	 * it to the ACL object.
	 * 
	 * @param array $roles
	 * @param Zend_Acl_Role_Interface|array|string|null $parent
	 * @return void
	 */
	protected function processRolesArray(array $roles, $parent = null) 
	{
		$roles = $roles[self::ROLES_CONFIG_ROLE_IDENTIFIER];
		if (Default_Model_Array_Utils::isAssoc($roles)) 
			$roles = array($roles);
		
		foreach($roles as $role) 
		{
			$roleName = $role[self::ROLES_CONFIG_ROLENAME_IDENTIFIER];

			$this->addRole($roleName, $parent); 
			
			if (key_exists(self::ROLES_CONFIG_ALLOWALL_IDENTIFIER, $role) &&
				1 == intval($role[self::ROLES_CONFIG_ALLOWALL_IDENTIFIER]))
				$this->allow($roleName);
			
			if (key_exists(self::ROLES_CONFIG_CHILDROLES_IDENTIFIER, $role))
				$this->processRolesArray($role[self::ROLES_CONFIG_CHILDROLES_IDENTIFIER], 
										 $roleName);
		}
	}
	
	/**
	 * Function overrides all resources and adds from either a
	 * Zend_Config object, or an appropriately formed array.
	 * 
	 * @uses Default_Model_Acl::addResources()
	 * @param Zend_Config|array $resources
	 * @return Default_Model_Acl
	 */
	public function setResources($resources)
	{
		$this->removeAll();
		$this->addResources($resources);
		
		return $this;
	}
	
	/**
	 * Function adds resources from either a Zend_Config object,
	 * or an appropriately formed array.
	 * 
	 * @param Zend_Config|array $resources
	 * @return Default_Model_Acl
	 * @throws Zend_Acl_Exception
	 */
	public function addResources($resources)
	{
		if ($resources instanceof Zend_Config) 
			$resources = $resources->toArray();
		
		if (!is_array($resources)) 
			throw new Zend_Acl_Exception("An unknown list of resources was provided.");
			
		$this->processResourcesArray($resources);
		
		return $this;
	}
	
	/**
	 * Function processes a resources configuration array and adds
	 * it to the ACL object.
	 * 
	 * @param array $resources
	 * @param Zend_Acl_Resource_Interface|array|string|null $parent
	 * @return void
	 * @throws Zend_Acl_Exception
	 */
	protected function processResourcesArray(array $resources, $parent = null) 
	{
		$resources = $resources['resource'];
		if (Default_Model_Array_Utils::isAssoc($resources)) 
			$resources = array($resources);
			
		foreach ($resources as $resource)
		{
			if (key_exists('name', $resource))
			{
				if ($parent instanceof Default_Model_Acl_PageResource)
					throw new Zend_Acl_Exception("A custom resource cannot inherit from a page-resource.");
					
				$this->addResource($resource['name'], $parent);
				$parentResource = $resource['name'];
				
			} else if (Default_Model_Array_Utils::keysExist($resource, 
													array('module', 'controller','action'),
													Default_Model_Array_Utils::MATCH_PARTIAL)) {
				
				$module = $this->getModule($resource, $parent);
				$controller = $this->getController($resource, $parent);
									
				$action = $this->getAction($resource, $parent);
				
				$parentResource = new Default_Model_Acl_PageResource($module, $controller, $action);
				$this->addResource($parentResource, $parent);
				
			} else 
				throw new Zend_Acl_Exception("An unknown resource-type was specified.");
				
			if (key_exists('resources', $resource))
				$this->processResourcesArray($resource['resources'], $parentResource);
		}
	}
	
	/**
	 * Function validates and returns the correct module identifier
	 * for a page-resource.
	 * 
	 * @param array $resource
	 * @param Default_Model_Acl_PageResource|null $parent
	 * @return string
	 * @throws Zend_Acl_Exception
	 */
	private function getModule(array $resource, Default_Model_Acl_PageResource $parent = null)
	{
		if (key_exists('module', $resource))
		{
			if (null !== $parent)
			{
				if ($resource['module'] !== $parent->getModule())
					throw new Zend_Acl_Exception("The same module must be used when inheriting from a parent page-resource.");
			}
			
			return $resource['module'];
		} else if (null !== $parent)
			return $parent->getModule();
		
		else
			return 'default';
	}
	
	/**
	 * Function validates and returns the correct controller identifier
	 * for a page-resource.
	 * 
	 * @param array $resource
	 * @param Default_Model_Acl_PageResource|null $parent
	 * @return string|null
	 * @throws Zend_Acl_Exception
	 */
	private function getController(array $resource, Default_Model_Acl_PageResource $parent = null)
	{
		if (key_exists('controller', $resource))
		{
			if (null !== $parent)
			{
				$parentController = $parent->getController();
				if (!empty($parentController) && $resource['controller'] !== $parentController)
					throw new Zend_Acl_Exception("The same controller must be used when inheriting from a parent page-resource with specified controller.");
			}
			return $resource['controller'];
		}
		else if (null !== $parent)
		{
			$parentController = $parent->getController();
			if (!empty($parentController))
				return $parentController;
		}
		
		return null;
	}
	
	/**
	 * Function validates and returns the correct action identifier
	 * for a page-resource.
	 * 
	 * @param array $resource
	 * @param Default_Model_Acl_PageResource|null $parent
	 * @return string|null
	 * @throws Zend_Acl_Exception
	 */
	private function getAction(array $resource, Default_Model_Acl_PageResource $parent = null)
	{
		if (key_exists('action', $resource))
		{
			$parentController = null;
			if (null !== $parent)
				$parentController = $parent->getController();
				
			if (empty($parentController) && !key_exists('controller', $resource))
				throw new Zend_Acl_Exception("You must specify a controller when creating a resource for a controller-action.");
				
			$parentAction = $parent->getAction();
			if (!empty($parentAction))
				throw new Zend_Acl_Exception("You cannot create a controller-action resource extending from a page-resource with an action already specified.");
				
			return $resource['action'];
		}
		return null;
	}
}