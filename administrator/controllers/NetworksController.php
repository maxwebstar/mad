<?php
class Administrator_NetworksController extends Zend_Controller_Action
{

    public function init()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('admin_v2');
    }

    public function indexAction()
    {

    }

    public function getNetworksAjaxAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        $networksModel = new Application_Model_DbTable_Networks();

        if($this->getRequest()->isPost()){
            $columns = [];
            $orders = [];
            $where = null;
            $tableName = "networks";
            $start = (int)$this->getRequest()->getPost('start');
            $lenght = (int)$this->getRequest()->getPost('length');
            $search = $this->getRequest()->getPost('search')['value'];

            $checkquery = $networksModel->select()
                ->from($tableName, array("num"=>"COUNT(*)"));

            foreach($this->getRequest()->getPost('columns') as $column){
                $columns[$column['data']] = $tableName. '.' . $column['data'];
            }

            $dataquery = $networksModel->select()
                ->from($tableName, $columns);

            if($search){
                foreach($this->getRequest()->getPost('columns') as $column){
                    if($column['searchable']){
                        $where .= "$tableName." . $column['data'] . " LIKE'%$search%' OR ";
                    }
                }
            }

            if($this->getRequest()->getPost('order')){
                foreach($this->getRequest()->getPost('order') as $order){
                    $orders[] = $tableName. '.'. $this->getRequest()->getPost('columns')[$order['column']]['data']." ".$order['dir'];
                }
            }

            $dataquery->order($orders);
            if($where){
                $where = substr_replace( $where, "", -3 );
                $dataquery->where($where);
                $checkquery->where($where);
            }

            if($start>0){
                $dataquery->limit($start, $lenght);
            }else{
                $dataquery->limit($lenght);
            }


            $checkrequest = $networksModel->fetchRow($checkquery);
            $datarequest = $networksModel->fetchAll($dataquery)->toArray();

            $result = [
                "draw"=>(int)$this->getRequest()->getPost('draw'),
                "recordsTotal"=>$checkrequest["num"],
                "recordsFiltered"=>$checkrequest["num"],
                "test"=>$dataquery->__toString(),
                "data"=>$datarequest
            ];
        }else{
            $result = [
                "draw"=>0,
                "recordsTotal"=>0,
                "recordsFiltered"=>0,
                "data"=>[],
                "error"=>"No post query"
            ];
        }
        $this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
    }

    public function addAction()
    {
        $networksModel = new Application_Model_DbTable_Networks();
        $sizesModel = new Application_Model_DbTable_Sizes();

        $this->view->dataNetworks = $networksModel->getActiveNetworks();
        $this->view->dataSizes = $sizesModel->getAllSize();

        $form = new Application_Form_Networks();

        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $dataNetwork = $networksModel->createRow();
                $dataNetwork->setFromArray(array(
                    'parent_id' => $form->getValue('parent_id'),
                    'name' => $form->getValue('name'),
                    'short_name' => $form->getValue('short_name'),
                    'created_at' => date("Y-m-d H:i:s"),
                    'active' => $form->getValue('active')==1 ? 1 : 0,
                    'position' => $form->getValue('position'),
                    'show' => $form->getValue('show')==1 ? 1 : 0
                ));
                $dataNetwork->save();

                if(count($form->getValue('sizes_id'))){
                    $NetworksSizesModel = new Application_Model_DbTable_NetworksSizes();
                    foreach($form->getValue('sizes_id') as $size){
                        $dataNetworkSizes = $NetworksSizesModel->createRow();
                        $dataNetworkSizes->setFromArray(array(
                            'network_id' => $dataNetwork->id,
                            'size_id' => $size,
                            'created_at' => date("Y-m-d H:i:s"),
                            'request'=>in_array($size, $form->getValue('size_request')) ? 1 : 0,
                            'primary'=>in_array($size, $form->getValue('only_primary')) ? 1 : 0
                        ));
                        $dataNetworkSizes->save();
                    }
                }

                $this->_redirect('/administrator/networks');
            }else{
                $this->view->formErrors = $form->getMessages();
                $this->view->formValues = $form->getValues();
            }
        }
    }

    public function editAction()
    {
        $network_id = (int)$this->getRequest()->getParam('id');
        if($network_id>0){
            $networksModel = new Application_Model_DbTable_Networks();
            $NetworksSizesModel = new Application_Model_DbTable_NetworksSizes();
            $sizesModel = new Application_Model_DbTable_Sizes();
            $form = new Application_Form_Networks();

            $dataNetwork = $networksModel->getNetworkById($network_id);
            $this->view->dataNetworks = $networksModel->getActiveNetworks();
            $this->view->dataSizes = $sizesModel->getAllSize();

            if($this->getRequest()->isPost()){
                if($form->isValid($this->getRequest()->getPost())){

                    $networksModel->update(array(
                        'parent_id' => $form->getValue('parent_id'),
                        'name' => $form->getValue('name'),
                        'short_name' => $form->getValue('short_name'),
                        'updated_at' => date("Y-m-d H:i:s"),
                        'active' => $form->getValue('active') == 1 ? 1 : 0,
                        'position' => $form->getValue('position'),
                        'show' => $form->getValue('show') == 1 ? 1 : 0,
                    ), $networksModel->getAdapter()->quoteInto('id = ?', $network_id));

                    $sitesTagsModel = new Application_Model_DbTable_SitesTags();

                    $sitesTagsModel->update([
                        'action'=>'remove',
                        'env'=>APPLICATION_ENV
                    ],
                        [
                            $sitesTagsModel->getAdapter()->quoteInto('network_id = ?', $network_id),
                            $sitesTagsModel->getAdapter()->quoteInto('size_id NOT IN (?)', $form->getValue('sizes_id'))
                        ]);

                    $where = [];
                    $where[] = $NetworksSizesModel->getAdapter()->quoteInto('size_id NOT IN (?)', $form->getValue('sizes_id'));
                    $where[] = $NetworksSizesModel->getAdapter()->quoteInto('network_id = ?', $network_id);
                    $NetworksSizesModel->delete($where);

                    if(count($form->getValue('sizes_id'))){
                        foreach($form->getValue('sizes_id') as $size){
                            $dataSizes = $NetworksSizesModel->fetchRow([
                                $NetworksSizesModel->getAdapter()->quoteInto('network_id = ?', $network_id),
                                $NetworksSizesModel->getAdapter()->quoteInto('size_id = ?', $size)
                            ]);
                            if(!$dataSizes){
                                $dataNetworkSizes = $NetworksSizesModel->createRow();
                                $dataNetworkSizes->setFromArray(array(
                                    'network_id' => $network_id,
                                    'size_id' => $size,
                                    'created_at' => date("Y-m-d H:i:s"),
                                    'request'=>in_array($size, $form->getValue('size_request')) ? 1 : 0,
                                    'primary'=>in_array($size, $form->getValue('only_primary')) ? 1 : 0
                                ));
                                $dataNetworkSizes->save();
                            }else{
                                $dataSizes->request = in_array($size, $form->getValue('size_request')) ? 1 : 0;
                                $dataSizes->primary = in_array($size, $form->getValue('only_primary')) ? 1 : 0;
                                $dataSizes->save();
                            }
                        }
                    }

                    if($form->getValue('active')!=1){
                        $sitesTagsModel->update([
                            'action'=>'remove',
                            'env'=>APPLICATION_ENV
                        ],
                            [
                                $sitesTagsModel->getAdapter()->quoteInto('network_id = ?', $network_id)
                            ]);
                    }

                    $this->_redirect('/administrator/networks');
                }else{
                    $this->view->formErrors = $form->getMessages();
                    $this->view->formValues = $form->getValues();
                }
            }else{
                $dataNetwork['sizes_id'] = [];
                $dataNetwork['size_request'] = [];
                $dataNetwork['only_primary'] = [];
                $networkSizes = $NetworksSizesModel->getNetworkSizes($network_id);
                if(count($networkSizes)){
                    foreach($networkSizes as $item){
                        $dataNetwork['sizes_id'][] = $item['size_id'];
                        if($item['request']==1)
                            $dataNetwork['size_request'][] = $item['size_id'];
                        if($item['primary']==1)
                            $dataNetwork['only_primary'][] = $item['size_id'];

                    }
                }
                $this->view->formValues = $dataNetwork;
            }
        }else{
            $this->_redirect('/administrator/networks');
        }
    }
}