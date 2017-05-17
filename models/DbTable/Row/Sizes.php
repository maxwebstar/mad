<?php
/**
 * Description of Sizes
 *
 * @author nik
 */
class Application_Model_DbTable_Row_Sizes extends Zend_Db_Table_Row_Abstract 
{
    protected $_tableClass='Application_Model_DbTable_Sizes';
    
    public function getContent($PubID, $SiteID, $folder = 'async', $type = 'js')
    {
        $content = null;
        $file = $_SERVER['DOCUMENT_ROOT']."/tags/".$PubID."/".$SiteID."/".$folder."/".$this->file_name.".".$type;
        
        if(is_file($file)) $content = file_get_contents($file);
        
        return $content;
    }
}

?>
