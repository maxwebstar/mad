<?php
class Administrator_NewsController extends Zend_Controller_Action
{
    protected $_layout;

    public function init()
    {
        $this->_layout = Zend_Layout::getMvcInstance();
        $this->_layout->setLayout('admin');
        $this->_layout->nav = 'user_news';
    }
    
    public function indexAction()
    {
    	$this->view->time = date("m/d/Y");
    	$this->view->csrf = $this->_helper->Csrf->set_token();
    }
    
    public function ajaxGetAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	$model_news = new Application_Model_DbTable_UserNews();
    	$result = $model_news->getNews($this->_getAllParams());
    	$this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
    }
    
    public function editAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	$model_news = new Application_Model_DbTable_UserNews();
    	$data = $model_news->getCurrentNews($this->_getParam('itemId'));
    	$result = array('error' => 0);
    	if($data)
    	{
    		$data['date'] = date('m/d/Y',strtotime($data['date']));
    		$result['data'] = $data;	
    	}
    	else
    		$result['error'] = 1;
    	$this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);
    }
    
    public function addAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
		$title = $this->_helper->Xss->xss_clean($this->_getParam('news_title'));
		//$text = $this->_helper->Xss->xss_clean($this->_getParam('news_text'));
		$text =  $this->_getParam('news_text');
		$date = $this->_helper->Xss->xss_clean($this->_getParam('date'));
		$published = $this->_helper->Xss->xss_clean($this->_getParam('published'));
		$csrf = $this->_helper->Xss->xss_clean($this->_getParam('csrf'));
		$position_left = $this->_helper->Xss->xss_clean($this->_getParam('position_left'));
		$position_above = $this->_helper->Xss->xss_clean($this->_getParam('position_above'));
		$result = array('error' => 0);
		if(!$title)
			$result['error'] = 1;
		if(!$text)
			$result['error'] = 2;
		if(!$date OR !strtotime($date))
			$result['error'] = 3;
		if(!$this->_helper->Csrf->check_token($csrf))
			$result['error'] = 5;
		if($result['error'] == 0)
		{
			$data = array(
				'title' => $title,
				'text' => $text,
				'date' => date('Y-m-d',strtotime($date)),
				'access' => $this->_getParam('access'),
				'published' => ($published ? 1: 0),
				'position_left' => $position_left ? 1 : 0,
				'position_above' => $position_above ? 1 : 0
			);
			$model_news = new Application_Model_DbTable_UserNews();
			$exising_news = $this->_getParam('newsId');
			if($exising_news)
				$result['error'] = $model_news->SaveNews($data,$this->_getParam('newsId'));
			else
				$result['error'] = $model_news->addNews($data);
		}	
		$this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);		
    }
    
    public function deleteAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	$csrf = $this->_helper->Xss->xss_clean($this->_getParam('csrf'));
    	$model_news = new Application_Model_DbTable_UserNews();
    	$result = array('error' => 0);
    	if(!$model_news->deleteNews($this->_getParam('id')))
    		$result['error'] = 1;
    	if(!$this->_helper->Csrf->check_token($csrf))
			$result['error'] = 2;
    	$this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);   	 
    }
}