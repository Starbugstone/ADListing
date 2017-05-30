<?php
//error_reporting(E_ERROR);
include '../php/config.php';
include '../php/functions.php';
//include '../php/vars.php';

//add a return data array to grab results
$returndata = array(
  'state' =>'',
  'error' =>'',
  'responseName' => ''
);
if(!isset($_SESSION))
  {
    session_start();
  }
  if (isset($_SESSION['domainsAMAccountName'])  && $_SESSION['ldapRHAdminGroup'] == TRUE){
    //check for session, no need to go further if no session and could be security risk
    //also checking if session has the rights to update profile / is member of the AD-RH group
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

        $samaccountname = $_POST["samaccountnamePhoto"];

        //1st grab the actual info so we can check later
        //$sessionSAM = $_SESSION['sAMAccountName'];
        $filter = "(&(objectCategory=person)(sAMAccountName=$samaccountname))";
        $result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
        $data = ldap_get_entries($ldapconn, $result);


        //set the canvas size
        $canvasWidth = 104;
        $canvasHeight = 104;


        //recupere l'image et modifie la taille
        $fn = $_FILES["userImage"]["tmp_name"];
        $imageFileType = $_FILES["userImage"]["type"];
        //$tmpImageData = file_get_contents($fn);

        if ($imageFileType == 'image/png') {
          $image = imagecreatefrompng($fn);
        } else if ($imageFileType == 'image/gif') {
          $image = imagecreatefromgif($fn);
        } else if ($imageFileType == 'image/jpeg'){
          $image = imagecreatefromjpeg($fn);
        } else{
          $returndata['state']=false;
          $returndata['error']="erreur format d'image";
        }

        if(isset($image)){
          list($width, $height) = getimagesize($fn);
          $ratioX = $canvasWidth / $width;
          $ratioY = $canvasHeight / $height;
          $ratio = $ratioY;
          if($ratioX < $ratioY){
            $ratio = $ratioX;
          }
          $newWidth = $width * $ratio;
          $newheight = $height * $ratio;
          //chech if image is ok, else we just return an error
          $resized_image = imagecreatetruecolor($newWidth, $newheight);
          imagecopyresampled($resized_image, $image, 0, 0, 0, 0, $newWidth, $newheight, $width, $height);

          imagejpeg($resized_image, '../tmpImg/'.$samaccountname.'.jpg', 100);
          $rawImage = file_get_contents('../tmpImg/'.$samaccountname.'.jpg');
          $base64Img = base64_encode($rawImage);
          $ldapParamDn = $data[0]['dn'];

          $userdata=array();
          $userdata['thumbnailphoto'][0] = $rawImage;

          ldap_modify($ldapconn,$ldapParamDn,$userdata);

          //add log
          $logPath = $logFolder.$samaccountname.'.txt';
          $modifiedLogTitle = ' ---RH-Update---'."\r\n".'Compte '.$samaccountname.' modifier par '.$_SESSION['domainsAMAccountName'].' le '.date("Y-m-d H:i:s")."\r\n";
          file_put_contents($logPath,$modifiedLogTitle,FILE_APPEND);
          $value = "photo mis à jour";
          file_put_contents($logPath,$value,FILE_APPEND);


          //send mail
          if (isset($data[0]['mail'][0]) && $data[0]['mail'][0] != ""){
            $mailUser = $data[0]['mail'][0];
            $mailSubject = 'Modification de votre Photo AD '.$samaccountname.' par '.$_SESSION['domainsAMAccountName'].' le '.date("Y-m-d H:i:s");
            $mailBody = '<p>La photo de votre profil AD viens d\'&ecirc;tre modifi&eacute; par '.$_SESSION['domainsAMAccountName'].'</p>';

            //add account link to mail
            $mailBody .= '<p>voir le detail du compte <a href="'.$racineSite.'/detailCompte.php?id='.$samaccountname.'">ici</a></p>';
            if(sendEmail($mailSubject,$mailBody,$mailUser)){
              $returndata['emailSend'] = true;
              $returndata['emailSendMail'] = $data[0]['mail'][0];
            }else{
              $returndata['emailSend'] = false;
            }
          }else{
            $returndata['emailSend'] = false;
          }

          $returndata['base64img'] = $base64Img;
          $returndata['files'] = $_FILES;
          $returndata['sam'] = $samaccountname;
        } //end if isset image.
      }else {
        //LDAP Bind failed
        $returndata['state']=false;
        $returndata['error']="Erreur Bind LDAP";
      }
    }else {
      //ldap connect failed
      $returndata['state']=false;
      $returndata['error']="Erreur Connect LDAP";
    }

  }else{
    #error no session
    $returndata['state']=false;
    $returndata['error']="aucun session";
  }
  echo json_encode($returndata);
 ?>
