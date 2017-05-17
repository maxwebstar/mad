<?php
class AjaxController extends Zend_Controller_Action
{
    
    public function init() {
                
        $this->_helper
                ->AjaxContext
                ->addActionContext('index', 'json')
                ->initContext('json');
        
        $this->_helper
                ->AjaxContext
                ->addActionContext('check-pdf', 'json')
                ->initContext('json');
        
        $this->_helper
                ->AjaxContext
                ->addActionContext('check-site-cpm', 'json')
                ->initContext('json');
        
        $this->_helper
                ->AjaxContext
                ->addActionContext('check-minimum-cpm', 'json')
                ->initContext('json');
        
        $this->_helper
                ->AjaxContext
                ->addActionContext('save-minimum-cpm', 'json')
                ->initContext('json');
        
        $this->_helper
                ->AjaxContext
                ->addActionContext('cancel-minimum-cpm', 'json')
                ->initContext('json');                            
    }
    
    public function indexAction()
    {                         
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender();
        
//        $_SERVER['HTTP_HOST'] == 'media' ? include_once '/../library/Pdf/mpdf.php' : include_once '/home/madads/library/Pdf/mpdf.php' ;
        
        $dataAuth = Zend_Auth::getInstance()->getIdentity();
                            
        $tablePdf = new Application_Model_DbTable_Pdf_Entity();
            
        $sql = $tablePdf->select()->where('PubID = ?', $dataAuth->id);
        
        $existPdf = $tablePdf->fetchRow($sql);
        
        if(count($existPdf)) $existPdf->delete(); 
                                
        $taxClassification = $this->_getParam('type') == '_llc' ? $this->_getParam('_tax_classification') : null;
        
        $date = date('Y-m-d H:i:s');
        $ip = My_Advanced::GetRealIp();
        
        $digitalSignature = md5($this->_getParam('_digital_signature').$ip.$date);
        
//        $fileName = md5($dataAuth->id). '.pdf';
//        $file = 'pdf/file/' . $fileName;
//
//          $css = file_get_contents('pdf/template/style.css'); 
//          $html = file_get_contents('pdf/template/index.html');
//
//           $content = 
//           '<tr>
//                <td colspan="1" rowspan="8" class="vertical-right"><img src="http://content.screencast.com/users/brandonsmith89/folders/Jing/media/aa9bdc15-d150-473b-87de-6cb90d352039/2012-06-22_1839.png"/></td>
//                <td colspan="14" class="horizontal-botton">
//                    <div style="clear: both; padding-left: 6px; font-size: 7pt;"> Name (as shown on your income tax return) </div>
//                    <div style="clear: both; padding-left: 6px; font-size: 12pt;">'.$this->_getParam('_name').'</div>
//                </td>
//            </tr>  
//            <tr>
//                <td colspan="14" class="horizontal-botton">
//                    <div style="clear: both; padding-left: 6px; font-size: 7pt;"> Business name/disregard entity name, if different from above </div>
//                    <div style="clear: both; padding-left: 6px; font-size: 12pt;">'.$this->_getParam('_business_name').'</div>
//                </td>
//            </tr>
//
//            <tr>
//                <td colspan="12" style="padding-left: 6px;"> 
//                    <div style="font-size: 7pt;"> Check appropriate box for federal tax classification: </div>
//                    <div style="font-size: 8pt;">
//                        <span>';
//           $content .= $this->_getParam('type') == '_sole_proprietor' ? '<img src="/images/checked.gif" />' : '<input type="checkbox" />';
//           $content .= 'Individual/sole proprietor
//                        </span>
//
//                        <span style="padding-left: 26px;">';
//           $content .= $this->_getParam('type') == '_c_corporation' ? '<img src="/images/checked.gif" />' : '<input type="checkbox" />';
//           $content .= ' C Corporation
//                        </span>
//
//                        <span style="padding-left: 26px;">';
//           $content .= $this->_getParam('type') == '_s_corporation' ? '<img src="/images/checked.gif" />' : '<input type="checkbox" />';
//           $content .= ' S Corporation
//                        </span>
//
//                        <span style="padding-left: 26px;">';
//           $content .= $this->_getParam('type') == '_partnership' ? '<img src="/images/checked.gif" />' : '<input type="checkbox" />';
//           $content .= ' Partnership
//                        </span>
//
//                        <span style="padding-left: 26px;">';
//           $content .= $this->_getParam('type') == '_trust_estate' ? '<img src="/images/checked.gif" />' : '<input type="checkbox" />';
//           $content .= ' Trust/estate
//                        </span>
//                    </div>
//                </td>
//                <td colspan="2" rowspan="3" class="vertical-left horizontal-botton">
//                    <div style="font-size: 8pt;">';
//           $content .= $this->_getParam('type') == '_exempt_payee' ? '<img src="/images/checked.gif" />' : '<input type="checkbox" />';
//           $content .= ' Exempt Payee
//                    </div>
//                </td>
//            </tr>    
//            <tr>
//                <td colspan="12" style="padding-left: 6px; font-size: 8pt; padding-top: 8px;">
//                    <div>';
//           $content .= $this->_getParam('type') == '_llc' ? '<img src="/images/checked.gif" />' : '<input type="checkbox" />';
//           $content .= ' Limited liability company. Enter the tax classification (C=C Corporation, S=S Corporation, P=partnership)'.$taxClassification.' 
//                    </div>     
//                </td>
//           </tr>
//
//           <tr>
//                <td colspan="12" class="horizontal-botton" style="padding-left: 6px; font-size: 8pt; padding-top: 16px; padding-bottom: 5px;">
//                    <div>
//                          <input type="checkbox"/>Other (see instructions) {%OtherTaxClass%}
//                    </div>
//                </td>
//            </tr>
//
//            <tr>
//                <td colspan="9" class="horizontal-botton">
//                    <div style="clear: both; padding-left: 6px; font-size: 7pt;"> Address (number, street, and apt. or suite no.) </div>
//                    <div style="clear: both; padding-left: 6px; font-size: 12pt;">'.$this->_getParam('_address').'</div>
//                </td>
//                <td colspan="5" rowspan="2" class="vertical-left horizontal-botton vertical-align-top">
//                    <div style="clear: both; padding-left: 6px; font-size: 7pt;"> Requesters name and address (optional) </div>
//                    <div style="clear: both; padding-left: 6px; font-size: 12pt;">'.$this->_getParam('_requester').'</div>
//                </td>
//            </tr>
//            <tr>
//                <td colspan="9" class="horizontal-botton">
//                   <div style="clear: both; padding-left: 6px; font-size: 7pt;"> City, state, and ZIP code </div>
//                   <div style="clear: both; padding-left: 6px; font-size: 12pt;">'.$this->_getParam('_city_state_zip').'</div>  
//                </td>
//            </tr>
//
//            <tr>
//                <td colspan="14" class="horizontal-botton">
//                    <div style="clear: both; padding-left: 6px; font-size: 7pt;"> List account number(s) here (optional) </div>
//                    <div style="clear: both; padding-left: 6px; font-size: 12pt;">'.$this->_getParam('_account_numbers').'</div>  
//                </td>
//            </tr>
//
//
//            <!--Part I-->
//
//
//            <tr>
//                <td colspan="1" class="black horizontal-botton">Part I</td>
//                <td colspan="14" class="horizontal-botton" style="padding-left: 29px;"><div style="font-weight: bold; font-size: 10pt;">Taxpayer Identification Number (TIN)</div></td>
//            </tr>
//            <tr>
//                <td colspan="10" rowspan="2">
//                     <div style="font-size: 8pt; padding-top: 4px; margin-bottom: 0px;">
//                            Enter your TIN in the appropriate box. The TIN provided must match the name given on the "Name" line to avoid backup withholding. For individuals, this is your social security number (SSN). However, for a resident alien, sole proprietor, or disregarded entity, see the Part I instructions on page 3. For other entities, it is your employer identification number (EIN). If you do not have a number, see <span style="font-style: italic;">How to get a TIN</span> on page 3.
//                     </div>    
//                </td>
//                <td colspan="5" class="vertical-align-top horizontal-botton vertical-left vertical-right" style="height: 15px;">
//                      <div style="padding-left: 6px; font-weight: bold; font-size: 8pt;">Social security number</div>
//                 </td>
//            </tr>
//            <tr>
//                <td colspan="5" class="vertical-align-top">
//                    <div style="padding-top: 2px; left: -2px; position: relative">
//                          <input type="text" style="width: 150px;" value="'.$this->_getParam('_ssn').'"/>
//                    </div>
//                </td>
//            </tr>    
//
//            <tr>
//                <td colspan="10" rowspan="2" class="vertical-align-top horizontal-botton" style="padding-top: 15px;">
//                    <div style="font-size: 8pt;">
//                        <span style="font-weight: bold;">Note.</span> If the account is in more than one name, see the chart on page 4 for guidleines on whose number to enter.
//                    </div> 
//                </td>
//                <td colspan="5" class="horizontal-botton horizontal-top vertical-left vertical-right vertical-align-top" style="height: 15px;">
//                        <div style="padding-left: 6px; font-weight: bold; font-size: 8pt;">Employee identification number</div>
//                </td>
//            </tr>
//            <tr>
//                <td colspan="5" class="horizontal-botton" style="padding-bottom: 15px;">
//                    <div style="padding-top: 2px; left: -2px; position: relative">
//                          <input type="text" style="width: 150px;" value="'.$this->_getParam('_ein').'"/>
//                    </div>
//                </td>
//            </tr>    
//
//           <!--Part II-->
//
//            <tr>
//                <td colspan="1" class="black horizontal-botton">Part II</td>
//                <td colspan="14" class="horizontal-botton" style="padding-left: 29px;"><div style="font-weight: bold; font-size: 10pt;">Sertification</div></td>
//            </tr>
//
//            <tr>
//                <td colspan="15" style="padding-top: 4px; font-size: 8pt;">Under penalties of perjury, I certify that:</td>
//            </tr> 
//            <tr>
//                <td colspan="15" style="padding-top: 7px; font-size: 8pt;">1. The number shown on this form is my correct taxpayer identification number (or I am waiting for a number to be issued to me), and</td>
//            </tr> 
//            <tr>
//                <td colspan="15" style="padding-top: 7px; font-size: 8pt;">2. I am not subject to backup withholding because: (a) I am exempt from backup withholding, or (b) I have not been notified by the Internal Revenue Service (IRS) that I am subject to backup withholding as a result of a failure to report all interest or dividends, or (c) the IRS has notified me that I am no longer subject to backup withholding, and</td>
//            </tr> 
//            <tr>
//                <td colspan="15" style="padding-top: 7px; font-size: 8pt;">3. I am a U.S. citizen or other U.S. person (defined below).</td>
//            </tr> 
//            <tr>
//                <td colspan="15" style="padding-top: 7px; font-size: 8pt;">Certification instructions. You must cross out item 2 above if you have been notified by the IRS that you are currently subject to backup withholding because you have failed to report all interest and dividends on your tax return. For real estate transactions, item 2 does not apply. For mortgage interest paid, acquisition or abandonment of secured property, cancellation of debt, contributions to an individual retirement arrangement (IRA), and generally, payments other than interest and dividends, you are not required to sign the certification, but you must provide your correct TIN. See the instructions on page 4.</td>
//            </tr> 
//
//
//            <tr>
//                <td colspan="1" class="horizontal-botton horizontal-top vertical-right" style="font-size: 10pt; font-weight: bold; padding-bottom: 5px;">Sign Here</td>
//                <td colspan="2" class="horizontal-botton horizontal-top" style="padding: 5px 0px 5px 10px; font-weight: bold; font-size: 7pt;">Signature of<br /> U.S. person</td>
//                <td colspan="7" class="horizontal-botton horizontal-top" style="padding: 5px 0px 5px 0px; font-weight: bold; font-size: 7pt;"><input type="text" style="width: 250px;" value="'.$digitalSignature.'"/></td>
//
//                <td colspan="1" class="horizontal-botton horizontal-top" style="padding: 5px 0px 5px 10px; font-weight: bold; font-size: 7pt;">Date</td>
//                <td colspan="4" class="horizontal-botton horizontal-top" style="padding: 5px 0px 5px 0px; font-weight: bold; font-size: 7pt;"><input type="text" value="'.$this->_getParam('_date').'"/></td>
//            </tr>';
//
//            $html = str_replace("<tr><td>{%content%}</td></tr>", $content, $html);
//
//            $mpdf = new mPDF();
//
//            $mpdf->WriteHTML($css, 1); 
//            $mpdf->WriteHTML($html, 2); 
//
//            $mpdf->Output($file, 'F');
            
            $dataPdf = $tablePdf->createRow();
            $dataPdf->setFromArray(array(
                'PubID' => $dataAuth->id,
                'name' => $this->_getParam('_name'),
                'business_name' => $this->_getParam('_business_name'),
                'federal_tax' => $this->_getParam('type'),
                'federal_tax_classification' => $taxClassification,
                'address' => $this->_getParam('_address'),
                'city_state_zip' => $this->_getParam('_city_state_zip'),
                'account_number' => $this->_getParam('_account_numbers'),
                'requester_address' => $this->_getParam('_requester'),
                'ssn' => $this->_getParam('_ssn'),
                'ein' => $this->_getParam('_ein'),
                'digital_signature' => $digitalSignature,
                'date' => $date,
                'ip' => $ip,
                'file' => null,
            ));
            
            $resultDb = $dataPdf->save();
            
            if($resultDb){ $data = array('ip' => $ip, 'date' => $date);
                
                   $this->view->result = array('status' => $resultDb, 'data' => $data); 
            }else  $this->view->result = array('status' => 0);

    }
    
    
    public function checkPdfAction()
    {   
        
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender();
        
        $dataAuth = Zend_Auth::getInstance()->getIdentity();
        
        $tablePdf = new Application_Model_DbTable_Pdf_Entity();
        
        $sql = $tablePdf->select()->where('PubID = ?', $dataAuth->id);
        
        $dataPdf = $tablePdf->fetchRow($sql);
        
        if(count($dataPdf)){ 
            
            $data = array('name' => $dataPdf->name,
                          'business_name' => $dataPdf->business_name,
                          'federal_tax' => $dataPdf->federal_tax,
                          'federal_tax_classification' => $dataPdf->federal_tax_classification,
                          'address' => $dataPdf->address,
                          'city_state_zip' => $dataPdf->city_state_zip,
                          'account_number' => $dataPdf->account_number,
                          'requester_address' => $dataPdf->requester_address,
                          'ssn' => $dataPdf->ssn,
                          'ein' => $dataPdf->ein,
                          'ip' => $dataPdf->ip,
                          'date' => $dataPdf->date,
                          'digital_signature' => $dataPdf->digital_signature);
            
                 $this->view->result = array('status' => 1, 'data' => $data); 
        } else { $this->view->result = array('status' => 0); }
        
    }
    
    
    public function checkSiteCpmAction()
    {
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender();
        
        $tableSite = new Application_Model_DbTable_Sites();
        
        $dataSite = $tableSite->checkSiteCpm((int) $this->_getParam('SiteID'));
        
        if(count($dataSite)) $this->view->result = array('status' => 1, 'data' => $dataSite);
        else                 $this->view->result = array('status' => 0);
          
    }

