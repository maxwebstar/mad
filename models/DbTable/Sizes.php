<?php
/**
 * Description of Sizes
 *
 * @author nik
 */
class Application_Model_DbTable_Sizes extends Zend_Db_Table_Abstract 
{
    protected $_name = 'display_size';
    protected $_rowClass='Application_Model_DbTable_Row_Sizes';
    
    public function getSize($SiteID)
    {
        $sqlSite = $this->_db->select()
                        ->from('sites', array('pop_unders AS pop'))
                        ->where('SiteID = ?', $SiteID);
        
        $dataSite = $this->_db->query($sqlSite)->fetch();
        
        $sqlSize = $this->_db->select()
                        ->from($this->_name, array('id', 'file_name AS size'))
                        ->order('position');
   
        if($dataSite['pop'] == false) $sqlSize->where('file_name != "pop"');
        
        $dataSize = $this->_db->query($sqlSize)->fetchAll();
                
        return $dataSize;
    }
    
    public function getSize2($pop, $video=true)
    {
        //$sql = $this->select()->order('position');
    	$sql = $this->select();
   
        if($pop == false) $sql->where('file_name != "pop"');
        
        if($video == true) $sql->where('video IS NULL');
        
        return $this->fetchAll($sql);                
    }
    
    public function getZoneSize($id)
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('rp_zonesize'))
                    ->where('id = ?', $id);
        
        $data = $this->_db->query($sql)->fetch();
        
        return $data['rp_zonesize'];
    }
    
    public function getData($id)
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->where('id = ?', $id);
        
        return $this->_db->query($sql)->fetch();
    }
    
    public function getAllSize($exclusion = false)
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->order('position ASc');
        
        if($exclusion) $sql->where('file_name != "pop"');
        
        return $this->_db->query($sql)->fetchAll();
    }
    
    public function getRubiconSizes()
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->order('position ASc');
        
        $sql->where('file_name != "pop"');
        $sql->where('hide_rubicon IS NULL');
        
        return $this->_db->query($sql)->fetchAll();	    
    }
    
    public function getGoogleSizes()
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('*'))
                    ->order('position ASc');
        
        $sql->where('file_name != "pop"');
        $sql->where('hide_google IS NULL');
        
        return $this->_db->query($sql)->fetchAll();	    
    }    
    
    public function getActiveSizeBySite($SiteID, $exceptPOP = true)
    {
        $sql = $this->_db->select()
                    ->from(array('ds' => 'display_size'), 
                           array('ds.id', 'ds.name', 'ds.width', 'ds.height', 'ds.file_name', 'ds.description', 'ds.position'))
                    ->joinLeft(array('ss' => 'site_size'), 'ss.AdSize = ds.id AND ss.SiteID = "'.$SiteID.'"', array(''))
                    ->where('IF(ds.individual = 1, IFNULL(ss.active, 0), 1) = 1')
                    ->group('ds.id')
                    ->order('ds.position');
        
        if($exceptPOP){ $sql->where('ds.file_name != "pop"'); }
        
        return $this->_db->query($sql)->fetchAll();        
    }    
    
    public function getIndividualSize($SiteID, $exceptPOP = true)
    {
        $sql = $this->_db->select()
                    ->from(array('ds' => 'display_size'), 
                           array('ds.id', 'ds.name', 'ds.width', 'ds.height', 'ds.file_name', 'ds.description', 'ds.position', 'ds.individual'))
                    ->joinLeft(array('ss' => 'site_size'), 'ss.AdSize = ds.id AND ss.SiteID = "'.$SiteID.'"', 
                               array('IFNULL(ss.active, 0) AS active'))
                    ->where('ds.individual = 1')
                    ->group('ds.id')
                    ->order('ds.position');
        
        if($exceptPOP) $sql->where('ds.file_name != "pop"');
      
        return $this->_db->query($sql)->fetchAll(); 
    }   
    
    public function saveData($data)
    {   
        $sql = "INSERT INTO `site_size` (`SiteID`, `AdSize`, `active`) VALUES ('".$data['SiteID']."', '".$data['AdSize']."', '".$data['value']."') ON DUPLICATE KEY UPDATE active = '".$data['value']."'";

        $this->_db->query($sql);
    }

    public function getSizesNetworks()
    {
        $sql = $this->_db->select()
            ->from(array('ds' => 'display_size'), array(
                'ds.id',
                'ds.name',
                'ds.width',
                'ds.height',
                'ds.file_name',
                'ds.description',
                'ds.rp_zonesize'))
            ->join(array('ns' => 'networks_sizes'), 'ns.size_id = ds.id', array(
                'ns.network_id'))
            ->join(array('n' => 'networks'), 'n.id = ns.network_id', array())
            ->where('n.active = 1')
            ->where('ds.active = 1')
            ->order('n.position ASC')
            ->order('ds.position ASC');
        return $this->_db->query($sql)->fetchAll();
    }
}