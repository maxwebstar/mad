<?php

class Helper_TagValidators extends Zend_Controller_Action_Helper_Abstract
{

    public function setValidators(Application_Form_Tags $form, array $dataPost)
    {
        $isEmptyMess = 'The field must be filled in at least for one AdSize';

        // for rubicon
        if(count($dataPost['sizes_5'])>0){
            $form->getElement('rubicon_site_name')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMess))
                );
            if(count($dataPost['use_id'])){
                $form->getElement('rp_account')
                    ->setRequired(true)
                    ->addValidator('Int')
                    ->addValidator('NotEmpty', true,
                        array('messages' => array('isEmpty' => $isEmptyMess))
                    );
                $form->getElement('rp_zonesize')
                    ->setRequired(true)
                    ->addValidator('Int')
                    ->addValidator('NotEmpty', true,
                        array('messages' => array('isEmpty' => $isEmptyMess))
                    );
                $form->getElement('rp_site')
                    ->setRequired(true)
                    ->addValidator('Int')
                    ->addValidator('NotEmpty', true,
                        array('messages' => array('isEmpty' => $isEmptyMess))
                    );
            }
        }

        // for pubmatic
        if(count($dataPost['sizes_8'])>0){
            $form->getElement('kPubID')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMess))
                )
                ->addValidator('Int');
            $form->getElement('kSiteID')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMess))
                )
                ->addValidator('Int');
            $form->getElement('kadID')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMess))
                )
                ->addValidator('Int');
            $form->getElement('kadWidth')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMess))
                )
                ->addValidator('Int');
            $form->getElement('kadHeight')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMess))
                )
                ->addValidator('Int');
            $form->getElement('kadType')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMess))
                )
                ->addValidator('Int');
            $form->getElement('kadPageUrl')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMess))
                );
        }

        // for amazon
        if(count($dataPost['sizes_9'])>0){
            $form->getElement('slot_uuid')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMess))
                );
        }

        // for pulsePoint
        if(count($dataPost['sizes_10'])>0){
            $form->getElement('PulsePubID')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMess))
                )
                ->addValidator('Int');
            $form->getElement('PulseTagID')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMess))
                )
                ->addValidator('Int');
        }

        // for PopAds
        if(count($dataPost['sizes_16'])>0) {
            $form->getElement('popSiteID')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMess))
                )
                ->addValidator('Int');

            $form->getElement('popVisitor')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMess))
                )
                ->addValidator('Int');
        }

        // for PopCash
        if(count($dataPost['sizes_17'])>0) {
            $form->getElement('wid')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMess))
                )
                ->addValidator('Int');
        }

        // for Sekindo
        if(count($dataPost['sizes_18'])>0) {
            $form->getElement('spaceId')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMess))
                )
                ->addValidator('Int');
        }
        
        // for AOL
        if(count($dataPost['sizes_19'])>0) {
            $form->getElement('aol_placementId')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMess))
                )
                ->addValidator('StringLength', false, array('min'=>7, 'max'=>7))         
                ->addValidator('Int');

            $form->getElement('aol_network')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMess))
                );
        }
        
        // for AOL Outstream
        if(count($dataPost['sizes_20'])>0) {
            $form->getElement('aol_o_PlacementName')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMess))
                );
                        
            $form->getElement('aol_o_AccountID')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMess))
                )     
                ->addValidator('Int');
            
            $form->getElement('aol_o_PlayerID')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMess))
                )     
                ->addValidator('StringLength', false, array('min'=>6, 'max'=>6))    
                ->addValidator('Int');

            $form->getElement('aol_o_DivID')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMess))
                );
            
            $form->getElement('aol_o_ScriptID')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMess))
                );
        }
        
        // for bRealTime
        if(count($dataPost['sizes_21'])>0) {
            $form->getElement('brt_placementId')
                ->setRequired(true)
                ->addValidator('NotEmpty', true,
                    array('messages' => array('isEmpty' => $isEmptyMess))
                )
                ->addValidator('StringLength', false, array('min'=>7, 'max'=>7))         
                ->addValidator('Int');
        }

        return $form;
    }
}