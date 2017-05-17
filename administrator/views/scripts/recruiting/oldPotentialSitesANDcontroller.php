 public function dataAction() {
//
//        $this->_helper->layout()->disableLayout();
//        $this->_helper->viewRenderer->setNoRender();
//        $status = $this->getRequest()->getParam("status");
//       $loginTable = new Zend_Db_Table('madads_login_activity');
//        $select = $loginTable->select();
//        //$select->from(array("e" => "madads_login_activity"))->setIntegrityCheck(false)
//        //        ->join(array("s" => "users"), '`s`.id = `e`.user_id', "s.email AS user");
//
////        $sSearch = $this->getRequest()->getParam("sSearch");
////
////        if (strlen($sSearch) >= 3):
////            $select->where("e.ip_address LIKE '%" . $sSearch . "%' OR e.activity_type LIKE '%" . $sSearch . "%' OR s.email LIKE '%" . $sSearch . "%'");
////        endif;
////
////        $sSortDir_0 = $this->getRequest()->getParam("sSortDir_0");
////        $iSortCol_0 = (int) $this->getRequest()->getParam("iSortCol_0");
////
////        if (is_numeric($iSortCol_0)):
////            switch ($iSortCol_0):
////                case 0 :
////                    $select->order("e.id " . $sSortDir_0);
////                    break;
////                case 1 :
////                    $select->order("e.ip_address " . $sSortDir_0);
////                    break;
////                case 2 :
////                    $select->order("e.activity_timestamp " . $sSortDir_0);
////                    break;
////                case 3 :
////                    $select->order("e.activity_type " . $sSortDir_0);
////                    break;
////                case 4 :
////                    $select->order("s.email " . $sSortDir_0);
////                    break;
////            endswitch;
////        endif;
//
////        $loginActivities = $loginTable->fetchAll($select);
//        $loginActivities = $loginTable->fetchAll();
//
//        $sEcho = (int) $_GET['sEcho'];
//        $output = array(
//            "select" => $select->__toString(),
//            "sEcho" => $sEcho++,
//            "iTotalRecords" => count($loginActivities),
//            "iTotalDisplayRecords" => count($loginActivities),
//            "sColumns" => 7,
//            "aaData" => array()
//        );
//
//        $aaData = array();
//        #if (count($loginActivities) > 0):
//
//            #$select->limit((int) $_GET['iDisplayLength'], (int) $_GET['iDisplayStart']);
//            #$select->assemble();
//           # $loginActivities = $loginTable->fetchAll($select);
//
//            foreach ($loginActivities as $loginActivity):
//                $loginData = array();
//                $loginData[0] = $loginActivity->id;
//                $loginData["id"] = $loginActivity->id;
//                $loginData[1] = $loginActivity->ip_address;
//                $loginData["ip_address"] = $loginActivity->ip_address;
//                $loginData[2] = $loginActivity->activity_timestamp;
//                $loginData["activity_timestamp"] = $loginActivity->activity_timestamp;
//                $loginData[3] = $loginActivity->activity_type;
//                $loginData["activity_type"] = $loginActivity->activity_type;
////                $loginData[4] = $loginActivity->user;
////                $loginData["user"] = $loginActivity->user;
//                $aaData[] = $loginData;
//            endforeach;
//
//            $output['aaData'] = $aaData;
//
//        #endif;
//        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);
    }

