<?php
include 'php/config.php';
include 'php/functions.php';

$ldapconn = ldap_connect($ldapserver) or die("Could not connect to LDAP server.");

if($ldapconn) {
  // Adding options
  ldap_set_option ($ldapconn, LDAP_OPT_REFERRALS, 0);
  ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
  // binding to ldap server
  $ldapbind = ldap_bind($ldapconn, $ldapuser, $ldappass) or die ("Error trying to bind: ".ldap_error($ldapconn));
  // verify binding and adding link for redirection
  if ($ldapbind) {
    if (isset($_GET['dn'])){
      $dn=$_GET['dn'];
      $filter = "(&(objectCategory=*)(distinguishedname=$dn))";
    }elseif (isset($_GET['id'])){
      $id=$_GET['id'];
      $filter = "(&(objectCategory=*)(sAMAccountName=$id))";
    }elseif (isset($_GET['dispName'])){
      $dispName = $_GET['dispName'];
      $filter = "(&(objectCategory=*)(displayname=$dispName))";
    }
    else{
      echo("<h1>erreur de filtre</h1>");
    }

    $result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
    $data = ldap_get_entries($ldapconn, $result);

    echo '<h1>Dump all data</h1><pre>';
    print_r($data);
    echo '</pre>';
  }
}

?>
