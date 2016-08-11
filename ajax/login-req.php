<?php
error_reporting(E_ERROR);
include '../php/config.php';
include '../php/functions.php';
$returndata = array(
  'state' =>'',
  'error' =>'',
  'fullName' =>'',
  'mail' =>'',
  'phone' =>'',
  'mobile' =>'',
  'fax' =>'',
  'manager' =>'',
  'fullNameLink' => ''
);
session_start();
if ( isset($_POST['sAMAccountName']) ) {
  $user_samaccountname = $ldapdomain ."\\".trim($_POST['sAMAccountName']);
  $sam = trim($_POST['sAMAccountName']);
  $user_password = trim($_POST['password']);
  // connect
  $ldapconn = ldap_connect($ldapserver);
  if($ldapconn){
    ldap_set_option ($ldapconn, LDAP_OPT_REFERRALS, 0);
  	ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
    // binding to ldap server
    $ldapbind = ldap_bind($ldapconn, $user_samaccountname, $user_password);

    if($ldapbind){
      //connexion Ok, grab info into session
      //echo "connexion OK";
      $returndata['state'] = true;
      $_SESSION['domainsAMAccountName'] = $user_samaccountname;
      $_SESSION['sAMAccountName'] = $sam;
      $_SESSION['password'] = $user_password;
      //connected to ldap, grab all usful info
      $filter = "(&(objectCategory=person)(samaccountname=$sam))";
      $result = ldap_search($ldapconn,$ldaptree,$filter);
      $data = ldap_get_entries($ldapconn,$result);

      $_SESSION['fullName'] = $returndata['fullName'] = $data[0]['displayname'][0];
      $_SESSION['fullNameLink'] = $returndata['fullNameLink'] = "<a href=\"detailCompte.php?id=".$sam."\">".$data[0]['displayname'][0]."</a>";
      $_SESSION['mail'] = $returndata['mail'] = getOr($data[0]['mail'][0], "Aucun mail");
      $_SESSION['phone'] = $returndata['phone'] =getOr($data[0]['telephonenumber'][0], "Aucun telephone");
      $_SESSION['mobile'] = $returndata['mobile'] = getOr($data[0]['mobile'][0], "Aucun mobile");
      $_SESSION['fax'] = $returndata['fax'] = getOr($data[0]['facsimiletelephonenumber'][0], "Aucun fax");
      //$_SESSION['manager'] = $returndata['manager'] = getOr($data[0]['manager'][0], "Aucun manager");
      if (isset($data[0]['manager'][0])){
        $_SESSION['manager'] = $returndata['manager'] = "<a href=\"detailCompte.php?dn=".$data[0]['manager'][0]."\">".explodeCN($data[0]['manager'][0])."</a>";
      }
      else{
        $_SESSION['manager'] = $returndata['manager'] = "Aucun gestionnaire";
      }
    }else{
      //connect with admin and check if account is locked or user exists
      $returndata['state'] = false;
      $ldapbind = ldap_bind($ldapconn, $ldapuser, $ldappass);
      if ($ldapbind){
        $filter = "(&(objectCategory=person)(samaccountname=$sam)(!(useraccountcontrol=514)))";
        $result = ldap_search($ldapconn,$ldaptree, $filter);
        $data = ldap_get_entries($ldapconn, $result);
        if(isset($data[0]["lockouttime"][0]) && $data[0]["lockouttime"][0]>0){
          //if account is locked out. Get timestamp
          $fileTime = $data[0]["lockouttime"][0];
          $winSecs       = (int)($fileTime / 10000000); // divide by 10 000 000 to get seconds
          $unixTimestamp = ($winSecs - 11644473600); // 1.1.1600 -> 1.1.1970 difference in seconds
          setlocale (LC_TIME, 'fr_FR.utf8','fra');
          //echo '<b>compte verouillé : </b>' .strftime("%A %d %B %Y %H:%M:%S",$unixTimestamp);
          $returndata['error'] = '<b>compte verouillé : </b>' .strftime("%A %d %B %Y %H:%M:%S",$unixTimestamp);
        }else if(isset($data[0])){
          //got user so bad password
          $returndata['error'] =  "Mauvais Mot de passe";
        }else{
          //no user
          $returndata['error'] =  "Mauvais utilisateur";
        }
      }else{
        $returndata['error'] =  "erreur bind LDAP";
      }

    }
  }
  echo json_encode($returndata);

}
?>
