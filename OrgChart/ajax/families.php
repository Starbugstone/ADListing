<?php
/* set out document type to text/javascript instead of text/html */
header("Content-type: text/javascript");

//initialise our array to be returned
$arr = array();
$arr['children'] = array();
$relationshipParent = "001"; //we are on famalies so parent has children
$relationshipSiblings  = "110"; //we must have siblings

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
    //get the selfDN to not return self as siblings
    if(isset($_GET['selfDn'])){
      $selfDn = $_GET['selfDn'];
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

    //Grabing the parent


    //get parent data
    if(isset($data[0]['manager'][0]) AND blacklistedDistinguishedname($data[0]['manager'][0],$refusedOU) == FALSE){
      //has parent
      $relationshipParent[0]="1";
      //check for siblings
      $dn=$data[0]['manager'][0];
      $dn=escapeLdapFilter($dn);
      $filter = "(&(objectCategory=*)(distinguishedname=$dn))";
      $result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
      $dataParent = ldap_get_entries($ldapconn, $result);
      if (isset($dataParent[0]['directreports'])){
        $children = $dataParent[0]['directreports'];
        $children = nonBlacklistedDistunguishednameArray($children,$refusedOU); //eliminating restricted OU's
        array_shift($children);
        //asort($directReports);

        //taking care of the siblings
        if(count($children)>1){
          $relationshipParent[1]="1";
        }
      }

    }

    //check for class, see config.php for the group membership
    $orgChartClass=[FALSE,""];
    if (isset($data[0]['memberof'][0])){
      $orgChartClass = getOrgChartClass($data[0]['memberof'],$orgChartColors);
    }

    $arr['className']=$orgChartClass[1];

    $arr['name']=getOr($data[0]['displayname'][0], "Aucun Nom");
    $arr['title']=getOr($data[0]['title'][0],"Aucun Titre");
    $arr['relationship']=$relationshipParent;
    $arr['DN']=removeAccents($data[0]['dn']);
    $arr['managerDn']=removeAccents(getOr($data[0]['manager'][0], ""));


    //now we get sibling data (parent children)

    //get rid of the first line
    if (isset($data[0]['directreports'])){
      $directReports = $data[0]['directreports'];
      array_shift($directReports);
      $directReports = nonBlacklistedDistunguishednameArray($directReports,$refusedOU); //eliminating restricted OU's
      asort($directReports);

      if(count($directReports)>0){

        foreach ($directReports as $dn) {
          $relationshipSib = $relationshipSiblings; //resetting the relationship to avoid polution

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
          //check if we have results AND we are not returning the same user
          //the results should be good and the isset is probably not necessary. Just habit !!
          if(isset($data[0]) AND removeAccents($data[0]['dn']) != $selfDn){


            //need to test relationship in AD for the relationship code
            //they have parent
            if (isset($data[0]['directreports'])){
              unset($children);
              $children = $data[0]['directreports'];
              array_shift($children);
              $children = nonBlacklistedDistunguishednameArray($children,$refusedOU);


              //taking care of the children
              if(count($children)>0){
                $relationshipSib[2]="1";
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
            $rowArray['relationship']=$relationshipSib;
            $rowArray['DN']=removeAccents($data[0]['dn']);
            $rowArray['managerDn']=removeAccents($data[0]['manager'][0]);

            array_push($arr['children'],$rowArray);

            //$arr['children']['id':$count,'name':$test1,'title':$test2,'relationship':'111'];
          }

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
