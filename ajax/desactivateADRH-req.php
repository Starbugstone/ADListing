<?php
error_reporting(E_ERROR);
include '../php/config.php';
include '../php/functions.php';
//require_once "../phpMailer/PHPMailerAutoLoad.php";
//include '../php/vars.php';

//add a return data array to grab results
$returndata = array(
  'state' =>'',
  'error' =>'',
  'responseName' => ''
);

//grab the account to enable or disable

$samaccountname = $_POST["samaccountname"];


if(!isset($_SESSION))
  {
    session_start();
  }
if (isset($_SESSION['domainsAMAccountName']) && $_SESSION['ldapRHAdminGroup'] == TRUE) {
  //check for session, no need to go further if no session and could be security risk
  //would be good to do a chech if the session has the rights to desactivate account
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

        $displayname = getOr($data[0]["displayname"][0],$data[0]["cn"][0]);

        $memberOf = $data[0]['memberof'];
        //chech if the account is desactivatable
        if (!in_array($nonDisactivatableAccountGroup,$memberOf)){
          //recuperation mail manager
          $managerMail = "";
          if (isset($data[0]['manager'][0])){
            $managerDN = $data[0]['manager'][0];
            $filterManager = "(&(objectCategory=person)(distinguishedname=$managerDN))";
            $resultManager = ldap_search($ldapconn,$ldaptree, $filterManager);
            $dataManager = ldap_get_entries($ldapconn, $resultManager);
            $managerMail = getOr($dataManager[0]['mail'][0],"");
          }
          $ldapParamDn = $data[0]['dn'];
          $useraccountcontrol=$data[0]["useraccountcontrol"][0];
          //construction on update
          if (accountIsNotActive($useraccountcontrol)){
            $dataActive = 0;
            $returndata['activated']=1;
            $modifiedLogTitle = ' ---RH-Activate---'."\r\n".'Compte '.$samaccountname.' Activé par '.$_SESSION['domainsAMAccountName'].' le '.date("Y-m-d H:i:s")."\r\n";
            $mailSubject = 'Activation compte Info '.$displayname;
            $mailBody = 'l\'utilisateur <b>'.$_SESSION['fullName'].'</b> &agrave; activer le compte <b>'.$displayname.'</b> le <i>'.date("d-m-Y H:i:s").'</i>';
          }else{
            $dataActive = 1;
            $returndata['activated']=0;
            $modifiedLogTitle = ' ---RH-Desactivate---'."\r\n".'Compte '.$samaccountname.' Desactivé par '.$_SESSION['domainsAMAccountName'].' le '.date("Y-m-d H:i:s")."\r\n";
            $mailSubject = 'Desactivation compte Info '.$displayname;
            $mailBody = '<p>l\'utilisateur <b>'.$_SESSION['fullName'].'</b> &agrave; desactiver le compte <b>'.$displayname.'</b> le <i>'.date("d-m-Y H:i:s").'</i></p>';
          }





          $disable=($useraccountcontrol |  2); // set all bits plus bit 1 (=dec2)
          $enable =($useraccountcontrol & ~2); // set all bits minus bit 1 (=dec2)
          $userdata=array();
          if ($dataActive==0) $new=$enable; else $new=$disable; //enable or disable?
          $userdata["useraccountcontrol"][0]=$new;
          ldap_modify($ldapconn, $ldapParamDn, $userdata); //change state
          $returndata['assignedValue']=$new;
          $returndata['UserAccountControl oldValue']=$useraccountcontrol;
          $returndata['WasActive']=$dataActive;

          //ajout au log
          $logPath = $logFolder.$samaccountname.'.txt';
          file_put_contents($logPath,$modifiedLogTitle,FILE_APPEND);

          //ajoute lien au mail
          $mailBody .= '<p>voir le detail du compte <a href="'.$racineSite.'/detailCompte.php?id='.$samaccountname.'">ici</a></p>';

          //envoi par mail
          $returndata['sendMailTo'] = $alertMailForDisactivation;
          if(sendEmail($mailSubject,$mailBody,$alertMailForDisactivation,$managerMail)){
            $returndata['emailSend'] = true;

          }else{
            $returndata['emailSend'] = false;
          }

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
  $returndata['error']="aucun session ou droits non conformes";
}

ldap_close();
//return our result data in json for to be used by our ajax call
echo json_encode($returndata);
?>
