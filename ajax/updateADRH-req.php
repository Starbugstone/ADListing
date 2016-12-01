<?php
error_reporting(E_ERROR);
include '../php/config.php';
include '../php/functions.php';
//include '../php/vars.php';

//add a return data array to grab results
$returndata = array(
  'state' =>'',
  'error' =>'',
  'responseName' => ''
);

//grab all our posted variables



$samaccountname = $_POST["samaccountname"];


if(!isset($_SESSION))
  {
    session_start();
  }
if (isset($_SESSION['domainsAMAccountName'])) {
  //check for session, no need to go further if no session and could be security risk
  $ldapconn = ldap_connect($ldapserver);
  if ($ldapconn) {

      ldap_set_option ($ldapconn, LDAP_OPT_REFERRALS, 0);
    	ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
      // binding to ldap server
      $ldapbind = ldap_bind($ldapconn, $_SESSION['domainsAMAccountName'], $_SESSION['password']);
      if ($ldapbind) {
        //ok we're in with the user's credentials. Let's update.

        //check if we go into super admin mode (damn feals bad doing this but no other way, have to go through ALL security)
        //At least we did a regular user connexion check and a session check before.
        if ($bypassUserRights){
          $ldapbind = ldap_bind($ldapconn, $ldapAdminuser, $ldapAdminpass);
        }
        $returndata['state']=true;


        //1st grab the actual info so we can check later
        //$sessionSAM = $_SESSION['sAMAccountName'];
        $filter = "(&(objectCategory=person)(sAMAccountName=$samaccountname))";
        $result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
        $data = ldap_get_entries($ldapconn, $result);

        $ldapParamDn = $data[0]['dn'];

        //construction on update
        //array of authorised updates. For security we check if allowed to update
        $RHUpdateKeys = array('sn','givenname','displayname','employeeid','rpps','description','telephonenumber','mobile','facsimiletelephonenumber','company');
        $userdata=array();
        //go through all that was posted

        $modifiedLog = array();
        foreach($_POST as $LDAPkey => $value){
          if(in_array($LDAPkey,$RHUpdateKeys)){
            //error_log('key : '.$LDAPkey.', value : '.$value);
            //check if value is diffrent in AD, if yes then add to update field
            if ($data[0][$LDAPkey][0] != $value){
              //error_log('updateKey :'.$LDAPkey.'->'.$value.' | OldKey=>'.$data[0][$LDAPkey][0].' | LdapKey=>'.$LDAPkey);
              array_push($modifiedLog,'cle mise a jour :'.$LDAPkey.'=>'.$value."\r\n".'Ancien Valeur=>'.$data[0][$LDAPkey][0]."\r\n");
              //$userdata[$LDAPkey][0] = $value;
              //run update
              if($value != null && !empty($value)){
                $userdata[$LDAPkey][0] = $value;
                ldap_modify($ldapconn,$ldapParamDn,$userdata);
              }else{
                $userdata[$LDAPkey][0] = $data[0][$LDAPkey][0];
                ldap_mod_del($ldapconn, $ldapParamDn,$userdata);
              }
            }
          }

        }
        //add log
        $logPath = $logFolder.$samaccountname.'.txt';
        $modifiedLogTitle = ' ---RH-Update---'."\r\n".'Compte '.$samaccountname.' modifier par '.$_SESSION['domainsAMAccountName'].' le '.date("Y-m-d H:i:s")."\r\n";
        file_put_contents($logPath,$modifiedLogTitle,FILE_APPEND);
        foreach ($modifiedLog as $key => $value) {
          file_put_contents($logPath,$value,FILE_APPEND);
        }

      }
      else {
        //LDAP Bind failed
        $returndata['state']=false;
        $returndata['error']="Erreur Bind LDAP";
      }

  }
  else {
    //ldap connect failed
    $returndata['state']=false;
    $returndata['error']="Erreur Connect LDAP";
  }

}
else{
  #error no session
  $returndata['state']=false;
  $returndata['error']="aucun session";
}

ldap_close();
//return our result data in json for to be used by our ajax call
echo json_encode($returndata);
?>
