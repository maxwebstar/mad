<?php
class Application_Plugin_PassbackTag extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(){}
    
    public function first($dataFile, $path, $tagType = 4, $storeFullUrl = 0)
    {         
            $dataFile['data'] = str_replace('<!--//<![CDATA[', '', $dataFile['data']);
            $dataFile['data'] = str_replace('//]]>-->', '', $dataFile['data']); 
        
            if($tagType == 4){ $str = "<img src='\"+prot+\"://pixel.madadsmedia.com/?site=".$dataFile['SiteID']."&size=".$dataFile['size_id']."&nofill=\"+nofill+\"&iframe=\"+iframe+\"&url=\"+url+\"&src=\"+src+\"&psa=\"+psa+\"&store=".$storeFullUrl."' style='display:none'/>"; }
                         else{ $str = "<img src='http://pixel.madadsmedia.com/?site=".$dataFile['SiteID']."&size=".$dataFile['size_id']."&nofill=\"+nofill+\"&iframe=\"+iframe+\"&url=\"+url+\"&src=\"+src+\"&psa=\"+psa+\"&store=".$storeFullUrl."' style='display:none'/>"; }
            
            /*$str = "<img src='http://pixel.madadsmedia.com/?site=".$dataFile['SiteID']."&size=".$dataFile['size_id']."&nofill=1&iframe=\"+iframe+\"&url=\"+url+\"&src=\"+src+\"' style='display:none'/>";*/
            $content = 'document.write("'.$str.'");';

            /* check google ad tag */
            $text = $dataFile['data'];        

            $content2 = '';
            $is_matched = preg_match_all( '/(?P<scriptstart>.*?<script.*?>)(?P<scripttext>.*?)(?P<scriptend><\/script>)/ms',
                $text,
                $results,
                PREG_SET_ORDER );
            if( $is_matched ){
                unset( $text );
                foreach( $results as $result ){
                    $result[ 'scripttext' ] = preg_replace( '/(.*?)<!--(.*?google_ad_client.*?)-->(.*?)/ms',
                        '$1$2$3',
                        $result[ 'scripttext' ] );
                    $content2 .= $result[ 'scriptstart' ] . $result[ 'scripttext' ] . $result[ 'scriptend' ];
                }
            } else  $content2 = $dataFile['data']; 
           /* end google ad tags */

            $str1 = preg_split("/[\n]/", $content2);

            foreach($str1 as $iter){

                $iter = str_replace('"', '\"', $iter);
                $iter = str_replace('/', '\/', $iter);

                $content .= 'document.write("'.$iter.'");';
            }

            if($tagType == 4){ $content = "var prot = document.location.protocol; var iframe = 0; var url = document.location.href; var src = 0; if(window.top !== window.self){ iframe = 1; src = document.referrer; } if(!window.nofill){ var nofill = 0; } ".$content;  }
                         else{ $content = "var iframe = 0; var url = document.location.href; var src = 0; if(window.top !== window.self){ iframe = 1; src = document.referrer; } if(!window.nofill){ var nofill = 0; } ".$content; }
            
            /*$content = "var iframe = 0; var url = document.location.href; var src = 0; if(window.top !== window.self){ iframe = 1; src = document.referrer ? document.referrer : document.location.href; }".$content;*/

            /*$str2 = preg_split('/(http:[^\'"<>]+)/i', $content, null, 2);*/

            file_put_contents($path.'/'.$dataFile['file'], $content);
        
    }
    
    
    public function second($dataFile, $path, $iframe = false, $tagType = 4, $storeFullUrl = 0)
    {  
         $dataFile['data'] = str_replace('<!--//<![CDATA[', '', $dataFile['data']);
         $dataFile['data'] = str_replace('//]]>-->', '', $dataFile['data']);
        
        if(!$iframe){ 
        
                $str = "<img src='http://pixel.madadsmedia.com/?site=".$dataFile['SiteID']."&size=".$dataFile['size_id']."&nofill=\"+nofill+\"&iframe=\"+iframe+\"&url=\"+url+\"&src=\"+src+\"' style='display:none'/>";
                $content = 'document.write("'.$str.'");';

                /* check google ad tag */
                $text = $dataFile['data'];        

                $content2 = '';
                $is_matched = preg_match_all( '/(?P<scriptstart>.*?<script.*?>)(?P<scripttext>.*?)(?P<scriptend><\/script>)/ms',
                    $text,
                    $results,
                    PREG_SET_ORDER );
                if( $is_matched ){
                    unset( $text );
                    foreach( $results as $result ){
                        $result[ 'scripttext' ] = preg_replace( '/(.*?)<!--(.*?google_ad_client.*?)-->(.*?)/ms',
                            '$1$2$3',
                            $result[ 'scripttext' ] );
                        $content2 .= $result[ 'scriptstart' ] . $result[ 'scripttext' ] . $result[ 'scriptend' ];
                    }
                } else  $content2 = $dataFile['data']; 
               /* end google ad tags */

                $str1 = preg_split("/[\n]/", $content2);

                foreach($str1 as $iter){

                    $iter = str_replace('"', '\"', $iter);
                    $iter = str_replace('/', '\/', $iter);

                    $content .= 'document.write("'.$iter.'");';
                }

                $content = "var iframe = 0; var url = document.location.href; var src = 0; if(window.top !== window.self){ iframe = 1; src = document.referrer ? document.referrer : document.location.href; } if(!window.nofill){ var nofill = 0; } ".$content;

                /*$str2 = preg_split('/(http:[^\'"<>]+)/i', $content, null, 2);*/
                
                file_put_contents($path.'/'.$dataFile['file'], $content);

                $dataFile['iframe'] = null;
        
        }else{            
                $tableSize = new Application_Model_DbTable_Sizes();
                $dataSize = $tableSize->getData($dataFile['size_id']);
                         
                $content  = " if(typeof iframe === 'undefined'){ var iframe = window.top !== window.self ? 1 : 0; } ";
                $content .= " if(typeof url === 'undefined'){ var url = document.location.href; } ";
                $content .= " if(typeof src === 'undefined'){ if(iframe){ var src = document.referrer ? document.referrer : document.location.href; } else { var src = 0; } } ";
                $content .= " if(typeof psa === 'undefined'){ var psa = 0; } ";
                $content .= " if(typeof store === 'undefined'){ var store = 0; } ";
                $content .= " if(!window.nofill){ var nofill = 0; } ";
       
                $content .= " document.write(\"<img src='http://pixel.madadsmedia.com/?site=".$dataFile['SiteID']."&size=".$dataFile['size_id']."&nofill=\"+nofill+\"&iframe=\"+iframe+\"&url=\"+url+\"&src=\"+src+\"&psa=\"+psa+\"&store=\"+store+\"' style='display:none'>\"); ";
                $content .= " document.write(\"<iframe src='http://ads-by.madadsmedia.com/tags/".$dataFile['PubID']."/".$dataFile['SiteID']."/default/".$dataFile['dynamic']."/iframe/".$dataFile['size'].".html' width='".$dataSize['width']."' height='".$dataSize['height']."' frameborder='0' scrolling='no' marginwidth='0' marginheight='0'></iframe>\"); ";

                file_put_contents($path.'/'.$dataFile['file'], $content);  
                
                $dataFile['iframe'] = 1;
                
                file_put_contents($path.'/iframe/'.$dataFile['size'].'.html', "<html><head></head><body>".$dataFile['data']."</body></html>");
        }    
    }
    
}
?>
