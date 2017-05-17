<?php
/**
 * Description of SizesRequestsController
 *
 * @author nik
 */
class Administrator_SizesRequestsController extends Zend_Controller_Action 
{
    protected $_layout;

    public function init()
    {
        $this->_layout = Zend_Layout::getMvcInstance();
        $this->_layout->setLayout('admin_v2');
    }

    public function indexAction()
    {

    }

    public function getAjaxAction()
    {
    	$this->_helper->layout()->disableLayout(); // disable layout
    	$this->_helper->viewRenderer->setNoRender(); // disable view rendering
        if($this->getRequest()->isPost()){
            $sizesRequestModel = new Application_Model_DbTable_SizesRequest();

            $columns = [];
            $orders = [];
            $where = null;
            $tableName = "sizes_request";
            $start = (int)$this->getRequest()->getPost('start');
            $lenght = (int)$this->getRequest()->getPost('length');
            $search = $this->getRequest()->getPost('search')['value'];

            $checkquery = $sizesRequestModel->select()
                ->from($tableName, array("num"=>"COUNT(*)"))
                ->where('status=1');

            $dataquery = $sizesRequestModel->select()
                ->from('sizes_request AS sr', [
                    'site_id'=>'sr.site_id',
                    'SiteName'=>'s.SiteName',
                    'email'=>'u.email',
                    'created_at'=>'sr.created_at',
                    'size_name'=>'sz.name',
                    'network_current'=>'nc.name',
                    'network_submited'=>'ns.name',
                    'id'=>'sr.id'
                ])
                ->join('sites AS s', 's.SiteID=sr.site_id', [])
                ->join('users AS u', 'u.id=s.PubID', [])
                ->join('sizes AS sz', 'sz.id=sr.size_id', [])
                ->join('networks AS nc', 'nc.id=sr.network_current_id', [])
                ->join('networks AS ns', 'ns.id=sr.network_requested_id', [])
                ->setIntegrityCheck(false);

            $dataquery->where('sr.status=1');

            if($search){
                foreach($this->getRequest()->getPost('columns') as $column){
                    if($column['searchable']){
                        $where .= $column['data'] . " LIKE'%$search%' OR ";
                    }
                }
            }

            if($this->getRequest()->getPost('order')){
                foreach($this->getRequest()->getPost('order') as $order){
                    $orders[] =  $this->getRequest()->getPost('columns')[$order['column']]['data']." ".$order['dir'];
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


            $checkrequest = $sizesRequestModel->fetchRow($checkquery);
            $datarequest = $sizesRequestModel->fetchAll($dataquery)->toArray();

            $result = [
                "draw"=>(int)$this->getRequest()->getPost('draw'),
                "recordsTotal"=>$checkrequest["num"],
                "recordsFiltered"=>$checkrequest["num"],
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

    public function enableAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        $id = (int)$this->_getParam('id');
        
        if($id){
            $sizesRequestModel = new Application_Model_DbTable_SizesRequest();
            $tableSite = new Application_Model_DbTable_Sites();
            $sizesModel = new Application_Model_DbTable_Sizes();

            $dataRequest = $sizesRequestModel->fetchRow([
                $sizesRequestModel->getAdapter()->quoteInto('id = ?', $id)
            ]);

            if(!$dataRequest)
                $this->_redirect('/administrator/sizes-requests');

            $dataRequest->status = 3;
            $dataRequest->save();

            $sql = $tableSite->select()->setIntegrityCheck(false)
                ->from('sites AS s', array(
                    'PubID'=>'s.PubID',
                    'SiteID'=>'s.SiteID',
                    'SiteName'=>'s.SiteName'
                ))
                ->join("users AS u", "s.PubID=u.id", array(
                    'email'=>'u.email',
                    'name'=>'u.name'
                ))
                ->where("s.SiteID='".$dataRequest->site_id."'");
            $dataSite = $tableSite->fetchRow($sql);

            $sql = $sizesModel->select()->setIntegrityCheck(false)
                ->from('display_size AS ds', array(
                    'name'=>'ds.name',
                    'width'=>'ds.width',
                    'height'=>'ds.height',
                    'file_name'=>'ds.file_name',
                    'description'=>'ds.description'
                ))
                ->where("ds.id='".$dataRequest->size_id."'");
            $dataSize = $tableSite->fetchRow($sql);

            if($dataRequest->size_id==12){
                $title = 'Your Video Tags for '.$dataSite->SiteName.' are Ready!';
                $message ="<p>This email is to notify you that the video tags you've requested for ".$dataSite->SiteName." are now ready.</p>";
                $message.='<p>For your convenience, you can find the video tag attached to this email.  We recommend display the ad site-wide in order to maximize your revenue potential.</p>';
                $message.='<p>If you have any questions, please feel free to respond to this email.</p>';
                $message.='<p>Regards,<br />The MadAds Media Publisher Team</p>';
            }else{
                $title = 'Your '.$dataSize->name.' Tags for '.$dataSite->SiteName.' are Ready!';
                $message = "Hello,";
                $message.="<p>This email is to notify you that your request for the ".$dataSize->name." Tags has been approved.</p>";
                $message.='<p>For your convenience, we\'ve attached the ad tag to this email.</p>';
                $message.='<p>Regards,<br />The MadAds Media Publisher Team</p>';
            }

            $mail = new Zend_Mail();
            $mail->setFrom(Application_Model_DbTable_Registry_Setting::getByName('admin_email'), Application_Model_DbTable_Registry_Setting::getByName('admin_name'));
            $mail->addTo($dataSite->email, $dataSite->name);
            $mail->setSubject($title);
            $mail->setBodyHtml($message.'');
            
            $txt = "<!-- MadAdsMedia.com Asynchronous Ad Tag For ".$dataSite->SiteName." -->\n";
            $txt.="<!-- Size: ".$dataSize->name." -->\n";
            $txt.="<script src=\"http://ads-by.madadsmedia.com/tags/".$dataSite->PubID."/".$dataSite->SiteID."/async/".$dataSize->file_name.".js\" type=\"text/javascript\"></script>\n";
            $txt.="<!-- MadAdsMedia.com Asynchronous Ad Tag For ".$dataSite->SiteName." -->";

            $attachment = new Zend_Mime_Part($txt);
            $attachment->type = 'text/plain';
            $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
            $attachment->encoding = Zend_Mime::ENCODING_BASE64;
            $attachment->filename = $dataSite->SiteName.'-'.$dataSize->file_name.".txt"; // name of file

            $mail->addAttachment($attachment);

            if(APPLICATION_ENV=='production')
                $mail->send();

            $this->_redirect('/administrator/tags/edit/site/'.$dataRequest->site_id);
        }
        
        $this->_redirect('/administrator/sizes-requests');
    }
    
    public function disableAction()
    {
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering

        $id = (int)$this->_getParam('id');
        
        if($id){
            $sizesRequestModel = new Application_Model_DbTable_SizesRequest();
            $sizesRequestModel->update(['status'=>2],$sizesRequestModel->getAdapter()->quoteInto('id = ?', $id));
        }
        
        $this->_redirect('/administrator/sizes-requests');
    }    
}