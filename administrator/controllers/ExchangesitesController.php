<?php
class Administrator_ExchangesitesController extends Zend_Controller_Action
{
    public function init() {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('admin');
        
        $this->view->headTitle('Exchange Sites');
    }
    
    public function indexAction() {
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->nav = 'exchangesites';
    	
    	$exchangeSitesModel = new Application_Model_DbTable_ExchangeSites();
    	 
    	$select = $exchangeSitesModel->select();    
		
    	$select->from("exchange_sites");
    	$select->columns(array("`exchange_sites`.`site_channel_(primary)` AS category","`exchange_sites`.`site_channel_(primary)` AS name"));
    	$select->group("`exchange_sites`.`site_channel_(primary)`");
    	    	
    	$categories = $exchangeSitesModel->fetchAll($select);
    	
    	$this->view->categories = $categories;
    	
    	$cols = $exchangeSitesModel->info(Zend_Db_Table_Abstract::COLS);
    	
    	 
    	$countries = array();
    	foreach ($cols as $col):
    		if (is_numeric(strpos($col, "monthly_imps_"))):
    			$countries[$col] = substr($col,13); 
    		endif;
    	endforeach;
    	 
    	asort($countries);
    	
    	$this->view->countries = $countries;
    	 
    }
    
    public function viewAction(){
    	
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->setLayout('modal');
    	 
    	$id = $this->getRequest()->getParam("id");
    	if ($id):
    	
	    	$exchangeSitesModel = new Application_Model_DbTable_ExchangeSites();
	    	
	    	$exchangeSites = $exchangeSitesModel->find($id);
	    	
	    	if (count($exchangeSites)==1):
	    	
		    	$meta = array("id","date_created","created_by","date_updated","updated_by","deleted","date_deleted","deleted_by");
		    	$site = $exchangeSites->current();
		    	$data = array();
		    	
	    		foreach ($site->toArray() as $key=>$value):
	    		
	    			if (!in_array($key, $meta)):
	    			
	    				$data[$key] = $value;
	    			
	    			endif;
	    		
	    		endforeach;
	    	
	    		$data['category'] = $data['site_channel_(primary)'];
	    		$this->view->data = $data;
	    	
	    	endif;
    	
    	endif;
    	
    }
    
    public function updateAction(){

    	$response = array();
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	 
    	$exchangeSitesModel = new Application_Model_DbTable_ExchangeSites();
    	 
    	$sites = $exchangeSitesModel->fetchAll();
    	 
    	foreach ($sites as $site):
	
    		try {
	    		$data = file_get_contents("http://data.alexa.com/data?cli=10&dat=snbamz&url=".$site->site_url);
	    		$xml = new SimpleXMLElement($data);
	    		$popularity = $xml->xpath("//POPULARITY");
	    		$rank = (string)$popularity[0]['TEXT'];
	    		 
	    		$site->alexa_rank = $rank;
	    		$site->save();
	    		
	    		$response[$site->site] =  $site->alexa_rank;
	    		
	    	} catch (Exception $e){
	    		
	    		$response[$site->site] =  $e->getMessage();

	    	}
    	endforeach;
    	
    	$this->getResponse()->setBody(Zend_Json::encode($response))->setHeader('content-type', 'application/json', true);
    }
    