    public function checkMinimumCpmAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        if(!$this->getRequest()->isPost())
            return false;

        $site_id = (int)$this->getRequest()->getPost('SiteID');

        if(!$site_id)
            return false;

        $sitesTagsModel = new Application_Model_DbTable_SitesTags();
        $cpmModel = new Application_Model_DbTable_Cpm_Minimum();

        $newDataSize = $sitesTagsModel->getSizesForTag($site_id,-1,'ds.id');
        $dataCpm = $cpmModel->getCpmSite($site_id);

        if($dataCpm){
            $cpmFileModel = new Application_Model_DbTable_Cpm_File();
            $dataFile = $cpmFileModel->getData($dataCpm['id']);

            $this->view->result = [
                'status'=>1,
                'data'=>$dataCpm,
                'file'=>$dataFile,
                'size'=>$newDataSize
            ];
        }else{
            $this->view->result = [
                'status'=>0,
                'size'=>$newDataSize
            ];
        }
    }
    
    public function saveMinimumCpmAction()
    {                
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender();
        
            $dataAuth = Zend_Auth::getInstance()->getIdentity();

            $tableSites = new Application_Model_DbTable_Sites();
            $tableFile = new Application_Model_DbTable_Cpm_File();
            $tableCpm = new Application_Model_DbTable_Cpm_Minimum();
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();

            $sqlSite = $tableSites->select()->setIntegrityCheck(false)
                               ->from(array('s' => 'sites'), 
                                      array('s.SiteName', 's.store_tag_url', 's.iframe_tags', 's.SiteID', 's.PubID', 's.factor_revshare'))
                               ->where('s.SiteID = ?', (int) $this->_getParam('SiteID'))
                               ->join(array('u' => 'users'),('s.PubID = u.id'),
                                      array('u.email', 'u.name', 'u.auto_min_cpm'))
                               ->join(array('t' => 'tags'),('t.site_id = s.SiteID'),
                                      array('t.type'));

            $dataSite = $tableSites->fetchRow($sqlSite);            
            
            if($dataSite->type==4 && $dataSite->auto_min_cpm==1)
                $generate = TRUE;
            else
                $generate = FALSE;
            
            $dataCpm = $tableCpm->createRow();
            $dataCpm->setFromArray(array(
                'PubID'  => $dataAuth->id,
                'SiteID' => (int) $this->_getParam('SiteID'),
                'cpm'    => $this->_getParam('cpm'),
                'estim_cpm'  => $this->_getParam('estim_cpm'),
                'estim_rate' => $this->_getParam('estim_rate'),
                'status' => $generate===TRUE ? 3 : 1,
                'created'=> date('Y-m-d')
            ));        

            $dataCpm->getPrevFloor();
            $dataCpm->createDynamic();
            $dataCpm->checkPath();
            
            if($generate===TRUE){ $prevDynamic = $dataCpm->deleteData();  }
                            else{ $dataCpm->deleteData(1); }

            $cpmID = $dataCpm->save(); 


            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";    
            $headers .= "From: MadadsMedia <support@madadsmedia.com>\r\n";                                    
            $to = 'support@madadsmedia.com';
            $title = 'New Floor Tag Requst For '.$dataSite->SiteName;
            $message = "This email is to notify you that there's a new floor tag request pending:<br><br>";
            $message.= "Website: <strong>".$dataSite->SiteName."</strong><br>";
            $message.= "New CPM: <strong>$ ".$dataCpm->cpm."</strong><br>";
            $message.= "Prev. CPM: <strong>$ ".$dataCpm->prev_cpm."</strong><br><br>";
            $message.= "<a href=\"http://madadsmedia.com/administrator/cpm/index/\">Go to floor tags admin.</a>";

            //if(APPLICATION_ENV == 'production'){ mail($to, $title, $message, $headers); }   

            $path = $dataCpm->getPath();
            
            $passbackArr = array();
            $flag_submitted_tag = false;
            foreach($this->_getParam('file') as $key => $iter){

                $dataFile = array('PubID'          => $dataCpm->PubID,
                                  'SiteID'         => $dataCpm->SiteID,
                                  'minimum_cpm_id' => $cpmID,
                                  'size_id'        => (int) $iter['sizeID'],
                                  'size'           => $iter['size'], 
                                  'passback'       => (int) $iter['passback'],
                                  'data'           => trim($iter['file']),
                                  'dynamic'        => $dataCpm->dynamic,
                                  'file'           => $iter['passback'] == 0 ? $iter['size'].'.js' : null,
                                  'iframe'         => null); 

                $dataFile['data'] = str_replace('<!--//<![CDATA[', '', $dataFile['data']);
                $dataFile['data'] = str_replace('//]]>-->', '', $dataFile['data']);
                $dataFile['data'] = str_replace('//<![CDATA[', '', $dataFile['data']);
                $dataFile['data'] = str_replace('//]]>', '', $dataFile['data']);
                
                $passbackArr[$iter['size']] = $dataFile['data'];
                
                if($iter['passback'] == 0){
					$flag_submitted_tag = true;
                    /*Application_Plugin_PassbackTag::first($dataFile, $path, $dataSite->type, $dataSite->store_tag_url);*/                    
                } 
                
                $tableFile->insert($dataFile);
            }       
            if($flag_submitted_tag)
            {
            	$logs_model = new Application_Model_DbTable_WebsiteLogs();
            	$params = array(
            			'PubID' => $dataCpm->PubID,
            			'SiteID' => $dataCpm->SiteID,
            			'note_type' => 2,
            			'admin_id' => null,
            			'note' => 'Floor tags was requested'
            	);
            	$logs_model->AddNote($params);
            }
            

            if($generate===TRUE && $dataCpm->cpm=='Max Fill'){
                if($dataCpm->checkPrevApprove()){
                    $sql = "DELETE FROM sites_floor_price WHERE SiteID = '".$dataSite->SiteID."'";
                    $dbAdapter->query($sql);  
                    $tableSites->update(array('floor_pricing' => NULL), 'SiteID = '.$dataSite->SiteID);
                }
            }elseif($generate===TRUE){
                $tableSites->update(array('floor_pricing' => 1), 'SiteID ='.$dataSite->SiteID);
                $sql = "INSERT INTO sites_floor_price (PubID, SiteID, date, price, percent) VALUES('".$dataCpm->PubID."', '".$dataCpm->SiteID."', '".$dataCpm->created."', '".$dataCpm->cpm."', '0.5')";
                $dbAdapter->query($sql);                
            }
            
            
            if($dataSite->type==4 && count($passbackArr)>0){
                $tableSites->update(array('create_dfp_passbacks'=>1), 'SiteID = '.$dataCpm->SiteID);
                
                $tableSitesWantApi = new Application_Model_DbTable_Sites_WantApi();
                $dataSitesWantApi = $tableSitesWantApi->createRow();
                $dataSitesWantApi->SiteID = $dataCpm->SiteID;
                if($generate===TRUE && $dataCpm->cpm=='Max Fill'){
                    $dataSitesWantApi->cpm = -1;
                }elseif ($generate==TRUE) {
                    
                    if($dataSite->factor_revshare==1){
                        $tableRevshare = new Application_Model_DbTable_UserRevshare();
                        $dataRevshare = $tableRevshare->fetchRow("PubID='".$dataSite->PubID."'", "date DESC");
                        if($dataRevshare->RevShare){
                            $cpm = round($dataCpm->cpm/($dataRevshare->RevShare/100), 2);
                        }else{
                            $cpm = null;
                        }                        
                    }else{
                        $cpm = null;
                    }
                    
                    $dataSitesWantApi->cpm = $cpm ? $cpm : $dataCpm->cpm;
                    $dataSitesWantApi->delPlacements = 1;                    
                }
                $dataSitesWantApi->created = date("Y-m-d H:i:s");
                $dataSitesWantApi->creative_passback = serialize($passbackArr);
                //$dataSitesWantApi->save();                
            }
            
            if($generate===TRUE){
                $siteTagsModel = new Application_Model_DbTable_SitesTags();
                $siteTagsModel->changeAction($dataSite->SiteID, 'gen', APPLICATION_ENV);
            }
            
            $this->view->result = array('status' => 1, 'data' => array('id' => $cpmID));
           
    }
        
    public function cancelMinimumCpmAction()
    {
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender();
        
        $dataAuth = Zend_Auth::getInstance()->getIdentity();
        
        $tableCpm = new Application_Model_DbTable_Cpm_Minimum();
        $tableFile = new Application_Model_DbTable_Cpm_File();
        
        $sql = $tableCpm->select()
                        ->where('PubID = ?', $dataAuth->id)
                        ->where('id = ?', (int) $this->_getParam('id'));
        
        $dataCpm = $tableCpm->fetchRow($sql);
        
        $tableFile->delete('minimum_cpm_id = '.$dataCpm->id);
                
        $dataPrev['PubID'] = $dataCpm->PubID;
        $dataPrev['SiteID'] = $dataCpm->SiteID;
        $dataPrev['dynamic'] = $dataCpm->dynamic;
                
        $dataCpm->delete();
        
        $siteTagsModel = new Application_Model_DbTable_SitesTags();
        $siteTagsModel->changeAction($dataPrev['SiteID'], 'gen', APPLICATION_ENV);

        $this->view->result = true;
    }
    
    public function deniedAction()
    {
        set_time_limit(0);
        
        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        
        /* MySQL connection */
	$settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);
     
	$gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );
	
	mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );
                
