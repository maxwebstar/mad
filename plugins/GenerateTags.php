<?php

class Application_Plugin_GenerateTags extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        
    }
        

    
    public function generate($SiteID)
    {
  
//        function RandNumber($e)
//        {
//            for($i=0;$i<$e;$i++){
//                    $rand =  $rand .  rand(0, 9);
//            }
//            return $rand;    	 
//        }
        
        $siteModel = new Application_Model_DbTable_Sites();
        $sizeModel = new Application_Model_DbTable_Sizes();
        $tableFile = new Application_Model_DbTable_Cpm_File();

        $sqlFile = $tableFile->select()->setIntegrityCheck(false)
                            ->from(array('mf' => 'minimum_cpm_file'), array('*'))
                            ->where('mf.SiteID = ?', $SiteID)
                            ->where('mf.file IS NOT NULL')
                            ->where('m.status = 3')
                            ->join(array('m' => 'minimum_cpm'),('m.id = mf.minimum_cpm_id'), array(''));                
        
        $siteInfo = $siteModel->getSiteTagsById($SiteID);
        $sizes = $sizeModel->fetchAll();
        $dataFile = $tableFile->fetchAll($sqlFile);
      
                
        if(!is_dir($_SERVER['DOCUMENT_ROOT']."/tags/".$siteInfo['user_id']))
                mkdir($_SERVER['DOCUMENT_ROOT']."/tags/".$siteInfo['user_id']);

        if(!is_dir($_SERVER['DOCUMENT_ROOT']."/tags/".$siteInfo['user_id']."/".$siteInfo['site_id']))
                mkdir($_SERVER['DOCUMENT_ROOT']."/tags/".$siteInfo['user_id']."/".$siteInfo['site_id']);

        if(!is_dir($_SERVER['DOCUMENT_ROOT']."/tags/".$siteInfo['user_id']."/".$siteInfo['site_id']."/async"))
                mkdir($_SERVER['DOCUMENT_ROOT']."/tags/".$siteInfo['user_id']."/".$siteInfo['site_id']."/async");
        
        
        if($siteInfo['type']==2){                    	

            foreach ($sizes as $size) {
                if($size->name!='Pop-unders'){
                     
                    $pbValue = "";
                                        
                    foreach($dataFile as $jter){
                        
                        if($jter->size_id == $size->id){ $pbValue = " var pb".$jter->size."='".$jter->PubID."/".$jter->SiteID."/default/".$jter->dynamic."/".$jter->file."'; "; }
                        
                    }
                    
                    $txt = "var iframe = 0; var url = document.location.href; var src = 0; if(window.top !== window.self){ iframe = 1; src = document.referrer.split('/'); if(src.length > 2){ src = src[2]; } else { src = src[0]; } }document.write(\"<img src='http://pixel.madadsmedia.com/?site=".$siteInfo['site_id']."&size=".$size->id."&iframe=\"+iframe+\"&url=\"+url+\"&src=\"+src+\"' style='display:none'>\");";
                    $txt.= "document.write(\"<script type='text/javascript'>\");".$pbValue."var rp_account='".$siteInfo['accountRub_id']."';var rp_site='".$siteInfo['siteRub_id']."';var rp_zonesize='".$siteInfo['zoneRub_id']."-".$size->rp_zonesize."';var rp_adtype='js';var rp_smartfile='[SMART FILE URL]';document.write(\"<\/script>\");document.write(\"<script type='text/javascript' src='http://ads-by.madadsmedia.com/tags/ad/8223.js'>\");document.write(\"<\/script>\");";
                    file_put_contents($_SERVER['DOCUMENT_ROOT']."/tags/".$siteInfo['user_id']."/".$siteInfo['site_id']."/async/".$size->file_name.".js", $txt);                            
                }
            } 

        }elseif($siteInfo['type']==4){              

            /*new test tags*/
            $sitesArray = array();
            $sitesArray = explode("\n", $siteInfo['SiteURL']);
            if($sitesArray){
                    $approvedURLs = '[';
                    $counArray = count($sitesArray);
                    $count=0;
                    foreach ($sitesArray as $site){
                            $count++;
                            $siteurl = str_replace("http://", "", $site);
                            $siteurl = str_replace("https://", "", $siteurl);
                            $siteurl = strtolower($siteurl);
                            if($count==$counArray)
                                    $approvedURLs.= "'".$siteurl."'";
                            else
                                    $approvedURLs.= "'".$siteurl."',";
                    }
                    $approvedURLs.= ']';
            }

            $blockedSitesArray = array();
            $blockedSites = $siteModel->getBlacklist();
            $blockedSitesArray = explode("\r\n", $blockedSites['list']);
            if($blockedSitesArray){
                    $blockedURLs = '[';
                    $counArray = count($blockedSitesArray);
                    $count=0;                    		
                    foreach ($blockedSitesArray as $site){
                            $count++;
                            if($count==$counArray)
                                    $blockedURLs.= "\"".$site."\"";
                            else
                                    $blockedURLs.= "\"".$site."\",";
                    }
                    $blockedURLs.= ']';                    		
            }

            $siteName = str_replace("http://", "", $siteInfo['SiteURL']);
            $siteName = str_replace("https://", "", $siteName);
            $siteName = strtolower($siteName);

            foreach ($sizes as $size) {
                if($size->name!='Pop-unders'){
                    
                    $pbValue = "";
                                        
                    foreach($dataFile as $jter){
                        
                        if($jter->size_id == $size->id){ $pbValue = " var pb".$jter->size."='".$jter->PubID."/".$jter->SiteID."/default/".$jter->dynamic."/".$jter->file."'; "; }
                        
                    }
                    
                    $rand = 0;
                    
                    for($i=0;$i<13;$i++){ $rand = $rand.rand(0, 9); }
                              
                    $random = $rand; /* RandNumber(13); */
                    if($formData['multiple']==1)
                            $txt = "var keyword = \"".$siteInfo['SiteName']."\"; var result = null; var resultBlocked = null; var blockedURLs = ".$blockedURLs."; var approvedURLs = ".$approvedURLs.";if (top === self){ var refURL = document.location.href; } else { var refURL = document.referrer; } for(var i = 0; i < blockedURLs.length; i++){ if(refURL.indexOf(blockedURLs[i],0)!==-1){ resultBlocked = true; break; }} if(!resultBlocked){ for(var i = 0; i < approvedURLs.length; i++){ var regex = new RegExp( '\\\b' + approvedURLs[i] + '\\\b' ); if(regex.test( refURL )){ result = true; break; }} if (result){ document.write(\"<script type='text/javascript'>\");var googletag = googletag || {};googletag.cmd = googletag.cmd || [];(function() {var gads = document.createElement('script');gads.async = true;gads.type = 'text/javascript';var useSSL = 'https:' == document.location.protocol;gads.src = (useSSL ? 'https:' : 'http:') + '//www.googletagservices.com/tag/js/gpt.js';var node = document.getElementsByTagName('script')[0];node.parentNode.insertBefore(gads, node);})();document.write(\"<\/script>\");document.write(\"<script type='text/javascript'>\");googletag.cmd.push(function() {googletag.cmd.push(function() {googletag.defineSlot('/20688632/".$siteInfo['SiteName']."-(ID:".$siteInfo['user_id'].")-ROS-".$size->width."x".$size->height."', [".$size->width.", ".$size->height."], 'div-gpt-ad-".$random."').addService(googletag.pubads());googletag.enableServices();});googletag.enableServices();});document.write(\"<\/script>\");{var mam=document;var acc".$size->width."x".$size->height." = \"".$siteInfo['accountRub_id']."\"; var site".$size->width."x".$size->height." = \"".$siteInfo['siteRub_id']."\"; var zone".$size->width."x".$size->height." = \"".$siteInfo['zoneRub_id']."-".$size->rp_zonesize."\";mam.writeln(\"<div id='div-gpt-ad-".$random."' style='width:".$size->width."px; height:".$size->height."px;'>\");mam.writeln(\"<script type='text/javascript'>\");mam.writeln(\"googletag.cmd.push(function() { googletag.display('div-gpt-ad-".$random."'); });\");mam.writeln(\"<\/script>\");mam.writeln(\"<\/div>\");} }else{ var iframe = 0; var url = document.location.href; var src = 0; if(window.top !== window.self){ iframe = 1; src = document.referrer.split('/'); if(src.length > 2){ src = src[2]; } else { src = src[0]; } }document.write(\"<img src='http://pixel.madadsmedia.com/?site=".$siteInfo['site_id']."&size=".$size->id."&iframe=\"+iframe+\"&url=\"+url+\"&src=\"+src+\"' style='display:none'>\");document.write(\"<script type='text/javascript'>\");".$pbValue."var rp_account='8223';var rp_site='14911';var rp_zonesize='59842-".$size->rp_zonesize."';var rp_adtype='js';var rp_smartfile='[SMART FILE URL]';var rp_kw='".$siteInfo['site_name']."';document.write(\"<\/script>\");document.write(\"<script type='text/javascript' src='http://ads-by.madadsmedia.com/tags/ad/8223.js'>\");document.write(\"<\/script>\"); } }";
                    else
                            $txt = "var keyword = \"".$siteInfo['SiteName']."\"; var result = null; var resultBlocked = null; var blockedURLs = ".$blockedURLs."; var approvedURLs = ".$approvedURLs.";if (top === self){ var refURL = document.location.href; } else { var refURL = document.referrer; } for(var i = 0; i < blockedURLs.length; i++){ if(refURL.indexOf(blockedURLs[i],0)!==-1){ resultBlocked = true; break; }} if(!resultBlocked){ for(var i = 0; i < approvedURLs.length; i++){ var regex = new RegExp( '\\\b' + approvedURLs[i] + '\\\b' ); if(regex.test( refURL )){ result = true; break; }} if (result){ document.write(\"<script type='text/javascript'>\");var googletag = googletag || {};googletag.cmd = googletag.cmd || [];(function() {var gads = document.createElement('script');gads.async = true;gads.type = 'text/javascript';var useSSL = 'https:' == document.location.protocol;gads.src = (useSSL ? 'https:' : 'http:') + '//www.googletagservices.com/tag/js/gpt.js';var node = document.getElementsByTagName('script')[0];node.parentNode.insertBefore(gads, node);})();document.write(\"<\/script>\");document.write(\"<script type='text/javascript'>\");googletag.cmd.push(function() {googletag.cmd.push(function() {googletag.defineSlot('/20688632/".$siteInfo['SiteName']."-(ID:".$siteInfo['user_id'].")-ROS-".$size->width."x".$size->height."', [".$size->width.", ".$size->height."], 'div-gpt-ad-".$random."').addService(googletag.pubads());googletag.enableServices();});googletag.pubads().set(\"page_url\", refURL);googletag.enableServices();});document.write(\"<\/script>\");{var mam=document;var acc".$size->width."x".$size->height." = \"".$siteInfo['accountRub_id']."\"; var site".$size->width."x".$size->height." = \"".$siteInfo['siteRub_id']."\"; var zone".$size->width."x".$size->height." = \"".$siteInfo['zoneRub_id']."-".$size->rp_zonesize."\";mam.writeln(\"<div id='div-gpt-ad-".$random."' style='width:".$size->width."px; height:".$size->height."px;'>\");mam.writeln(\"<script type='text/javascript'>\");mam.writeln(\"googletag.cmd.push(function() { googletag.display('div-gpt-ad-".$random."'); });\");mam.writeln(\"<\/script>\");mam.writeln(\"<\/div>\");} }else{ var iframe = 0; var url = document.location.href; var src = 0; if(window.top !== window.self){ iframe = 1; src = document.referrer.split('/'); if(src.length > 2){ src = src[2]; } else { src = src[0]; } }document.write(\"<img src='http://pixel.madadsmedia.com/?site=".$siteInfo['site_id']."&size=".$size->id."&iframe=\"+iframe+\"&url=\"+url+\"&src=\"+src+\"' style='display:none'>\");document.write(\"<script type='text/javascript'>\");".$pbValue."var rp_account='8223';var rp_site='14911';var rp_zonesize='59842-".$size->rp_zonesize."';var rp_adtype='js';var rp_smartfile='[SMART FILE URL]';var rp_kw='".$siteInfo['site_name']."';document.write(\"<\/script>\");document.write(\"<script type='text/javascript' src='http://ads-by.madadsmedia.com/tags/ad/8223.js'>\");document.write(\"<\/script>\"); } }";

                    file_put_contents($_SERVER['DOCUMENT_ROOT']."/tags/".$siteInfo['user_id']."/".$siteInfo['site_id']."/async/".$size->file_name.".js", $txt);
                }
            } 

        }elseif($siteInfo['type']==5){              
            
            if($siteInfo['floor_pricing'] == 1){
                
                if($siteInfo['limited_demand_tag'] == 1){
                    $rp_account  = '8223';
                    $rp_site     = '19163';
                    $rp_zonesize = '60833-';
                }else{
                    $rp_account   = '8223';
                    $rp_site      = '18802';
                    $rp_zonesize  = '58616-';               
                }
                
            }else{
                
                if($siteInfo['rubiconType']==1){
                    $rp_account = '8223';
                    $rp_site = '14911';
                    $rp_zonesize = '59842-';                            
                }else{
                    $rp_account = '8223';
                    $rp_site = '19163';
                    $rp_zonesize = '60833-';
                }            
            }      
            
            
            
            $blockedSitesArray = array();
            $blockedSites = $siteModel->getBlacklist();
            $blockedSitesArray = explode("\r\n", $blockedSites['list']);
            if($blockedSitesArray){
                    $blockedURLs = '[';
                    $counArray = count($blockedSitesArray);
                    $count=0;                    		
                    foreach ($blockedSitesArray as $site){
                            $count++;
                            if($count==$counArray)
                                    $blockedURLs.= "\"".$site."\"";
                            else
                                    $blockedURLs.= "\"".$site."\",";
                    }
                    $blockedURLs.= ']';                    		
            }
            
            
            foreach ($sizes as $size) {
                    
                $pbValue = "";

                foreach($dataFile as $jter){

                    if($jter->size_id == $size->id){ $pbValue = " var pb".$jter->size."='".$jter->PubID."/".$jter->SiteID."/default/".$jter->dynamic."/".$jter->file."'; "; }

                }
                    
                if($size->name!='Pop-unders'){
                    
                    $txt = "var resultBlocked = null; var blockedURLs = ".$blockedURLs."; if (top === self){ var refURL = document.location.href; } else { var refURL = document.referrer; } for(var i = 0; i < blockedURLs.length; i++){ if(refURL.indexOf(blockedURLs[i],0)!==-1){ resultBlocked = true; break; }} if(!resultBlocked){";
                    $txt.= "var iframe = 0; var url = document.location.href; var src = 0; if(window.top !== window.self){ iframe = 1; src = document.referrer.split('/'); if(src.length > 2){ src = src[2]; } else { src = src[0]; } }document.write(\"<img src='http://pixel.madadsmedia.com/?site=".$siteInfo['site_id']."&size=".$size->id."&iframe=\"+iframe+\"&url=\"+url+\"&src=\"+src+\"' style='display:none'>\");";
                    $txt.= "document.write(\"<script type='text/javascript'>\");".$pbValue."var rp_account='".$rp_account."';var rp_site='".$rp_site."';var rp_zonesize='".$rp_zonesize."".$size->rp_zonesize."';var rp_adtype='js';var rp_smartfile='[SMART FILE URL]'; var rp_kw='".$siteInfo['site_name']."'; document.write(\"<\/script>\");document.write(\"<script type='text/javascript' src='http://ads-by.madadsmedia.com/tags/ad/8223.js'>\");document.write(\"<\/script>\");";
                    $txt.= "}";
                    file_put_contents($_SERVER['DOCUMENT_ROOT']."/tags/".$siteInfo['user_id']."/".$siteInfo['site_id']."/async/".$size->file_name.".js", $txt);
                }elseif($siteInfo['pop_unders']==1){
                    $txt = "var resultBlocked = null; var blockedURLs = ".$blockedURLs."; if (top === self){ var refURL = document.location.href; } else { var refURL = document.referrer; } for(var i = 0; i < blockedURLs.length; i++){ if(refURL.indexOf(blockedURLs[i],0)!==-1){ resultBlocked = true; break; }} if(!resultBlocked){";
                    $txt.= "if(window.top !== window.self){ document.write(\"<img src='http://pixel.madadsmedia.com/?site=".$siteInfo['site_id']."&size=6&iframe=1' style='display:none'>\"); }else{ document.write(\"<img src='http://pixel.madadsmedia.com/?site=".$siteInfo['site_id']."&size=6&iframe=0' style='display:none'>\"); }";
                    $txt.= "document.write(\"<script type='text/javascript'>\");var rp_account='8223';var rp_site='14911';var rp_zonesize='59842-20';var rp_adtype='js';var rp_smartfile='[SMART FILE URL]';var rp_kw='".$siteInfo['site_name']."';document.write(\"<\/script>\");document.write(\"<script type='text/javascript' src='http://ads-by.madadsmedia.com/tags/ad/8223.js'>\");document.write(\"<\/script>\");";
                    $txt.= "}";
                    file_put_contents($_SERVER['DOCUMENT_ROOT']."/tags/".$siteInfo['user_id']."/".$siteInfo['site_id']."/async/pop.js", $txt);                    
                }
            } 
        }
        
        //----------------iframe--------------
        if($siteInfo['iframe_tags']==1){
            if(!is_dir($_SERVER['DOCUMENT_ROOT']."/tags/".$siteInfo['user_id']))
                    mkdir($_SERVER['DOCUMENT_ROOT']."/tags/".$siteInfo['user_id']);

            if(!is_dir($_SERVER['DOCUMENT_ROOT']."/tags/".$siteInfo['user_id']."/".$siteInfo['site_id']))
                    mkdir($_SERVER['DOCUMENT_ROOT']."/tags/".$siteInfo['user_id']."/".$siteInfo['site_id']);

            if(!is_dir($_SERVER['DOCUMENT_ROOT']."/tags/".$siteInfo['user_id']."/".$siteInfo['site_id']."/iframe"))
                    mkdir($_SERVER['DOCUMENT_ROOT']."/tags/".$siteInfo['user_id']."/".$siteInfo['site_id']."/iframe");

            foreach ($sizes as $size) {
                if($size->name!='Pop-unders'){
                    $iframe = "<!-- MadAdsMedia.com Asynchronous Ad Tag For ".$siteInfo['SiteName']." -->\r\n<!-- Size: ".$size->width."x".$size->height." -->\r\n<script src=\"http://ads-by.madadsmedia.com/tags/".$siteInfo['user_id']."/".$siteInfo['site_id']."/async/".$size->file_name.".js\" type=\"text/javascript\"></script>\r\n<!-- MadAdsMedia.com Asynchronous Ad Tag For ".$siteInfo['SiteName']." -->\r\n";
                    file_put_contents($_SERVER['DOCUMENT_ROOT']."/tags/".$siteInfo['user_id']."/".$siteInfo['site_id']."/iframe/".$size->file_name.".html", $iframe);
                }
            }                        
        }            
    }
}