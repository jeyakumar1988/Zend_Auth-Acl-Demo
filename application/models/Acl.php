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
	const ROLES_CONFIG_BASEROLE_IDENTIFIER = 'baseRole';
	
	/**
	 * Roles-Config: Identifier for child roles node.
	 */
	const ROLES_CONFIG_CHILDROLES_IDENTIFIER = 'roles';
	
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
	 * @uses self::addRoles()
	 * @param Zend_Config|array $roles
	 * @return void
	 */
	public function setRoles($roles) 
	{
		$this->removeRoleAll();
		$this->addRoles($roles);
	}
	
	/**
	 * Function adds roles from either a Zend_Config object,
	 * or an appropriately formed array.
	 * 
	 * @param Zend_Config|array $roles
	 * @return void
	 * @throws Zend_Acl_Exception
	 */
	public function addRoles($roles) 
	{
		if ($roles instanceof Zend_Config) 
			$roles = $roles->toArray();
		
		if (!is_array($roles)) 
			throw new Zend_Acl_Exception("An unknown list of roles was provided.");
		
		$this->processRolesArray($roles);
		
		//If base-role provided, set-up all other roles to inherit.
		if (key_exists(self::ROLES_CONFIG_BASEROLE_IDENTIFIER, $roles))
			$this->addRole($roles[self::ROLES_CONFIG_BASEROLE_IDENTIFIER]
								 [self::ROLES_CONFIG_ROLENAME_IDENTIFIER], 
								 	$this->getRoles());
	}
	
	/**
	 * Function processes a roles configuration array and adds
	 * it to the ACL object.
	 * 
	 * @param array $roles
	 * @param string|null $parent
	 * @return void
	 */
	protected function processRolesArray(array $roles, $parent = null) 
	{
		$roles = $roles[self::ROLES_CONFIG_ROLE_IDENTIFIER];
		if (key_exists(self::ROLES_CONFIG_ROLENAME_IDENTIFIER, $roles)) 
			$roles = array($roles);
		
		foreach($roles as $role) 
		{
			$roleName = $role[self::ROLES_CONFIG_ROLENAME_IDENTIFIER];
			$this->addRole($roleName, $parent);
			
			if (key_exists(self::ROLES_CONFIG_CHILDROLES_IDENTIFIER, $role))
				$this->processRolesArray($role[self::ROLES_CONFIG_CHILDROLES_IDENTIFIER], 
										$roleName);
		}
	}
}