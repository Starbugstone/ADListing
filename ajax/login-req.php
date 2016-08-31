<?php
error_reporting(E_ERROR);
include '../php/config.php';
include '../php/functions.php';
include '../php/vars.php';
$returndata = array(
  'state' =>'',
  'error' =>'',
  'responseName' => '' // these 3 are neede for the main reply, the rest will be added dynamicly with the values from vars.php later
);
if(!isset($_SESSION))
  {
    session_start();
  }
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
      $returndata['state'] = true;
      $_SESSION['domainsAMAccountName'] = $user_samaccountname;
      $_SESSION['sAMAccountName'] = $sam;
      $_SESSION['password'] = $user_password;
      //connected to ldap, grab all usful info
      $filter = "(&(objectCategory=person)(samaccountname=$sam))";
      $result = ldap_search($ldapconn,$ldaptree,$filter);
      $data = ldap_get_entries($ldapconn,$result);

      $_SESSION['responseName'] = $returndata['responseName'] = $data[0]['displayname'][0];
      //get all the info from our vars.php file
      foreach ($loggedinInfo as $row => $param) {
        //add extra element to array for return
        $returndata[$row]='';
        //verify if we have a value in LDAP
        if (isset($data[0][$param['ldapName']][0])){
          //check if we need to explode CN and return Value and store value in $ldapVal
          if ($param['ldapNameExplodeCN']){
            $ldapVal = explodeCN($data[0][$param['ldapName']][0]);
          }else{
            $ldapVal = $data[0][$param['ldapName']][0];
          }

          //transform into link if needed
          if ($param['isLink']){
            $_SESSION[$row] = $returndata[$row] = "<a href=\"".$param['linkPage'].$data[0][$param['linkPageLdapVar']][0]."\">".$ldapVal."</a>";
          }else{
            $_SESSION[$row] = $returndata[$row] = $ldapVal;
          }
        }else{
          //no ldap value, return error configured in vars.php
          $_SESSION[$row] = $returndata[$row] = $param['ldapErrorVal'];
        }
      }
    }else{
      //connexion refused for user, connect with admin and check if account is locked or user exists and return detailed error
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
          $returndata['error'] = '<b>compte verouillé : </b>' .strftime("%A %d %B %Y %H:%M:%S",$unixTimestamp);
        }else if(isset($data[0])){
          //got user and not locked out so bad password
          $returndata['error'] =  "Mauvais Mot de passe";
        }else{
          //no user
          $returndata['error'] =  "Mauvais utilisateur";
        }
      }else{
        //we had a bind error
        $returndata['error'] =  "erreur bind LDAP";
      }

    }
  }
  ldap_close();
  echo json_encode($returndata);

}
?>
