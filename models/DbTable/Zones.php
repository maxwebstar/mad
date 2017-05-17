<?php

/**
 * Zones database view
 * 
 * @author Ralph Ritoch <rritoch@gmail.com>
 */


class Application_Model_DbTable_Zones extends Zend_Db_Table_Abstract
{

	protected $_name = 'display_zone_reference';
	
	static $_inst;
	
	public static function &getInstance() 
	{
		if (!isset(self::$_inst)) {
			self::$_inst = new Application_Model_DbTable_Zones();
		}
		return self::$_inst;		
	}
	
	public function doUnlink($userId,$childZone) 
	{	
		$childZone = is_numeric($childZone) ? (int)$childZone : $childZone;
			
		if (is_int($childZone)) {
		    $query = "UPDATE `display_zone` SET `applied_zone` = ?  WHERE `id` = ?";
		    $args = array($childZone,$childZone);		
		    $result = $this->_db->query($query,$args);		
		    return true;
		}				
		return false;
	}
	
	public function doLink($userId,$parentZone,$childZone) 
	{
		$parentZone = is_numeric($parentZone) ? (int)$parentZone : $parentZone;		
		$childZone = is_numeric($childZone) ? (int)$childZone : $childZone;
				
		if (is_int($parentZone) && is_int($childZone)) {  
		    $query = "UPDATE `display_zone` SET `applied_zone` = ?  WHERE `id` = ?";
		    $args = array($parentZone,$childZone);
		    $result = $this->_db->query($query,$args);		
		    return true;
		}
		return false;
	}
	
	public function getUserZones($userId)	
	{
		
		$qry = "SELECT `dzr`.`display_zone` AS `display_zone`, `dz`.`UName` as `UName`, `dz`.`applied_zone` AS `applied_zone`, `dzr`.`zone` AS `zone`, `dzr`.`placementName` as `placementName` FROM `display_zone_reference` `dzr` LEFT JOIN `display_zone` `dz` ON `dz`.`id`=`dzr`.`display_zone` WHERE `dzr`.`display_zone` IN (SELECT DISTINCT `display_zone` FROM `placement_reference` LEFT JOIN `placement` on `placement_reference`.`placement` = `placement`.`id` LEFT JOIN `sites` ON `sites`.`SiteID` = `placement`.`site` WHERE `sites`.`PubID`= ?)";	
		$result = $this->_db->query($qry,array($userId));
		
		$rows = $result->fetchAll();
				
		$track = array();
		$ret = array();
		foreach($rows as $row) {
			$key = $row['applied_zone'];			
			if (!isset($track[$key])) {
				$track[$key] = count($ret);
				$ret[$track[$key]] = new stdClass;
				$ret[$track[$key]]->id = $row['applied_zone'];
				$ret[$track[$key]]->UName = null;
				$ret[$track[$key]]->refrences = array();								
			}
			
			if ($row['applied_zone'] == $row['display_zone']) {
			    $ret[$track[$key]]->UName = $row['UName'];
			}

			$ref = new stdClass;
			$ref->zoneId = $row['display_zone'];
			$ref->zone = $row['zone'];
			$ref->placementName = $row['placementName'];
			$ret[$track[$key]]->refrences[] = $ref;			
		}
		return $ret;	
	}
	
}