//        $aColumns = array(  'IF(psa.iframe, psa.src, psa.url)', 'SUM(psa.num)', 'MAX(psa.query_date)');
//        $Columns = array(  'IF(psa.iframe, psa.src, psa.url)', 'SUM(psa.num)', 'MAX(psa.query_date)');
//        $likeColumns = array( 0 => 'IF(psa.iframe, psa.src, psa.url)', 1 => 'SUM(psa.num)'); 
//	$sIndexColumn = "psa.SiteID";		
//	$sTable = "madads_psa AS psa";
        
        $aColumns = array(  'spn.doname', 'spn.num', 'spn.updated');
        $Columns = array(  'doname', 'num', 'updated');
        $likeColumns = array( 0 => 'spn.doname', 1 => 'spn.num'); 
	$sIndexColumn = "spn.SiteID";		
	$sTable = "sites_psa_num AS spn";
        
        $sitesDB = new Application_Model_DbTable_Sites();
        $selectsites = $sitesDB->select();
        $selectsites->where("PubID='".$_GET['PubID']."'");
        //$sOrder=" ORDER BY psa.query_date DESC ";
        
        $sitesSel = $sitesDB ->fetchAll($selectsites);
        
        $siteIDS = "(";
        $first = true;
        foreach($sitesSel as $siteSel){
            
            if(!$first){ $siteIDS = $siteIDS.","; }
                $first=false;
                
            $siteIDS = $siteIDS."'".$siteSel->SiteID."'";
        }   $siteIDS = $siteIDS.")";
        
