<?php

class BuyController extends Zend_Controller_Action
{
	public function indexAction()
	{
		$list = array(
			'valueclick'		=> 'ValueClick',
			'advertising.com'	=> 'Advertising',
			'media.net'			=> 'Media',
			'realmedia'			=> 'UnderDog',
			'audiencescience'	=> 'AudienceScience'
		);

		$network = $this->_getParam('network');

		if (!array_key_exists($network, $list)) {
			$this->view->error = true;
			return false;
		}

		$model	= new Application_Model_DbTable_Optimization_CaseSite();
		$this->view->network = $network;
		$this->view->showNetwork = $list[$network];
		$this->view->status = $this->_getParam('status');
		$this->view->data	= $model->getForNetwork($list[$network],$this->_getParam('status'));
	}

	public function ajaxOptionAction()
	{
	    $network = $this->_getParam('network');
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$output = array('res' => false);

		switch ($this->_getParam('type')) {
			case 'accept':	$field = 'accept_pending'; break;
			case 'deny':	$field = 'deny_pending'; break;
			default: $status = 2; break;
		}

		$sid	= (int) $this->_getParam('sid');
		$model	= new Application_Model_DbTable_Sites();
		$site	= $model->find($sid);

		if (isset($site[0]) && isset($field)) {
			$output['val'] = $site[0]->$field == 0 ? 1 : 0;

			    $sql_array = array();
			switch($field)
			{
			    case 'accept_pending':
			        {
			             if($output['val'] == 1)
			             {
			                 $sql_array = array(
			                     'accept_pending' => 1,
			                     'deny_pending' => 0
			                 );
			                 $status = 1;
			             }
			             else
			             {
			                 $sql_array = array(
			                     'accept_pending' => 0,
			                     'deny_pending' => 0
			                 );
			                 $status = 2;
			             }
			        }
			        break;
			    case "deny_pending":
			        {
			            if($output['val'] == 1)
			            {
			                $sql_array = array(
			                    'accept_pending' => 0,
			                    'deny_pending' => 1
			                );
			                $status = 3;
			            }
			            else
			            {
			                $sql_array = array(
			                    'accept_pending' => 0,
			                    'deny_pending' => 0
			                );
			                $status = 1;
			            }
			        }
			        break;
			}
			$status_model	= new Application_Model_DbTable_Optimization_CaseSite();
			$status_model->updateStatus($sid, $network, $status);
			if ($model->update($sql_array, 'SiteID = '.$sid)) {
				$output['res'] = true;
			}
		}

		$this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
	}

}