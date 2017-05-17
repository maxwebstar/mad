<?php
class Application_Model_DbTable_Optimization_CaseSite extends Zend_Db_Table_Abstract
{
    protected $_name = 'optimization_site_case';
    protected $_primary = 'id';

    public function saveData($data)
    {
		$fields = array(
			'`SiteID`'			=> "'".$data['SiteID']."'",
			'`ValueClick`'		=> "'".$data['ValueClick']."'",
			'`Advertising`'		=> "'".$data['Advertising']."'",
			'`Media`'			=> "'".$data['Media']."'",
			'`UnderDog`'		=> "'".$data['UnderDog']."'",
			'`AudienceScience`'	=> "'".$data['AudienceScience']."'",
			'`updated`'			=> "'".date('Y-m-d')."'"
		);

		if ($data['ValueClick'] == 2)		$fields['`ValueClick_pending_dt`']		= 'NOW()';
		if ($data['Advertising'] == 2)		$fields['`Advertising_pending_dt`']		= 'NOW()';
		if ($data['Media'] == 2)			$fields['`Media_pending_dt`']			= 'NOW()';
		if ($data['UnderDog'] == 2)			$fields['`UnderDog_pending_dt`']		= 'NOW()';
		if ($data['AudienceScience'] == 2)	$fields['`AudienceScience_pending_dt`']	= 'NOW()';

		foreach ($fields as $key => $val) {
			$sqlData[] = $key." = ".$val;
		}

		$sql =	"INSERT INTO `".$this->_name."` (".implode(', ', array_keys($fields)).") ";
		$sql .=	"VALUES (".implode(', ', array_values($fields)).") ";
		$sql .=	"ON DUPLICATE KEY UPDATE ".implode(', ', $sqlData);

		return $this->_db->query($sql);
    }

	public function getForNetwork($field,$status = 2)
	{
	    if(!intval($status))
	        $status = 2;
		$sql = 'SELECT
					osc.SiteID,
					osc.'.$field.'_pending_dt AS `date_add`,
					s.SiteName,
					s.impressions_1day_ago AS impressions,
					s.accept_pending,
					s.deny_pending,
					(SELECT mro.impressions FROM madads_rubicon_optimization mro WHERE mro.SiteID = s.SiteID AND mro.impressions > 100000 ORDER BY mro.query_date LIMIT 1) AS usa
				FROM optimization_site_case osc
				JOIN sites s ON s.SiteID = osc.SiteID
				WHERE '.$field.' = '.$status;

		return $this->_db->query($sql)->fetchAll();
	}

	public function updateStatus($siteId, $network ,$status)
	{
	   $siteId = intval($siteId);
	   $status = intval($status);
	   if(isset($siteId) AND isset($status) AND isset($network))
	   {
	       $list = array(
	           'valueclick' => 'ValueClick',
	           'advertising.com' => 'Advertising',
	           'media.net' => 'Media',
	           'realmedia' => 'UnderDog',
	           'audiencescience' => 'AudienceScience'
	       );
	       $sql = 'UPDATE `optimization_site_case` SET `'.$list[$network].'` = '.$status.' WHERE SiteID = ' . $siteId . '';
	       if($this->_db->query($sql))
	           return true;
	   }
	   else
	       return false;
	}
}
?>