    public function dataAction(){
    	
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	
    	$exchangeSitesModel = new Application_Model_DbTable_ExchangeSites();
    	
    	$select = $exchangeSitesModel->select()->setIntegrityCheck(false);
    	
    	$country = $this->getRequest()->getParam("country");
    	if ($country):
	    	$columns = array("*", "category"=> "es.`site_channel_(primary)`",
	    			"avg_monthly_impressions" => "FORMAT(es.$country,0)",
	    			"avg_rtb_clearing_price" => "FORMAT(es.avg_rtb_clearing_price,2)"
	    	);    		
    	else :
	    	$columns = array("*", "category"=> "es.`site_channel_(primary)`",
	    			"avg_monthly_impressions" => "FORMAT(es.avg_monthly_impressions,0)",
	    			"avg_rtb_clearing_price" => "FORMAT(es.avg_rtb_clearing_price,2)"
	    	);    	
    	endif;
    	
    	$select->from(array("es"=>"exchange_sites"), $columns);
    	 
    	$status = $this->getRequest()->getParam("status");
    	
    	if ($status == "pending"):
		   	$select->where("es.approved = 0 AND es.denied=0");
    	 
		elseif ($status == "approved"):
			$select->where("es.approved = 1 AND es.denied=0");
		
		elseif ($status == "denied") :
			$select->where("es.approved = 0 AND es.denied=1");
		endif;
		
		$sSearch = $this->getRequest()->getParam("sSearch");
		
		if (strlen($sSearch)>=3):
			$select->where("es.site LIKE '%".$sSearch."%' OR es.site_url LIKE '%".$sSearch."%'");
		endif;
		
		$category = $this->getRequest()->getParam("category");
		if ($category):
			$select->where("es.`site_channel_(primary)` = ?", $category);
		endif;
		
		$sSortDir_0 = $this->getRequest()->getParam("sSortDir_0");
		
		$iSortCol_0 = (int)$this->getRequest()->getParam("iSortCol_0");
		
		if (is_numeric($iSortCol_0)):
			switch ($iSortCol_0):
		    	case 0 :
					$select->order("es.id ". $sSortDir_0);
					break;
		    	case 1 :
		    		$select->order("es.site ". $sSortDir_0);
		    		break;
		    	case 2 :
		    		$select->order("es.site_url ". $sSortDir_0);
		    		break;
		    	case 3 :
		    		$select->order("category ". $sSortDir_0);
		    		break;
		    	case 4 :
		    		if ($sSortDir_0 == 'asc'):
		    			$select->where("es.alexa_rank <> 0 ");
		    		endif;
		    		$select->order("es.alexa_rank ". $sSortDir_0);
		    		break;
		    	case 5 :
		    		if ($country):
		    			$select->order("CAST(es.$country AS UNSIGNED) ". $sSortDir_0);
		    		else:
		    			$select->order("CAST(es.avg_monthly_impressions AS UNSIGNED) ". $sSortDir_0);
		    		endif;
		    		break;
		    	case 6 :
		    		$select->order("CAST(es.avg_rtb_clearing_price AS DECIMAL(5,2)) ". $sSortDir_0);
		    		break;
		    endswitch;
		endif;
    	
    	$pendings = $exchangeSitesModel->fetchAll($select);
    	
    	$sEcho = (int)$_GET['sEcho'];
    	
    	$output = array(
    			"select" => $select->__toString(),
    			"sEcho" => $sEcho++,
    			"iTotalRecords" => count($pendings),
    			"iTotalDisplayRecords" => count($pendings),
    			"sColumns" => 7,
    			"aaData" =>	array()
    	);    	
    	
    	if (count($pendings)>0):
    	
	    	$select->limit((int)$_GET['iDisplayLength'],(int)$_GET['iDisplayStart']);
	    	
	    	$select->assemble();
	    	
	    	$pendings = $exchangeSitesModel->fetchAll($select);
	    	 
	    	$aaData = array();
	    	 
	    	foreach ($pendings as $pending):
	    	 
		    	$data = array();
		    	$data[0] = $pending->id;
		    	$data["id"] = $pending->id;
		    	$data[1] = $pending->site;
		    	$data["site"] = $pending->site;
		    	$data[2] = $pending->site_url;
		    	$data["site_url"] = $pending->site_url;
		    	$data[3] = $pending->alexa_rank;
		    	$data["alexa_rank"] = $pending->alexa_rank;
		    	$data[4] = $pending->category;
		    	$data["category"] = $pending->category;
		    	$data[5] = $pending->avg_monthly_impressions;
		    	$data["avg_monthly_impressions"] = $pending->avg_monthly_impressions;
		    	$data[6] = $pending->avg_rtb_clearing_price;
		    	$data["avg_rtb_clearing_price"] = $pending->avg_rtb_clearing_price;
		    	$data[7] = $pending->id;
		    	$data[8] = $pending->id;
		    	$data[9] = $pending->id;
		    	$aaData[] = $data;
	    	
	    	endforeach;    	
	    	
	    	$output['aaData'] = $aaData;
    	
    	endif;
    	
        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    	    	
    }
    
    protected function _rowAction($approved, $denied){
    	
    	$id = (int)$this->getRequest()->getParam("id");
    	
    	$response = array();
    	
    	if ($id):
	    	$exchangeSitesModel = new Application_Model_DbTable_ExchangeSites();
	    	
	    	$exchangeSites = $exchangeSitesModel->find($id);
	    	
	    	if (count($exchangeSites)==1):
		    	$site = $exchangeSites->current();
		    	
		    	try {
		    	
		    		$site->approved = $approved;
		    		$site->denied = $denied;
		    		$site->save();
		    	
		    		$siteName = $site->site;
		    	
		    		$response['error'] = false;
		    		$response['data'] = "$siteName " . (($approved) ? "approved" : (($denied) ? "denied" : "pending"));
		    	
		    	}catch (Exception $e){
		    	
		    		$response['error'] = true;
		    		$response['data'] = $e->getMessage();
		    	}
		    	
	    	
	    	else :
	    	
		    	$response['error'] = true;
		    	$response['data'] = "Site not found";
		    	
	    	endif;
	    	
	    else :
	    	 
	    	$response['error'] = true;
	    	$response['data'] = "No id";
	    	 
    	endif;

    	return $response;
    }
    
    public function pendingAction(){
    	 
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(); 
    	
    	$response = $this->_rowAction(0,0);
    	 
    	$this->getResponse()->setBody(Zend_Json::encode($response))->setHeader('content-type', 'application/json', true);
    	 
    }
    
    public function approveAction(){
    	
    	$this->_helper->layout()->disableLayout(); 
    	$this->_helper->viewRenderer->setNoRender(); 
    	
    	$response = $this->_rowAction(1,0);
    	 
        $this->getResponse()->setBody(Zend_Json::encode($response))->setHeader('content-type', 'application/json', true);
    }
    
    public function approvalAction(){
    	
    	$layout = Zend_Layout::getMvcInstance();
    	$layout->setLayout('modal');
    	
    	$id = $this->getRequest()->getParam("id");
    	if ($id):
    	 
	    	$exchangeSitesModel = new Application_Model_DbTable_ExchangeSites();
	    	
	    	$exchangeSites = $exchangeSitesModel->find($id);
	    	
	    	if (count($exchangeSites)==1):
	    	
		    	$site = $exchangeSites->current();
	    	
    			$this->view->headTitle(" - " .$site->site);
	    		
	    		$data = $site->toArray();
	    		
	    		$url = parse_url($site->site_url);
	    	
	    		$data['category'] = $data['site_channel_(primary)'];
	    		$data['domain'] = $url['host'];
	    		 
		    	$this->view->data = $data;
		    	
	    	endif;
    	 
    	endif;    	
    	
    }
    
    
    public function denyAction(){
    	 
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	$response = $this->_rowAction(0,1);
    	    	 
        $this->getResponse()->setBody(Zend_Json::encode($response))->setHeader('content-type', 'application/json', true);
    }    
    
    
}
?>
