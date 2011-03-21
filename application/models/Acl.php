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
	
	/**
	 * Resources-Config: Identifier for resource node.
	 */
	const RESOURCES_CONFIG_RESOURCE_IDENTIFIER = 'resource';
	
	/**
	 * Resources-Config: Identifier for custom role name node.
	 */
	const RESOURCES_CONFIG_RESOURCE_CUSTOMNAME_IDENTIFIER = 'name';
	
	/**
	 * Resources-Config: Identifier for page-resource module node.
	 */
	const RESOURCES_CONFIG_RESOURCE_MODULE_IDENTIFIER = 'module';
	
	/**
	 * Resources-Config: Identifier for page-resource controller node.
	 */
	const RESOURCES_CONFIG_RESOURCE_CONTROLLER_IDENTIFIER = 'controller';
	
	/**
	 * Resources-Config: Identifier for page-resource action node.
	 */
	const RESOURCES_CONFIG_RESOURCE_ACTION_IDENTIFIER = 'action';
	
	/**
	 * Resources-Config: Identifier for child resources node.
	 */
	const RESOURCES_CONFIG_CHILDRESOURCES_IDENTIFIER = 'resources';
	
	/**
	 * Resources-Config: Identifier for permissions node.
	 */
	const RESOURCES_CONFIG_PERMISSIONS_IDENTIFIER = 'permissions';
	
	/**
	 * Permissions-Config: Identifier for permission setter type allowAll node.
	 */
	const PERMISSIONS_CONFIG_TYPE_ALLOWALL = 'allowAll';
	
	/**
	 * Permissions-Config: Identifier for perrmission setter type denyAll node.
	 */
	const PERMISSIONS_CONFIG_TYPE_DENYALL = 'denyAll';
	
	/**
	 * Permissions-Config: Identifier for permission setter type allow node.
	 */
	const PERMISSIONS_CONFIG_TYPE_ALLOW = 'allow';
	
	/**
	 * Permissions-Config: Identifier for permission setter type deny node.
	 */
	const PERMISSIONS_CONFIG_TYPE_DENY = 'deny';
	
	/**
	 * Permissions-Config: Identifier for permission setter type disableAll node.
	 */
	const PERMISSIONS_CONFIG_TYPE_DISABLEALL = 'disableAll';
	
	/**
	 * Permissions-Config: Identifier for permission setter type enableAll node.
	 */
	const PERMISSIONS_CONFIG_TYPE_ENABLEALL = 'enableAll';
	
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
		$resources = $resources[self::RESOURCES_CONFIG_RESOURCE_IDENTIFIER];
		if (Default_Model_Array_Utils::isAssoc($resources)) 
			$resources = array($resources);
			
		foreach ($resources as $resource)
		{
			if (key_exists(self::RESOURCES_CONFIG_RESOURCE_CUSTOMNAME_IDENTIFIER,
							$resource))
			{
				if ($parent instanceof Default_Model_Acl_PageResource)
					throw new Zend_Acl_Exception("A custom resource cannot inherit from a page-resource.");
					
				$this->addResource($resource[self::RESOURCES_CONFIG_RESOURCE_CUSTOMNAME_IDENTIFIER],
									$parent);
				$parentResource = $resource[self::RESOURCES_CONFIG_RESOURCE_CUSTOMNAME_IDENTIFIER];
				
			} else if (Default_Model_Array_Utils::keysExist($resource, 
													array(
														self::RESOURCES_CONFIG_RESOURCE_MODULE_IDENTIFIER,
														self::RESOURCES_CONFIG_RESOURCE_CONTROLLER_IDENTIFIER,
														self::RESOURCES_CONFIG_RESOURCE_ACTION_IDENTIFIER
													),
													Default_Model_Array_Utils::MATCH_PARTIAL)) {
				
				$module = $this->getModule($resource, $parent);
				$controller = $this->getController($resource, $parent);
									
				$action = $this->getAction($resource, $parent);
				
				$parentResource = new Default_Model_Acl_PageResource($module, $controller, $action);
				$this->addResource($parentResource, $parent);
				
			} else 
				throw new Zend_Acl_Exception("An unknown resource-type was specified.");
				
			if (key_exists(self::RESOURCES_CONFIG_PERMISSIONS_IDENTIFIER, $resource)) {
				$this->setResourcePermissions($parentResource, 
							$resource[self::RESOURCES_CONFIG_PERMISSIONS_IDENTIFIER]);
			}
				
			if (key_exists(self::RESOURCES_CONFIG_CHILDRESOURCES_IDENTIFIER, 
							$resource))
				$this->processResourcesArray($resource[self::RESOURCES_CONFIG_CHILDRESOURCES_IDENTIFIER],
											 $parentResource);
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
		if (key_exists(self::RESOURCES_CONFIG_RESOURCE_MODULE_IDENTIFIER, 
					   $resource))
		{
			if (null !== $parent)
			{
				if ($resource[self::RESOURCES_CONFIG_RESOURCE_MODULE_IDENTIFIER] !== $parent->getModule())
					throw new Zend_Acl_Exception("The same module must be used when inheriting from a parent page-resource.");
			}
			
			return $resource[self::RESOURCES_CONFIG_RESOURCE_MODULE_IDENTIFIER];
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
		if (key_exists(self::RESOURCES_CONFIG_RESOURCE_CONTROLLER_IDENTIFIER,
					   $resource))
		{
			if (null !== $parent)
			{
				$parentController = $parent->getController();
				if (!empty($parentController) && $resource[self::RESOURCES_CONFIG_RESOURCE_CONTROLLER_IDENTIFIER] !== 
													$parentController)
					throw new Zend_Acl_Exception("The same controller must be used when inheriting from a parent page-resource with specified controller.");
			}
			return $resource[self::RESOURCES_CONFIG_RESOURCE_CONTROLLER_IDENTIFIER];
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
		if (key_exists(self::RESOURCES_CONFIG_RESOURCE_ACTION_IDENTIFIER,
					   $resource))
		{
			$parentController = null;
			if (null !== $parent)
				$parentController = $parent->getController();
				
			if (empty($parentController) && !key_exists(self::RESOURCES_CONFIG_RESOURCE_CONTROLLER_IDENTIFIER,
														$resource))
				throw new Zend_Acl_Exception("You must specify a controller when creating a resource for a controller-action.");
				
			$parentAction = $parent->getAction();
			if (!empty($parentAction))
				throw new Zend_Acl_Exception("You cannot create a controller-action resource extending from a page-resource with an action already specified.");
				
			return $resource[self::RESOURCES_CONFIG_RESOURCE_ACTION_IDENTIFIER];
		}
		return null;
	}
	
	/**
	 * Function sets permissions for a given resource.
	 * 
	 * @param Zend_Acl_Resource_Interface|array|string $resource
	 * @param array $permissionsList
	 * @return Default_Model_Acl
	 * @throws Zend_Acl_Exception
	 */
	public function setResourcePermissions($resource, array $permissionsList)
	{
		foreach ($permissionsList as $permissionType => $permissions)
		{
			if (Default_Model_Array_Utils::isAssoc($permissions))
				$permissions = array($permissions);
				
			foreach ($permissions as $permission)
			{
				$role = null;
				if (key_exists(self::ROLES_CONFIG_ROLE_IDENTIFIER, $permission))
				{
					$role = $permission[self::ROLES_CONFIG_ROLE_IDENTIFIER];
					unset($permission[self::ROLES_CONFIG_ROLE_IDENTIFIER]);
				}
				
				switch ($permissionType)
				{
					case self::PERMISSIONS_CONFIG_TYPE_ALLOWALL:
						$this->allow(null, $resource, array_keys($permission));
						break;
						
					case self::PERMISSIONS_CONFIG_TYPE_DENYALL:
						$this->deny(null, $resource, array_keys($permission));
						break;
						
					case self::PERMISSIONS_CONFIG_TYPE_ALLOW:
						if (empty($role))
							throw new Zend_Acl_Exception("No role was specified for an \"allow\" rule node.");
							
						if (empty($permission))
							throw new Zend_Acl_Exception("No permissions were specified for an \"allow\" rule node.");
							
						$this->allow($role, $resource, array_keys($permission));
						break;
						
					case self::PERMISSIONS_CONFIG_TYPE_DENY:
						if (empty($role))
							throw new Zend_Acl_Exception("No role was specified for a \"deny\" rule node.");
							
						if (empty($permission))
							throw new Zend_Acl_Exception("No permissions were specified for a \"deny\" rule node.");
						
						$this->deny($role, $resource, array_keys($permission));
						break;
						
					case self::PERMISSIONS_CONFIG_TYPE_DISABLEALL:
						if (empty($role))
							throw new Zend_Acl_Exception("No role was specified for a \"block\" rule node.");
							
						$this->deny($role, $resource);
						break;
					
					case self::PERMISSIONS_CONFIG_TYPE_ENABLEALL:
						if (empty($role))
							throw new Zend_Acl_Exception("No role was specified for an \"unblock\" rule node.");
							
						$this->allow($role, $resource);
						break;
						
					default:
						throw new Zend_Acl_Exception("An unknown permission setter was discovered.");
				} 
			}
		}
		
		return $this;
	}
}