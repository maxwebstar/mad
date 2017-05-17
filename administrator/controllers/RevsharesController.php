<?php

/**
 * Created by PhpStorm.
 * User: nick
 * Date: 12.07.16
 * Time: 10:04
 */
class Administrator_RevsharesController extends Zend_Controller_Action
{
    public function init()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('admin_v2');
    }

    public function indexAction()
    {

    }

    public function addAction()
    {
        $sitesTagsModel = new Application_Model_DbTable_SitesTags();
        $revshareModel = new Application_Model_DbTable_Revshares();
        $sitesModel = new Application_Model_DbTable_Sites();
        $form = new Application_Form_Revshares();

        $site_id = (int)$this->getRequest()->getParam('site');
        $dataSizes = $sitesTagsModel->getSizesForTag($site_id, -1, 'ds.id');
        $dataSite = $sitesModel->fetchRow($sitesModel->getAdapter()->quoteInto('SiteID = ?', $site_id));

        if($this->getRequest()->isPost()){
            $dataRequest['revshare'] = array_filter($this->getRequest()->getPost()['revshare']);
            if($form->isValid($dataRequest)){

                foreach ($form->getValue('revshare') as $id=>$revshare){
                    $dataRevshare = $revshareModel->createRow();
                    $dataRevshare->setFromArray([
                        'pub_id'=>$dataSite['PubID'],
                        'site_id'=>$site_id,
                        'size_id'=>$id,
                        'RevShare'=>$revshare,
                        'created_at'=>date("Y-m-d H:i:s")
                    ]);
                    $dataRevshare->save();
                }
                $this->_redirect('/administrator/revshares');
            }else{
                $this->view->formErrors = $form->getMessages();
                $this->view->formValues = $form->getValues();
            }
        }

        $this->view->dataSizes = $dataSizes;
    }

    public function editAction()
    {
        $sitesTagsModel = new Application_Model_DbTable_SitesTags();
        $revshareModel = new Application_Model_DbTable_Revshares();
        $sitesModel = new Application_Model_DbTable_Sites();
        $form = new Application_Form_Revshares();

        $site_id = (int)$this->getRequest()->getParam('site');
        $dataSizes = $sitesTagsModel->getSizesForTag($site_id, -1, 'ds.id');
        $dataRevshareSizes = $revshareModel->getSaveSizes($site_id);

        $dataSite = $sitesModel->fetchRow($sitesModel->getAdapter()->quoteInto('SiteID = ?', $site_id));

        if($this->getRequest()->isPost()){
            $dataRequest['revshare'] = array_filter($this->getRequest()->getPost()['revshare']);
            if($form->isValid($dataRequest)){
                $null = new Zend_Db_Expr("NULL");
                $revshareModel->update(['updated_at'=>$null], $revshareModel->getAdapter()->quoteInto('site_id = ?', $site_id));

                foreach ($form->getValue('revshare') as $id=>$revshare){
                    $dataRevshares = $revshareModel->fetchRow([
                        $revshareModel->getAdapter()->quoteInto('site_id = ?', $site_id),
                        $revshareModel->getAdapter()->quoteInto('size_id = ?', $id)
                    ]);
                    if($dataRevshares){
                        $dataRevshares->RevShare = $revshare;
                        $dataRevshares->updated_at = date("Y-m-d H:i:s");
                        $dataRevshares->save();
                    }else{
                        $dataRevshares = $revshareModel->createRow();
                        $dataRevshares->setFromArray([
                            'pub_id'=>$dataSite['PubID'],
                            'site_id'=>$site_id,
                            'size_id'=>$id,
                            'RevShare'=>$revshare,
                            'created_at'=>date("Y-m-d H:i:s"),
                            'updated_at'=>date("Y-m-d H:i:s")
                        ]);
                        $dataRevshares->save();
                    }
                }

                $revshareModel->delete([
                    $revshareModel->getAdapter()->quoteInto('site_id = ?', $site_id),
                    $revshareModel->getAdapter()->quoteInto('updated_at IS ?', $null),
                ]);

                $this->_redirect('/administrator/revshares');
            }else{
                $this->view->formErrors = $form->getMessages();
                $this->view->formValues = $form->getValues();
            }
        }else{
            $dataForm = [];
            $dataForm['revshare'] = $revshareModel->getRevsharesValues($site_id);
            $this->view->formValues = $dataForm;
        }

        $sizesInView = [];

        if($dataRevshareSizes){
            foreach ($dataRevshareSizes as $size){
                $sizesInView[$size['id']] = $size;
            }
        }

        if($dataSizes){
            foreach ($dataSizes as $size){
                if(!isset($sizesInView[$size['id']]))
                    $sizesInView[$size['id']] = $size;
            }
        }
        ksort($sizesInView);

        $this->view->dataSizes = $sizesInView;
    }

    public function ajaxRevAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        if($this->getRequest()->isPost()){
            $sitesRevshares = new Application_Model_DbTable_Revshares();

            $columns = [];
            $orders = [];
            $where = null;

            $start = (int)$this->getRequest()->getPost('start');
            $lenght = (int)$this->getRequest()->getPost('length');
            $search = $this->getRequest()->getPost('search')['value'];

            $checkquery = $sitesRevshares->select()
                ->from('users_revshares_size AS ur', array("num"=>"COUNT(ur.site_id)"))
                ->join('sites AS s', 's.SiteID=ur.site_id', array())
                ->group('ur.site_id')
                ->setIntegrityCheck(false);

            foreach($this->getRequest()->getPost('columns') as $column){
                $columns[$column['data']] = 's.' . $column['data'];
            }

            $dataquery = $sitesRevshares->select()
                ->from('users_revshares_size AS ur', $columns)
                ->join('sites AS s', 's.SiteID=ur.site_id', array())
                ->group('ur.site_id')
                ->setIntegrityCheck(false);

            if($search){
                foreach($this->getRequest()->getPost('columns') as $column){
                    if($column['searchable']=='true'){
                        $where .= $column['data'] . " LIKE'%$search%' OR ";
                    }
                }
            }

            if($this->getRequest()->getPost('order')){
                foreach($this->getRequest()->getPost('order') as $order){
                    $orders[] = $this->getRequest()->getPost('columns')[$order['column']]['data']." ".$order['dir'];
                }
            }

            $dataquery->order($orders);
            if($where){
                $where = substr_replace( $where, "", -3 );
                $dataquery->where($where);
                $checkquery->where($where);
            }

            $dataquery->limit($lenght, $start);

            $checkrequest = $sitesRevshares->fetchAll($checkquery);
            $datarequest = $sitesRevshares->fetchAll($dataquery);

            $result = [
                "draw"=>(int)$this->getRequest()->getPost('draw'),
                "recordsTotal"=>$checkrequest->count(),
                "recordsFiltered"=>$checkrequest->count(),
                "data"=>$datarequest->toArray()
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

    public function ajaxNoRevAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        if($this->getRequest()->isPost()){
            $sitesRevshares = new Application_Model_DbTable_Revshares();

            $columns = [];
            $orders = [];
            $where = null;

            $start = (int)$this->getRequest()->getPost('start');
            $lenght = (int)$this->getRequest()->getPost('length');
            $search = $this->getRequest()->getPost('search')['value'];

            $checkquery = $sitesRevshares->select()
                ->setIntegrityCheck(false)
                ->from('sites_tags AS st', ['site_id'=>'st.site_id'])
                ->joinLeft('sites AS s', 's.SiteID=st.site_id', array())
                ->joinLeft('users_revshares_size AS ur', 's.SiteID=ur.site_id', array())
                ->where('ur.site_id IS NULL')
                ->where('st.site_id IS NOT NULL')
                ->where('s.status = 3')
                ->group('st.site_id');

            foreach($this->getRequest()->getPost('columns') as $column){
                $columns[$column['data']] = 's.' . $column['data'];
            }

            $dataquery = $sitesRevshares->select()
                ->setIntegrityCheck(false)
                ->from('sites AS s', $columns)
                ->joinLeft('users_revshares_size AS ur', 's.SiteID=ur.site_id', array())
                ->joinLeft('sites_tags AS st', 's.SiteID=st.site_id', array())
                ->where('ur.site_id IS NULL')
                ->where('st.site_id IS NOT NULL')
                ->where('s.status = 3')
                ->group('s.SiteID');

            if($search){
                foreach($this->getRequest()->getPost('columns') as $column){
                    if($column['searchable']=='true'){
                        $where .= $column['data'] . " LIKE'%$search%' OR ";
                    }
                }
            }

            if($this->getRequest()->getPost('order')){
                foreach($this->getRequest()->getPost('order') as $order){
                    $orders[] = $this->getRequest()->getPost('columns')[$order['column']]['data']." ".$order['dir'];
                }
            }

            $dataquery->order($orders);
            if($where){
                $where = substr_replace( $where, "", -3 );
                $dataquery->where($where);
                $checkquery->where($where);
            }

            $dataquery->limit($lenght, $start);

            $checkrequest = $sitesRevshares->fetchAll($checkquery);
            $datarequest = $sitesRevshares->fetchAll($dataquery);

            $result = [
                "draw"=>(int)$this->getRequest()->getPost('draw'),
                "recordsTotal"=>$checkrequest->count(),
                "recordsFiltered"=>$checkrequest->count(),
                "data"=>$datarequest->toArray()
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
}