$loginTable = new Zend_Db_Table('madads_login_activity');
        $select = $loginTable->select();
        //$select->from(array("e" => "madads_login_activity"))->setIntegrityCheck(false)
        //        ->join(array("s" => "users"), '`s`.id = `e`.user_id', "s.email AS user");
        
        $select->join('users', 'madads_login_activity.user_id=users.id', array('id' => 'madads_login_activity.id',
                        "ip_address" => "madads_login_activity.ip_address",
                        'activity_timestamp' => 'madads_login_activity.activity_timestamp', 'activity_type' =>'madads_login_activity.activity_type',
                        'user' => 'users.email'
                        //,
                        //'countWS' => 'count(DISTINCT website)'))
                        ))
                    //->order(array('alexa ASC', 'website'))
                    //->order($orderArray)
                   // ->limit(5000)
                    //->limit((int)$_GET['iDisplayLength'],(int)$_GET['iDisplayStart'])
                    ;
        
        $sSearch = $this->getRequest()->getParam("sSearch");

        if (strlen($sSearch) >= 3):
            $select->where("e.ip_address LIKE '%" . $sSearch . "%' OR e.activity_type LIKE '%" . $sSearch . "%' OR s.email LIKE '%" . $sSearch . "%'");
        endif;

        $sSortDir_0 = $this->getRequest()->getParam("sSortDir_0");
        $iSortCol_0 = (int) $this->getRequest()->getParam("iSortCol_0");

        if (is_numeric($iSortCol_0)):
            switch ($iSortCol_0):
                case 0 :
                    $select->order("madads_login_activity.id " . $sSortDir_0);
                    break;
                case 1 :
                    $select->order("madads_login_activity.ip_address " . $sSortDir_0);
                    break;
                case 2 :
                    $select->order("madads_login_activity.activity_timestamp " . $sSortDir_0);
                    break;
                case 3 :
                    $select->order("madads_login_activity.activity_type " . $sSortDir_0);
                    break;
                case 4 :
                    $select->order("users.email " . $sSortDir_0);
                    break;
            endswitch;
        endif;

        //$loginActivities = $loginTable->fetchAll($select);
        $loginActivities = $loginTable->fetchAll();

        $sEcho = (int) $_GET['sEcho'];
        $output = array(
            "select" => $select->__toString(),
            "sEcho" => $sEcho++,
            "iTotalRecords" => count($loginActivities),
            "iTotalDisplayRecords" => count($loginActivities),
            "sColumns" => 7,
            "aaData" => array()
        );

        $aaData = array();
        #if (count($loginActivities) > 0):

            #$select->limit((int) $_GET['iDisplayLength'], (int) $_GET['iDisplayStart']);
            #$select->assemble();
           # $loginActivities = $loginTable->fetchAll($select);

            foreach ($loginActivities as $loginActivity):
                $loginData = array();
                $loginData[0] = $loginActivity->id;
                $loginData["id"] = $loginActivity->id;
                $loginData[1] = $loginActivity->ip_address;
                $loginData["ip_address"] = $loginActivity->ip_address;
                $loginData[2] = $loginActivity->activity_timestamp;
                $loginData["activity_timestamp"] = $loginActivity->activity_timestamp;
                $loginData[3] = $loginActivity->activity_type;
                $loginData["activity_type"] = $loginActivity->activity_type;
//                $loginData[4] = $loginActivity->user;
//                $loginData["user"] = $loginActivity->user;
                $aaData[] = $loginData;
            endforeach;

            $output['aaData'] = $aaData;

        #endif;
        $this->getResponse()->setBody(Zend_Json::encode($output))->setHeader('content-type', 'application/json', true);


<!-- AFTER BRACKET IN DATATABLE POTENTIALSITES VIEW-->
,
            "fnDrawCallback": function() {
                $(".response").click(function(e) {

                    if ($(this).is(":checked")) {
                        var email_id = $(this).attr("email_id");
                        var response = ($(this).attr("name") === "responded") ? "responded" : "never_responded";

                        if (response === "responded" && $(this).next("input").is(":checked"))
                            $(this).next("input").attr("checked", null);
                        else if (response === "never_responded" && $(this).prev("input").is(":checked"))
                            $(this).prev("input").attr("checked", null);

                        $.get("/administrator/recruiting/response", {"email_id": email_id, "response": response}, function(res) {

                            if (!res.error)
                                $pendingTable.fnClearTable(true);
                        });
                    }
                });
            },
            "aaSorting": [[0, "asc"]]

<!-- OLD DATA PART-->
//                    $emailData = array();
//                    $emailData[0] = $emailJoin->website_id;
//                    $emailData["website_id"] = $emailJoin->website_id;
//                    $emailData[1] = $emailJoin->email_id;
//                    $emailData["email_id"] = $emailJoin->email_id;
//                    $emailData[2] = $emailJoin->website;
//                    $emailData["website"] = $emailJoin->website;
//                    $emailData[3] = $emailJoin->email;
//                    $emailData["email"] = $emailJoin->email;
//                    $emailData[4] = $emailJoin->alexa;
//                    $emailData["alexa"] = $emailJoin->alexa;
//                    $aaData[] = $emailData;

<!-- OLD CONTROLLER POTENTIALSITESACTION PART -->

