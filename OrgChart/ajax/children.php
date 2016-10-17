<?php
/* set out document type to text/javascript instead of text/html */
header("Content-type: text/javascript");

//initialise our array to be returned
$arr['children'] = array();
$relationshipOrig = "100"; //we are getting children so does have parent

include '../../php/config.php';
include '../../php/functions.php';

$ldapconn = ldap_connect($ldapserver);

if($ldapconn) {
  // Adding options
  ldap_set_option ($ldapconn, LDAP_OPT_REFERRALS, 0);
  ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
  // binding to ldap server
  $ldapbind = ldap_bind($ldapconn, $ldapuser, $ldappass);
  // verify binding and adding link for redirection
  if ($ldapbind) {
    if (isset($_GET['dn'])){
      $dn=$_GET['dn'];
      $dn=escapeLdapFilter($dn);
      $filter = "(&(objectCategory=*)(distinguishedname=$dn))";
    }elseif (isset($_GET['id'])){
      $id=$_GET['id'];
      $filter = "(&(objectCategory=*)(sAMAccountName=$id))";
    }elseif (isset($_GET['dispName'])){
      $dispName = $_GET['dispName'];
      $filter = "(&(objectCategory=*)(displayname=$dispName))";
    }
    else{
      $arr['isError']="1";
      $arr['errorMessage']="Erreur de filtre";
      //echo("<h1>erreur de filtre</h1>");
    }

    $result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
    $data = ldap_get_entries($ldapconn, $result);

    if (!isset($data[0])){
      //bug in IE with utf-8 encoding
      $filter = utf8_encode($filter);
      $result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
      $data = ldap_get_entries($ldapconn, $result);
      //echo $filter;
    }

    //Grabbing children
    if (isset($data[0]['directreports'])){

      //do a foreach, construct array and check blacklistedDistinguishedname
      $directReports = $data[0]['directreports'];
      array_shift($directReports);
      $directReports = nonBlacklistedDistunguishednameArray($directReports,$refusedOU); //eliminating restricted OU's
      natcasesort($directReports);

      //taking care of the siblings
      if(count($directReports)>1){
        $relationshipOrig[1]="1";
      }
      $arr['test'] = $directReports;
      foreach ($directReports as $dn) {
        $relationship = $relationshipOrig;

        // connect to LDAP Server to get info
        /*$ldapconn = ldap_connect($ldapserver);
        ldap_set_option ($ldapconn, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
        // binding to ldap server
        $ldapbind = ldap_bind($ldapconn, $ldapuser, $ldappass);*/
        $dn=escapeLdapFilter($dn);
        $filter = "(&(objectCategory=*)(distinguishedname=$dn))";
        $result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
        $data = ldap_get_entries($ldapconn, $result);

        if (!isset($data[0])){
          //bug in IE with utf-8 encoding
          $filter = utf8_encode($filter);
          $result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
          $data = ldap_get_entries($ldapconn, $result);
          //echo $filter;
        }
        if(isset($data[0])){
          //need to test relationship in AD for the relationship code
          //checking for children
          if (isset($data[0]['directreports'])){
            $children = $data[0]['directreports'];
            array_shift($children);
            $children = nonBlacklistedDistunguishednameArray($children,$refusedOU);
            //asort($directReports);

            //taking care of the children
            if(count($children)>0){
              $relationship[2]="1";
            }
          }

          //check for class, see config.php for the group membership
          $orgChartClass=[FALSE,""];
          if (isset($data[0]['memberof'][0])){
            $orgChartClass = getOrgChartClass($data[0]['memberof'],$orgChartColors);
          }

          $rowArray['className']=$orgChartClass[1];

          $rowArray['name']=getOr($data[0]['displayname'][0], "Aucun Nom");
          $rowArray['title']=getOr($data[0]['title'][0],"Aucun Titre");
          $rowArray['relationship']=$relationship;
          $rowArray['DN']=removeAccents($data[0]['dn']);
          $rowArray['managerDn']=removeAccents($data[0]['manager'][0]);

          array_push($arr['children'],$rowArray);

          //$arr['children']['id':$count,'name':$test1,'title':$test2,'relationship':'111'];
        }

      }
    }

    //return our json
    echo json_encode($arr);


  } else{
    $arr['isError']="1";
    $arr['errorMessage']="Erreur Bind LDAP";
  }
} else {
  $arr['isError']="1";
  $arr['errorMessage']="Erreur connexion LDAP";
}


?>
