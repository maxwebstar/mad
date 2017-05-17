<?php
    
class Application_Form_Tags extends Zend_Form{
    
    public function init(){
        //name form
        $this->setName('tags');
        
        $isEmptyMessage = 'Field is required';
        $isEmptyMessageNetwork = 'The field must be filled in at least for one AdSize';

        $networks = new Zend_Form_Element_Text('networks', ['isArray'=>true]);
        $networks->setLabel('Networks:')
            ->setRequired(true)
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => $isEmptyMessage))
            )
            ->addValidator('Int');

        $activeNetwork = new Zend_Form_Element_Text('activeNetwork');
        $activeNetwork->setLabel('activeNetwork:')
            ->setRequired(true)
            ->addValidator('NotEmpty', true,
                array('messages' => array('isEmpty' => "Select Primary network"))
            )
            ->addValidator('Int');

        // for rubicon tags
        $rubicon_site_name = new Zend_Form_Element_Text('rubicon_site_name', array('isArray'=>true));
        $rubicon_site_name->setLabel('Site Name:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim');

        $use_id = new Zend_Form_Element_MultiCheckbox('use_id', [
            'multiOptions'=>[
                'on'
            ]
        ]);
        $use_id->setLabel('use_id:');

        $rp_account = new Zend_Form_Element_Text('rp_account', array('isArray'=>true));
        $rp_account->setLabel('rp_account:');

        $rp_zonesize = new Zend_Form_Element_Text('rp_zonesize', array('isArray'=>true));
        $rp_zonesize->setLabel('rp_zonesize:');

        $rp_site = new Zend_Form_Element_Text('rp_site', array('isArray'=>true));
        $rp_site->setLabel('rp_site:');

        $rubicon_sizes = new Zend_Form_Element_MultiCheckbox('sizes_5', [
            'multiOptions'=>[
                'on'
            ]
        ]);
        $rubicon_sizes->setLabel('rubicon-sizes:');


        // for pubmatic tags
        $pubmatic_sizes = new Zend_Form_Element_MultiCheckbox('sizes_8', [
            'multiOptions'=>[
                'on'
            ]
        ]);
        $pubmatic_sizes->setLabel('pubmatic-sizes:');

        $kPubID = new Zend_Form_Element_Text('kPubID', array('isArray'=>true));
        $kPubID->setLabel('pubID=:');

        $kSiteID = new Zend_Form_Element_Text('kSiteID', array('isArray'=>true));
        $kSiteID->setLabel('siteId=:');

        $kadID = new Zend_Form_Element_Text('kadID', array('isArray'=>true));
        $kadID->setLabel('kadId=:');

        $kadWidth = new Zend_Form_Element_Text('kadWidth', array('isArray'=>true));
        $kadWidth->setLabel('kadWidth=:');

        $kadHeight = new Zend_Form_Element_Text('kadHeight', array('isArray'=>true));
        $kadHeight->setLabel('kadHeight=:');

        $kadType = new Zend_Form_Element_Text('kadType', array('isArray'=>true));
        $kadType->setLabel('kadtype=:');

        $kadPageUrl = new Zend_Form_Element_Text('kadPageUrl', array('isArray'=>true));
        $kadPageUrl->setLabel('kadpageurl=:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim');


        // for amazon tags
        $slot_uuid = new Zend_Form_Element_Text('slot_uuid', array('isArray'=>true));
        $slot_uuid->setLabel('slot_uuid:');

        $amazon_sizes = new Zend_Form_Element_MultiCheckbox('sizes_9', [
            'multiOptions'=>[
                'on'
            ]
        ]);
        $amazon_sizes->setLabel('amazon-sizes:');


        // for pulsePoint tags
        $pulse_sizes = new Zend_Form_Element_MultiCheckbox('sizes_10', [
            'multiOptions'=>[
                'on'
            ]
        ]);
        $pulse_sizes->setLabel('pulse-sizes:');

        $PulsePubID = new Zend_Form_Element_Text('PulsePubID', array('isArray'=>true));
        $PulsePubID->setLabel('PubID:');

        $PulseTagID = new Zend_Form_Element_Text('PulseTagID', array('isArray'=>true));
        $PulseTagID->setLabel('TagID:');

        // for PopAds
        $pop_ads_sizes = new Zend_Form_Element_MultiCheckbox('sizes_16', [
            'multiOptions'=>[
                'on'
            ]
        ]);
        $pop_ads_sizes->setLabel('pop-ads-sizes:');

        $popSiteID = new Zend_Form_Element_Text('popSiteID', array('isArray'=>true));
        $popSiteID->setLabel('popSiteID:');

        $popVisitor = new Zend_Form_Element_Text('popVisitor', array('isArray'=>true));
        $popVisitor->setLabel('popVisitor:');

        // for PopCash
        $pop_cash_sizes = new Zend_Form_Element_MultiCheckbox('sizes_17', [
            'multiOptions'=>[
                'on'
            ]
        ]);
        $pop_cash_sizes->setLabel('pop-cash-sizes:');

        $wid = new Zend_Form_Element_Text('wid', array('isArray'=>true));
        $wid->setLabel('wid:');

        // for Sekindo
        $sekindo_sizes = new Zend_Form_Element_MultiCheckbox('sizes_18', [
            'multiOptions'=>[
                'on'
            ]
        ]);
        $sekindo_sizes->setLabel('sekindo-sizes:');

        $spaceId = new Zend_Form_Element_Text('spaceId', array('isArray'=>true));
        $spaceId->setLabel('spaceId:');
        
        // for AOL
        $aol = new Zend_Form_Element_MultiCheckbox('sizes_19', [
            'multiOptions'=>[
                'on'
            ]
        ]);
        $aol->setLabel('aol-sizes:');
        
        $aolPlacement = new Zend_Form_Element_Text('aol_placementId', array('isArray'=>true));
        $aolPlacement->setLabel('PlacementID:');
        
        $aolNetwork = new Zend_Form_Element_Text('aol_network', array('isArray'=>true));
        $aolNetwork->setLabel('Network:');
        
        // for AOL Outstream
        $aolo = new Zend_Form_Element_MultiCheckbox('sizes_20', [
            'multiOptions'=>[
                'on'
            ]
        ]);
        $aolo->setLabel('aol-outstream-sizes:');
        
        $aoloPlacementName = new Zend_Form_Element_Text('aol_o_PlacementName', array('isArray'=>true));
        $aoloPlacementName->setLabel('PlacementName:');
        
        $aoloAccountID = new Zend_Form_Element_Text('aol_o_AccountID', array('isArray'=>true));
        $aoloAccountID->setLabel('AccountID:');
        
        $aoloPlayerID = new Zend_Form_Element_Text('aol_o_PlayerID', array('isArray'=>true));
        $aoloPlayerID->setLabel('PlayerID:');
        
        $aoloDivID = new Zend_Form_Element_Text('aol_o_DivID', array('isArray'=>true));
        $aoloDivID->setLabel('DivID:');
        
        $aoloScriptID = new Zend_Form_Element_Text('aol_o_ScriptID', array('isArray'=>true));
        $aoloScriptID->setLabel('ScriptID:');
        
        // for bRealTime
        $brt = new Zend_Form_Element_MultiCheckbox('sizes_21', [
            'multiOptions'=>[
                'on'
            ]
        ]);
        $brt->setLabel('brt-sizes:');
        
        $brtPlacement = new Zend_Form_Element_Text('brt_placementId', array('isArray'=>true));
        $brtPlacement->setLabel('PlacementID:');


        //add elements to form
        $this->addElements(array(
            $networks,
            $activeNetwork,
            $rubicon_site_name,
            $use_id,
            $rp_account,
            $rp_site,
            $rp_zonesize,
            $rubicon_sizes,
            $pubmatic_sizes,
            $kPubID,
            $kSiteID,
            $kadID,
            $kadWidth,
            $kadHeight,
            $kadType,
            $kadPageUrl,
            $amazon_sizes,
            $slot_uuid,
            $pulse_sizes,
            $PulsePubID,
            $PulseTagID,
            $pop_ads_sizes,
            $popSiteID,
            $popVisitor,
            $pop_cash_sizes,
            $wid,
            $sekindo_sizes,
            $spaceId,
            $aol,
            $aolPlacement,
            $aolNetwork,
            $aolo,
            $aoloPlacementName,
            $aoloAccountID,
            $aoloPlayerID,
            $aoloDivID,
            $aoloScriptID,
            $brt,
            $brtPlacement,
            ));
        $this->setMethod('post');

        //attr group for rubicon
        $this->addDisplayGroup([
            'sizes_5',
            'rubicon_site_name',
            'rp_account',
            'rp_site',
            'rp_zonesize'
        ], 5);

        //attr group for pubmatic
        $this->addDisplayGroup([
            'sizes_8',
            'kPubID',
            'kSiteID',
            'kadID',
            'kadWidth',
            'kadHeight',
            'kadType',
            'kadPageUrl'
        ], 8);

        //attr group for amazon
        $this->addDisplayGroup([
            'sizes_9',
            'slot_uuid'
        ], 9);

        //attr group for pulsePoint
        $this->addDisplayGroup([
            'sizes_10',
            'PulsePubID',
            'PulseTagID'
        ], 10);

        //attr group for PopAds
        $this->addDisplayGroup([
            'sizes_16',
            'popSiteID',
            'popVisitor'
        ], 16);

        //attr group for PopCash
        $this->addDisplayGroup([
            'sizes_17',
            'wid'
        ], 17);

        //attr group for Sekindo
        $this->addDisplayGroup([
            'sizes_18',
            'spaceId'
        ], 18);
        
        //attr group for AOL
        $this->addDisplayGroup([
            'sizes_19',
            'aol_placementId',
            'aol_network'
        ], 19);
        
        //attr group for AOL Outstream
        $this->addDisplayGroup([
            'sizes_20',
            'aol_o_PlacementName',
            'aol_o_AccountID',
            'aol_o_PlayerID',
            'aol_o_DivID',
            'aol_o_ScriptID',
        ], 20);
        
        //attr group for bRealTime
        $this->addDisplayGroup([
            'sizes_21',
            'brt_placementId',
        ], 21);
    }
}