//        $select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)
//                ->setIntegrityCheck(false);
//
//        $select->where('base_website_id IS NULL AND accepted IS NULL')
//                ->join('recruiting_found_emails', 'recruiting_searches.id=recruiting_found_emails.website_id', array('website_id' => 'recruiting_searches.id',
//                    "email_id" => "recruiting_found_emails.id",
//                    'website' => 'recruiting_searches.website', 'email',
//                    'alexa' => 'recruiting_searches.alexa'))
//                ->order('website')
//                ->limit(40, $this->_getParam("page") * 40);
//        $emailJoins = $table->fetchAll($select);
//
//        $table2 = new Zend_Db_Table('recruiting_found_emails');
//        $rows2 = $table2->fetchAll();
//        $maxPages = count($rows2);
//
//        $currentID = 0;
//        $lastPage = 0;
//        $counts = array();
//        $alexa = array();
//        foreach ($emailJoins as $emailJoin) {
//            if ($currentID != $emailJoin->website_id) {
//                $currentID = $emailJoin->website_id;
//                $counts[$currentID] = 1;
//                $lastPage = floor($maxPages / 40);
//                try {
//                    $data = file_get_contents("http://data.alexa.com/data?cli=10&dat=snbamz&url=" . $emailJoin->website);
//                    $xml = new SimpleXMLElement($data);
//                    $popularity = $xml->xpath("//POPULARITY");
//                    $rank = (string) $popularity[0]['TEXT'];
//                    $alexa[$emailJoin->website_id] = $rank;
//                } catch (Exception $e) {
//                    
//                }
//            } else {
//                $counts[$currentID]++;
//            }
//        }
//
//        $firstPage = 0;
//        $page = 0;
//        $page = $this->_getParam("page");
//        if ($page == 0 || $page == 1 || $page == 2) {
//            
//        } else {
//            $firstPage = $page - 3;
//        }
//
//        $this->view->maxPages = $maxPages;
//        $this->view->emailJoins = $emailJoins;
//        $this->view->firstPage = $firstPage;
//        $this->view->counts = $counts;
//        $this->view->lastPage = $lastPage;
//        $this->view->page = $page;
//        $this->view->alexa = $alexa;

<!-- OLD VIEW PART -->
<?php
$counts = $this->counts;
$lastPage = $this->lastPage;
$page = $this->page;
$firstPage = $this->firstPage;
$alexa = $this->alexa;
?>
<style type="text/css">
    #headerDiv{
        width: 62%; font-size: 20px;
    }
    #websiteHeader{
        padding-top: 15px; width: 62%;
    }
    #wsHeadCell1{
        font-size: 15px; width: 50%; background-color: #FFCC66;
    }
    #wsHeadCell2{
        font-size: 15px; width: 15%; background-color: #FFCCCC;
    }
    #wsHeadCell3{
        font-size: 15px; width: 19%; background-color: #FFCC66;
    }
    #wsHeadCell4{
        font-size: 15px; width: 6%;background-color: #FFCCCC;
    }
    #wsHeadCell5{
        font-size: 15px; width: 10%; background-color: #FFCC66;
    }
    #emailCell1{
        width: 33%; background-color:#FED3D3;
    }
    #emailCell2{
        width: 33%; background-color:#FFEAEA;
    }
</style>


<div id="headerDiv">
    Potential Recruiting Websites
    <br/>
    <table><tr><td>Pages...</td>
            <?php
            if ($firstPage > 3) {
                ?><td><a href="potentialsites?page=0">1</a></td><td>...</td><?php
            }
            for ($x = $firstPage; $x < min($firstPage + 6, $lastPage); $x++) {
                ?>
                <td style="padding: 2px; width: 8px;">
                    <a href="potentialsites?page=<?= $x ?>"><?= $x + 1 ?></a>
                </td>
                <?php
            }
            if ($lastPage > $firstPage + 6) {
                ?><td>...</td><td><a href="potentialsites?page=<?= $lastPage ?>"><?= $lastPage + 1 ?></a></td><?php
            }
            ?></tr></table>
    <br/>
    <div>
        Max 40 Emails per page. Websites may have more entries on the next page.
    </div>
</div>
<?php
$currentID = 0;
$first = true;
$number = 0;
foreach ($this->emailJoins as $emailJoin) {
    if ($currentID != $emailJoin->website_id) {
        $currentID = $emailJoin->website_id;
        $number = 1;
        if ($first) {
            $first = false;
        } else {
            ?></table><?php
        }
        ?>
        <table id="websiteHeader">
            <tr>
                <td id="wsHeadCell1">
                    Website: <?= $emailJoin->website ?>
                </td>
                <td id="wsHeadCell2">
                    Emails: <?= $counts[$emailJoin->website_id] ?>
                </td>
                <td id="wsHeadCell3">
                    Alexa:<?= $alexa[$emailJoin->website_id]; ?>
                </td>
                <td id="wsHeadCell4">
                    <a href="potentialsites?page=<?= $page ?>&ws=<?= $emailJoin->website ?>&add=t&wsid=<?= $emailJoin->website_id ?>">Add</a>
                </td>
                <td id="wsHeadCell5">
                    <a href="potentialsites?page=<? $page ?>&add=f&wsid=<?= $emailJoin->website_id ?>">Remove</a>
                </td>
            </tr>
        </table>
        <table id="body<?= $emailJoin->website_id ?>" style="width: 62%;">
            <?php
        }
        if (($number % 3) == 1) {
            ?><tr><?php }
        ?>
            <td id="<?php
            if ($number % 2 == 1) {
                echo"emailCell1";
            } else {
                echo "emailCell2";
            }
            ?>"><?= $emailJoin->email ?></td>
            <?php if (($number % 3) == 0) { ?></tr><?php
        }
        $number++;
    }
    ?>
</table>
