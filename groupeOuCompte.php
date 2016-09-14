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
      $link = "?dn=".$dn;
    }elseif (isset($_GET['id'])){
      $id=$_GET['id'];
      $filter = "(&(objectCategory=*)(sAMAccountName=$id))";
      $link = "?id=".$id;
    }elseif (isset($_GET['dispName'])){
      $dispName = $_GET['dispName'];
      $filter = "(&(objectCategory=*)(displayname=$dispName))";
      $link = "?dispName=".$dispName;
    }
    else{
      echo("<h1>erreur de filtre</h1>");
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

    /*
    echo $dn;
    echo "<p>".mb_detect_encoding($dn)."</p>";
    echo "<br>";
    $dn2 = utf8_encode($dn);
    echo $dn2;
    echo "<p>".mb_detect_encoding($dn2)."</p>";
    print_r($data[0]);
    */

    //testing if user or group and construction a URL
    $objectType = $data[0]['objectclass'][1];
    if($objectType=="person"){
      echo "USER";
      echo "<a href=\"detailCompte.php".$link."\">link</a>";
      $url = "detailCompte.php".$link;
    }
    elseif($objectType=="group"){
      echo "Groupe";
      echo "<a href=\"detailGroupe.php".$link."\">link</a>";
      $url = "detailGroupe.php".$link;
    }
    else{
      //
      echo "SHIT - UNKNOWN. Get a debugger in here";
      $url="index.php";
    }

    //redirect to proper file
    header('Location: '.$url);
    //echo "<br>".utf8_decode($url);
    exit();
  }
} else {
  echo "LDAP bind failed...";
}


?>
