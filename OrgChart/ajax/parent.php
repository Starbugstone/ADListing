<?php
/* set out document type to text/javascript instead of text/html */
header("Content-type: text/javascript");

//initialise our array to be returned
$arr = array();
$relationship = "001"; //we are getting parent so must have children

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

    //Grabing the parent
    $dn=escapeLdapFilter($dn);
    $filter = "(&(objectCategory=*)(distinguishedname=$dn))";
    $result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
    $data = ldap_get_entries($ldapconn, $result);

    //Checking the relationship
    if(isset($data[0]['manager'][0]) AND blacklistedDistinguishedname($data[0]['manager'][0],$refusedOU) == FALSE){
      //has parent
      $relationship[0]="1";
      //check for siblings
      $dn=$data[0]['manager'][0];
      $dn=escapeLdapFilter($dn);
      $filter = "(&(objectCategory=*)(distinguishedname=$dn))";
      $result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
      $dataParent = ldap_get_entries($ldapconn, $result);
      //if parent has more than 1 children not in restricted OU then has siblings
      if (isset($dataParent[0]['directreports'])){
        $children = $dataParent[0]['directreports'];
        $children = nonBlacklistedDistunguishednameArray($children,$refusedOU); //eliminating restricted OU's
        array_shift($children); //getting rid of the unwanted array
        //$arr['test']=$children;
        //taking care of the siblings
        if(count($children)>1){
          $relationship[1]="1";
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
    $arr['relationship']=$relationship;
    $arr['DN']=removeAccents($data[0]['dn']);
    $arr['managerDn']=removeAccents(getOr($data[0]['manager'][0], ""));
    //$arr['test']=$relationship;






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
