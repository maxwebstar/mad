<?php
class Administrator_PpAccountsController extends Zend_Controller_Action{

    public function init()
    {
        $this->_layout = Zend_Layout::getMvcInstance();
        $this->_layout->setLayout('admin_v2');
    }


    public function indexAction(){

    }

    public function ajaxGetAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        if($this->getRequest()->isPost()){
            $accountsModel = new Application_Model_DbTable_PpAccounts();

            $orders = [];
            $where = null;

            $start = (int)$this->getRequest()->getPost('start');
            $lenght = (int)$this->getRequest()->getPost('length');
            $search = $this->getRequest()->getPost('search')['value'];

            $checkquery = $accountsModel->select()
                ->from('pp_accounts', ['id']);

            $dataquery = $accountsModel->select()
                ->from('pp_accounts', [
                    'id','user_name','acc_num','token','created_at','updated_at'
                ]);


            if($search){
                foreach($this->getRequest()->getPost('columns') as $column){
                    if($column['searchable']=='true'){
                        $where .= $column['name'] . " LIKE'%$search%' OR ";
                    }
                }
                $where = substr_replace( $where, "", -3 );
                $dataquery->where($where);
                $checkquery->where($where);
            }

            if($this->getRequest()->getPost('order')){
                foreach($this->getRequest()->getPost('order') as $order){
                    if($this->getRequest()->getPost('columns')[$order['column']]['name'])
                        $orders[] = $this->getRequest()->getPost('columns')[$order['column']]['name']." ".$order['dir'];
                }
                $dataquery->order($orders);
            }

            $dataquery->limit($lenght, $start);

            $datarequest = $accountsModel->fetchAll($dataquery);
            $checkrequest = $accountsModel->fetchAll($checkquery);

            $result = [
                "draw"=>(int)$this->getRequest()->getPost('draw'),
                "recordsTotal"=>$checkrequest->count(),
                "recordsFiltered"=>$checkrequest->count(),
                "test"=>$dataquery->__toString(),
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

    public function addAction(){
        $form = new Application_Form_PpAccounts();

        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                $accountsModel = new Application_Model_DbTable_PpAccounts();

                $dataAccount = $accountsModel->createRow();
                $dataAccount->setFromArray(array(
                    'user_name' => $form->getValue('user_name'),
                    'acc_num' => $form->getValue('acc_num'),
                    'token' => $form->getValue('token'),
                    'created_at' => date("Y-m-d H:i:s")
                ));
                $dataAccount->save();

                $this->_redirect('/administrator/pp-accounts');
            }else{
                $this->view->formErrors = $form->getMessages();
                $this->view->formValues = $form->getValues();
            }
        }

        $this->view->action = 'Add new';
    }

    public function editAction(){
        $form = new Application_Form_PpAccounts();
        $accountsModel = new Application_Model_DbTable_PpAccounts();

        $id = (int)$this->getRequest()->getParam('id');

        $dataAccount = $accountsModel->fetchRow("id=".$id);

        if(!$dataAccount)
            $this->_redirect('/administrator/pp-accounts');

        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){

                $dataAccount->user_name = $form->getValue('user_name');
                $dataAccount->acc_num = $form->getValue('acc_num');
                $dataAccount->token = $form->getValue('token');
                $dataAccount->updated_at = date("Y-m-d H:i:s");
                $dataAccount->save();

                $this->_redirect('/administrator/pp-accounts');
            }else{
                $this->view->formErrors = $form->getMessages();
                $this->view->formValues = $form->getValues();
            }
        }else{
            $this->view->formValues = $dataAccount;
        }

        $this->view->action = 'Edit';

        $this->render('add');
    }

    public function delAction(){
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        $accountsModel = new Application_Model_DbTable_PpAccounts();

        $id = (int)$this->getRequest()->getParam('id');

        if(!$id)
            $this->_redirect('/administrator/pp-accounts');

        $accountsModel->delete("id=".$id);

        $this->_redirect('/administrator/pp-accounts');
    }
}