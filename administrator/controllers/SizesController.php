<?php
/**
 * Description of SizesController
 *
 * @author nik
 */
class Administrator_SizesController extends Zend_Controller_Action 
{

    public function init()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('admin');
    }
    
    public function indexAction()
    {
        $sizeModel = new Application_Model_DbTable_Sizes();
        $this->view->sizes = $sizeModel->fetchAll();

    }
    
    public function addAction()
    {
        $form = new Application_Form_Sizes();
        
        if($this->getRequest()->isPost()){        	        	            
            if($form->isValid($this->getRequest()->getPost())){ 
                $sizeModel = new Application_Model_DbTable_Sizes();
                
                $dataSize = $sizeModel->createRow();
              
                    $dataSize->setFromArray(array(
                        'name' => $form->getValue('name'),
                        'width' => $form->getValue('width'),
                        'height' => $form->getValue('height'),
                        'rp_zonesize' => $form->getValue('rp_zonesize'),
                        'rp_account_max_fill' => $form->getValue('rp_account_max_fill'),
                        'rp_account_floor_tag' => $form->getValue('rp_account_floor_tag'),
                        'rp_account_limited' => $form->getValue('rp_account_limited'),
                        'rp_site_max_fill' => $form->getValue('rp_site_max_fill'),
                        'rp_site_floor_tag' => $form->getValue('rp_site_floor_tag'),
                        'rp_site_limited' => $form->getValue('rp_site_limited'),
                        'rp_zonesize_max_fill' => $form->getValue('rp_zonesize_max_fill'),
                        'rp_zonesize_floor_tag' => $form->getValue('rp_zonesize_floor_tag'),
                        'rp_zonesize_limited' => $form->getValue('rp_zonesize_limited'),
                        'file_name' => $form->getValue('file_name'),
                        'description' => $form->getValue('description'),
                        'position' => $form->getValue('position'),
                        'video' => $form->getValue('video')==1 ? 1 : NULL,
                        'active' => $form->getValue('active')==1 ? 1 : 0,
                        'individual' => $form->getValue('individual')==1 ? 1 : 0
                    ));                
                
                $dataSize->save();
                
                $this->_redirect('/administrator/sizes/');
            }else{
                $this->view->formErrors = $form->getMessages();
                $this->view->formValues = $form->getValues();                 
            }
        }

    }
    
    public function editAction()
    {                
        $tableSize = new Application_Model_DbTable_Sizes();
        $form = new Application_Form_Sizes();
        
        $sql = $tableSize->select()->where('id = ?', (int) $this->_getParam('id'));
        
        $dataSize = $tableSize->fetchRow($sql);
        
        if($this->getRequest()->isPost()){        	        	            
            if($form->isValid($this->getRequest()->getPost())){ 
             
                    $dataSize->setFromArray(array(
                        'name' => $form->getValue('name'),
                        'width' => $form->getValue('width'),
                        'height' => $form->getValue('height'),
                        'rp_zonesize' => $form->getValue('rp_zonesize'),
                        'rp_account_max_fill' => $form->getValue('rp_account_max_fill'),
                        'rp_account_floor_tag' => $form->getValue('rp_account_floor_tag'),
                        'rp_account_limited' => $form->getValue('rp_account_limited'),
                        'rp_site_max_fill' => $form->getValue('rp_site_max_fill'),
                        'rp_site_floor_tag' => $form->getValue('rp_site_floor_tag'),
                        'rp_site_limited' => $form->getValue('rp_site_limited'),
                        'rp_zonesize_max_fill' => $form->getValue('rp_zonesize_max_fill'),
                        'rp_zonesize_floor_tag' => $form->getValue('rp_zonesize_floor_tag'),
                        'rp_zonesize_limited' => $form->getValue('rp_zonesize_limited'),
                        'file_name' => $form->getValue('file_name'),
                        'description' => $form->getValue('description'),
                        'position' => $form->getValue('position'),
                        'video' => $form->getValue('video')==1 ? 1 : NULL,
                        'hide_rubicon' => $form->getValue('hide_rubicon')==1 ? 1 : NULL,
                        'hide_google' => $form->getValue('hide_google')==1 ? 1 : NULL,
                        'active' => $form->getValue('active')==1 ? 1 : 0,
                        'individual' => $form->getValue('individual')==1 ? 1 : 0
                    ));         
                
                $dataSize->save();
                
                $this->_redirect('/administrator/sizes/');
                
            } else {
                
                $this->view->formErrors = $form->getMessages();
                $this->view->formValues = $form->getValues();   
                
            }
            
        } else {
            
              $this->view->formValues = array('name' => $dataSize->name,
                                              'width' => $dataSize->width,
                                              'height' => $dataSize->height,
                                              'rp_zonesize' => $dataSize->rp_zonesize,
                                              'rp_account_max_fill' => $dataSize->rp_account_max_fill,
                                              'rp_account_floor_tag' => $dataSize->rp_account_floor_tag,
                                              'rp_account_limited' => $dataSize->rp_account_limited,
                                              'rp_site_max_fill' => $dataSize->rp_site_max_fill,
                                              'rp_site_floor_tag' => $dataSize->rp_site_floor_tag,
                                              'rp_site_limited' => $dataSize->rp_site_limited,
                                              'rp_zonesize_max_fill' => $dataSize->rp_zonesize_max_fill,
                                              'rp_zonesize_floor_tag' => $dataSize->rp_zonesize_floor_tag,
                                              'rp_zonesize_limited' => $dataSize->rp_zonesize_limited, 
                                              'file_name' => $dataSize->file_name,
                                              'description' => $dataSize->description,
                                              'position' => $dataSize->position,
                                              'video' => $dataSize->video,
                                              'hide_rubicon' => $dataSize->hide_rubicon,
                                              'hide_google' => $dataSize->hide_google,
                                              'active' => $dataSize->active,
                                              'individual' => $dataSize->individual); 
          
        
        }
              
    }
}

?>
