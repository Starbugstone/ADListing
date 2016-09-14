<?php
/* set out document type to text/javascript instead of text/html */
header("Content-type: text/javascript");

//initialise our array to be returned
$arr = array(
  "type"=>"",
  "isError"=>"0",
  "errorMessage"=>""
);

include '../php/config.php';
include '../php/functions.php';

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

    //testing if user or group and construction a URL
    $objectType = $data[0]['objectclass'][1];
    if($objectType=="person"){
      //echo "USER";

      $arr['type']="USER";
    }
    elseif($objectType=="group"){
      //echo "Groupe";

      $arr['type']="GROUP";
    }
    else{
      //
      //echo "SHIT - UNKNOWN. Get a debugger in here";
      $arr['type']="Unknown";
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
