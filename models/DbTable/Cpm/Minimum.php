<?php
class Application_Model_DbTable_Cpm_Minimum extends Zend_Db_Table_Abstract
{
    protected $_primary = 'id';
    protected $_name = 'minimum_cpm';
    protected $_rowClass='Application_Model_DbTable_Row_Cpm_Minimum';
    
   //protected $_dependentTables = array('Application_Model_DbTable_Cpm_File');
    
    ////// status //////
    // 1 - new        //
    // 2 - rejected   //
    // 3 - approved   //
    ////////////////////
    
    public function getNum($status = 1)
    {
        $sql = $this->_db->select()
                    ->from($this->_name, array('COUNT(id) AS count'))
                    ->where('status = ?', $status);
        
        $data = $this->_db->query($sql)->fetchAll();
        
        return $data[0]['count'];
    }

    function getCpmSite($site_id){
        $sql = $this->_db->select()
            ->from(['mc'=>$this->_name],[
                'mc.id',
                'mc.cpm',
                'mc.estim_cpm',
                'mc.estim_rate',
                'mc.status'
            ]);

        $sql->join(['s'=>'sites'], 's.SiteID=mc.SiteID', [
            's.SiteName'
        ]);

        $sql->where('mc.SiteID=?', $site_id);
        $sql->where('s.status=?', 3);
        $sql->where('mc.status!=?', 4);
        $sql->order('mc.id DESC');

        return $this->_db->query($sql)->fetch();
    }
}
