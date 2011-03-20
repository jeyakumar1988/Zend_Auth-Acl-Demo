<?php
/**
 * @author Alex Lintott <alex.lintott@lintal.co.uk>
 * @package Utilities
 * @subpackage Default_Model_Array_Utilities
 * @copyright Alex Lintott - Mar 20, 2011 
 */
class Default_Model_Array_Utils 
{
	const MATCH_ALL = 'all';
	const MATCH_PARTIAL = 'partial';
	
	/**
	 * Function to test if an array contains associative keys.
	 * 
	 * @param array $aData
	 * @return bool
	 */
	public static function isAssoc(array $aData) 
	{
		$arrayKeys = self::getKeys($aData);
		$testKeys = self::getTestKeys($aData);
		
		return ($arrayKeys !== $testKeys);
	}
	
	/**
	 * Function to test if an array contains only numeric keys.
	 * 
	 * @param array $aData
	 * @return bool
	 */
	public static function isNumeric (array $aData) 
	{
		$arrayKeys = self::getKeys($aData);
		$testKeys = self::getTestKeys($aData);
		
		return ($arrayKeys === $testKeys);
	}
	
	/**
	 * Function returns array keys.
	 * 
	 * @param array $aData
	 * @return array
	 */
	protected static function getKeys(array $aData)
	{
		return array_keys($aData);
	}
	
	/**
	 * Function returns a list of numeric values for testing
	 * array types.
	 * 
	 * @see Default_Model_Array_Utils::isAssoc()|Default_Model_Array_Utils::isNumeric()
	 * @param array $aData
	 * @return array
	 */
	protected static function getTestKeys(array $aData)
	{
		return range(0, count ($aData) - 1);;
	}
	
	/**
	 * Function returns only requested values from an array 
	 * by key. Note: Returned in order of array being filtered.
	 * 
	 * @param array $aData
	 * @param array $desiredKeys
	 * @return array
	 * @throws Zend_Exception
	 */
	public static function filterArrayByKeys(array $aData, array $desiredKeys) 
	{
		if (!self::isAssoc($aData))
			throw new Zend_Exception("A non-associative data array was passed.");
			
		if (!self::isNumeric($desiredKeys))
			throw new Zend_Exception("A non-sequential numeric or associative array was passed");
			
		return array_intersect_key($aData, array_flip($desiredKeys));
	}
	
	/**
	 * Function orders an array by key in the order specified
	 * in the second arguement. If any other keys exist outside
	 * those specified, they are appended to the end of the 
	 * resulting array.
	 * 
	 * @param array $aData
	 * @param array $aKeys
	 * @return array
	 * @throws Zend_Exception
	 */
	public static function orderArrayByKeys(array $aData, array $aKeys)
	{
		if (! self::isAssoc($aData))
			throw new Zend_Exception("A non-associative array was passed.");
		
		$aReturn = array();
		foreach ($aKeys as $sKey) 
		{
			if (key_exists($sKey, $aData)) 
			{
				$aReturn[$sKey] = $aData[$sKey];
				unset ($aData[$sKey]);
			}
		}
		
		if (0 < count($aData))
			$aReturn = array_merge($aReturn, $aData);
			
		return $aReturn;
	}
	
	/**
	 * Function tests to see if some or all keys exist in an associative array.
	 * 
	 * @param array $aData
	 * @param array $aKeys
	 * @param string $type
	 * @return bool
	 * @throws Zend_Exception
	 */
	public static function keysExist(array $aData, array $aKeys, $type = self::MATCH_PARTIAL)
	{
		if (!self::isAssoc($aData))
			throw new Zend_Exception("A non-associative array was passed");
			
		$availableKeys = array_keys($aData);
		$result = true;
		foreach ($aKeys as $sKey) {
			if (in_array($sKey, $availableKeys))
			{
				switch ($type)
				{
					case self::MATCH_PARTIAL:
						return true;
					case self::MATCH_ALL:
						continue;
					default:
						throw new Zend_Exception("An unknown search-type was provided");
				}
			}
			$result = false;
		}
		
		return $result;
	}
}