//        $sWhere1 = " WHERE SiteID IN $siteIDS AND IF(psa.iframe, psa.src != '', psa.url != '') ";
//        $sGroup = " GROUP BY IF(psa.iframe, psa.src, psa.url) ";
//	if($_GET['SiteID']>0){ $sWhere1 = $sWhere1." AND psa.SiteID = '".$_GET['SiteID']."' "; }
        
        $sWhere1 = " WHERE spn.SiteID IN $siteIDS AND spn.doname != '' AND spn.status != 1 ";
        $sGroup = " ";
	if($_GET['SiteID']>0){ $sWhere1 = $sWhere1." AND spn.SiteID = '".$_GET['SiteID']."' "; }
        
	/* 
	 * Paging
	 */
	$sLimit = "";
	if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
	{
		$sLimit = "LIMIT ".mysql_real_escape_string( $_GET['iDisplayStart'] ).", ".
			mysql_real_escape_string( $_GET['iDisplayLength'] );
	}
	
	
	/*
	 * Ordering
	 */
	$sOrder = "";
	if ( isset( $_GET['iSortCol_0'] ) )
	{
		$sOrder = "ORDER BY  ";
		for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
		{
			if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
			{
				$sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ] ." ".
				 	mysql_real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
			}
		}
		
		$sOrder = substr_replace( $sOrder, "", -2 );
		if ( $sOrder == "ORDER BY" )
		{
			$sOrder = "";
		}
	}
	

	/* 
	 * Filtering
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here, but concerned about efficiency
	 * on very large tables, and MySQL's regex functionality is very limited
	 */
	$sWhere = "";
	if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
	{
		$sWhere = "AND (";
		foreach ( $likeColumns as $key => $field )
		{
			$sWhere .= $field." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
		}
		$sWhere = substr_replace( $sWhere, "", -3 );
		$sWhere .= ')';
	}
	
        
	/* Individual column filtering */
	foreach( $likeColumns as $key => $field )
	{
		if ( isset($_GET['bSearchable_'.$key]) && $_GET['bSearchable_'.$key] == "true" && $_GET['sSearch_'.$key] != '' )
		{
			if ( $sWhere == "" )
			{
				$sWhere = "AND ";
			}
			else
			{
				$sWhere .= " AND ";
			}
			$sWhere .= $field." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$key])."%' ";
		}
	}

	/*
	 * SQL queries
	 * Get data to display
	 */
	$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
		FROM   $sTable
                $sWhere1
		$sWhere
                $sGroup
		$sOrder
		$sLimit
		"; 
        //throw new Exception($sQuery);
	$rResult = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	$tmp = $sQuery;
	/* Data set length after filtering */
	$sQuery = "
		SELECT FOUND_ROWS()
	"; 
	$rResultFilterTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error()); 
	$aResultFilterTotal = mysql_fetch_array($rResultFilterTotal); 
	$iFilteredTotal = $aResultFilterTotal[0];
	
	/* Total data set length */
	$sQuery = "
		SELECT COUNT(".$sIndexColumn.")
		FROM   $sTable
                $sWhere1
                $sWhere  
                $sGroup
		$sOrder
		$sLimit
	"; 
	$rResultTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
	$aResultTotal = mysql_fetch_array($rResultTotal);
	$iTotal = $aResultTotal[0];
	
	
	/*
	 * Output
	 */
	$output = array(
		"sEcho" => intval($_GET['sEcho']),
		"iTotalRecords" => $iTotal,
		"iTotalDisplayRecords" => $iFilteredTotal,
		"aaData" => array()
	);
     
        
	while ( $aRow = mysql_fetch_array( $rResult ) )
	{
		$row = array();
		for ( $i=0 ; $i<count($Columns) ; $i++ )
		{
			if ( $Columns[$i] == "version" )
			{
				/* Special output formatting for 'version' column */
				$row[] = ($aRow[ $Columns[$i] ]=="0") ? '-' : $aRow[ $Columns[$i] ];
			}
			else if ( $Columns[$i] != ' ' )
			{
				/* General output */
				$row[] = $aRow[ $Columns[$i] ];
			}
		}
		$output['aaData'][] = $row;
	}
             
       
        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);       
    }
    
    public function bannedAction()
    { 
        set_time_limit(0);

        $this->_helper->layout()->disableLayout(); // disable layout
        $this->_helper->viewRenderer->setNoRender(); // disable view rendering
        $auth = Zend_Auth::getInstance()->getIdentity();
        $siteID = (int)$_GET['SiteID'];
        
        /* MySQL connection */
        $settingsArray = parse_ini_file(APPLICATION_PATH.'/configs/application.ini', TRUE);

        $gaSql['link'] =  mysql_pconnect( $settingsArray[APPLICATION_ENV]['resources.db.params.host'], $settingsArray[APPLICATION_ENV]['resources.db.params.username'], $settingsArray[APPLICATION_ENV]['resources.db.params.password']  ) or die( 'Could not open connection to server' );

        mysql_select_db( $settingsArray[APPLICATION_ENV]['resources.db.params.dbname'], $gaSql['link'] ) or die( 'Could not select database '. $gaSql['db'] );



        $aColumns = array('s.SiteName', 'IF(mb.updated, mb.updated, mb.query_date)', 'IF(mb.iframe, mb.src_full, mb.url_full)');
         $Columns = array('SiteName', 'IF(mb.updated, mb.updated, mb.query_date)', 'IF(mb.iframe, mb.src_full, mb.url_full)');
     $likeColumns = array(0 => 's.SiteName');  

        /* Indexed column (used for fast and accurate table cardinality) */
        $sIndexColumn = "mb.SiteID";

        /* DB table to use */
        $sTable = "madads_blocked AS mb ";
        $sJoin = "JOIN sites AS s ON s.SiteID = mb.SiteID JOIN users AS u ON u.id = s.PubID";
        $sGroup = "GROUP BY mb.SiteID, IF(mb.iframe, mb.src_full, mb.url_full) ";                  


        /* 
         * Paging
         */
        $sLimit = "";
        if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
        {
                $sLimit = "LIMIT ".mysql_real_escape_string( $_GET['iDisplayStart'] ).", ".
                        mysql_real_escape_string( $_GET['iDisplayLength'] );
        }


        /*
         * Ordering
         */
        $sOrder = "";
        if ( isset( $_GET['iSortCol_0'] ) )
        {
                $sOrder = "ORDER BY  ";
                for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
                {
                        if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
                        {
                                $sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ] ." ".
                                        mysql_real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
                        }
                }

                $sOrder = substr_replace( $sOrder, "", -2 );
                if ( $sOrder == "ORDER BY" )
                {
                        $sOrder = "";
                }
        }


        /* 
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $sWhere = "";
        if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
        {
                $sWhere = "WHERE (";
                foreach ( $likeColumns as $key => $field )
                {
                        $sWhere .= $field." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
                }
                $sWhere = substr_replace( $sWhere, "", -3 );
                $sWhere .= ')';
        }


        /* Individual column filtering */
        foreach( $likeColumns as $key => $field )
        {
                if ( isset($_GET['bSearchable_'.$key]) && $_GET['bSearchable_'.$key] == "true" && $_GET['sSearch_'.$key] != '' )
                {
                        if ( $sWhere == "" )
                        {
                                $sWhere = "WHERE ";
                        }
                        else
                        {
                                $sWhere .= " AND ";
                        }
                        $sWhere .= $field." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$key])."%' ";
                }
        }


        if($sWhere == ""){
            if($siteID)
                $sWhere = 'WHERE mb.hide IS NULL AND u.id='.$auth->id.' AND mb.SiteID='.$siteID.' ';
            else
                $sWhere = 'WHERE mb.hide IS NULL AND u.id='.$auth->id.' ';
        } else {
            if($siteID)
                $sWhere .= ' AND mb.hide IS NULL AND u.id='.$auth->id.' ';
            else
                $sWhere .= ' AND mb.hide IS NULL AND u.id='.$auth->id.' AND mb.SiteID='.$siteID.' ';
        }

        /*
         * SQL queries
         * Get data to display
         */
        $sQuery = "
                SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
                FROM   $sTable
                $sJoin
                $sWhere
                $sGroup
                $sOrder
                $sLimit
                ";
        $rResult = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());

        /* Data set length after filtering */
        $sQuery = "
                SELECT FOUND_ROWS()
        "; 
        $rResultFilterTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error()); 
        $aResultFilterTotal = mysql_fetch_array($rResultFilterTotal); 
        $iFilteredTotal = $aResultFilterTotal[0];

        /* Total data set length */
        $sQuery = "
                SELECT COUNT(".$sIndexColumn.")
                FROM   $sTable 
                $sJoin
                $sWhere
                $sGroup
        "; 
        $rResultTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
        $aResultTotal = mysql_fetch_array($rResultTotal);
        $iTotal = $aResultTotal[0];


        /*
         * Output
         */
        $output = array(
                "sEcho" => intval($_GET['sEcho']),
                "iTotalRecords" => $iTotal,
                "iTotalDisplayRecords" => $iFilteredTotal,
                "aaData" => array()
        );


        while ( $aRow = mysql_fetch_array( $rResult ) )
        {
                $row = array();
                for ( $i=0 ; $i<count($Columns) ; $i++ )
                {
                        if ( $Columns[$i] == "version" )
                        {
                                /* Special output formatting for 'version' column */
                                $row[] = ($aRow[ $Columns[$i] ]=="0") ? '-' : $aRow[ $Columns[$i] ];
                        }
                        else if ( $Columns[$i] != ' ' )
                        {
                                /* General output */
                                $row[] = $aRow[ $Columns[$i] ];
                        }
                }
                $output['aaData'][] = $row;
        }


        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    }

    public function getPaymentAmountAction()
    {
    	$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	$auth = Zend_Auth::getInstance()->getIdentity();
    	$user_model = new Application_Model_DbTable_Users();
    	$user_data = $user_model->getUserById($auth->id);
    	$result = array('status' => 0);
    	$payment_type = $this->_getParam('payment_type');
    	if($payment_type)
    	{
    		switch($payment_type)
    		{
    			case 1:
    			case 2:
    			case 3:
    				$payments = $user_model->querySelectPaymentAmount();
    				break;
    			case 4:
    				$payments = $user_model->querySelectPaymentAmount(true);
    				break;
    			default:
    				break;
    		}
    		$result['status'] = 1;
    		$result['data'] = $payments;
    	}
    	$this->getResponse()->setBody(Zend_Json::encode($result))->setHeader('content-type', 'application/json', true);    	
    }
    
    public function getReportMadxAction()
    {
        $this->_helper->layout()->disableLayout(); 
        $this->_helper->viewRenderer->setNoRender();
        
        $SiteID = (int) $this->_getParam('SiteID');
        $AdSize = (int) $this->_getParam('AdSize');
        $date = date('Y-m-d', strtotime($this->_getParam('date')));
        $dataAuth =  Zend_Auth::getInstance()->getIdentity();
        
        $dataAuth =  Zend_Auth::getInstance()->getIdentity();
        
        $tableReport = new Application_Model_DbTable_Report();
        $dataReport = $tableReport->getReportMadx($SiteID, $date, $AdSize, $dataAuth->id);

        $this->getResponse()->setBody(Zend_Json::encode($dataReport))->setHeader('content-type', 'application/json', true);               
    }    
}