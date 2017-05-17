<?php
/**
 * Description of QuickbooksController
 *
 * @author nik
 */
class Administrator_QuickbooksController extends Zend_Controller_Action 
{

    public function init()
    {
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('admin');
    }
    
    public function indexAction()
    {
        $form = new Application_Form_Qb();
        
        if($this->getRequest()->isPost()){        	        	            
            if($form->isValid($this->getRequest()->getPost())){ 
                $qbModel = new Application_Model_DbTable_Qb();
                
                $dataQb = $qbModel->createRow();
              
                    $dataQb->setFromArray(array(
                        'action' => $form->getValue('action'),
                        'created' => date("Y-m-d H:i:s"),
                        'new_vend' => $form->getValue('new_vend'),
                        'year' => $form->getValue('year'),
                        'month' => $form->getValue('month'),
                        'by_id' => $form->getValue('by_id'),
                        'by_email' => $form->getValue('by_email'),
                        'by_name' => $form->getValue('by_name'),
                        'status' => 1
                    ));                
                
                $dataQb->save();
                
                $this->_redirect('/administrator/quickbooks/index');
            }else{
                $this->view->formErrors = $form->getMessages();
                $this->view->formValues = $form->getValues();                 
            }
        }		
    }    
}

?>
