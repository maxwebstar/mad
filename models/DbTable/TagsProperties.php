<?php
class Application_Model_DbTable_TagsProperties extends Zend_Db_Table_Abstract
{
    protected $_name = 'sites_tags_properties';
    protected $_primary = 'id';

    function getSiteTagsProp($site_id){
        $sql = $this->_db->select()
            ->from($this->_name .' AS stp' , array(
                'tag_id'=>'stp.tag_id',
                'name'=>'stp.name',
                'value'=>'stp.value'
            ));

        $sql->join('sites_tags AS st', 'st.id=stp.tag_id', [
            'size_id'=>'st.size_id'
        ]);

        $sql->where('st.site_id=?', $site_id);

        $data = $this->_db->query($sql)->fetchAll();
        $dataArr = [];
        if($data){
            foreach($data as $item){
                $dataArr[$item['tag_id']][$item['size_id']][$item['name']] = $item['value'];
            }
        }
        return $dataArr;
    }
}