<?php
header("X-UA-Compatible: IE=Edge");
if(!isset($_SESSION))
{
  session_start();
}
include 'php/config.php';
include 'php/functions.php';
include 'php/vars.php';

if(!isset($_SESSION))
  {
    session_start();
  }
if ( !CheckIfRH() ){
  header('Location: index.php');
  exit();
}

// connect
$ldapconn = ldap_connect($ldapserver) or die("Could not connect to LDAP server.");

if($ldapconn) {
  // Adding options
  ldap_set_option ($ldapconn, LDAP_OPT_REFERRALS, 0);
  ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
  // binding to ldap server
  $ldapbind = ldap_bind($ldapconn, $ldapuser, $ldappass) or die ("Error trying to bind: ".ldap_error($ldapconn));
  // verify binding
  if ($ldapbind) {

    //recupere variable passé dans URL
    //on fait un choix de filtre en fonction de ceux qu'on passe comme paramettre. Le resultat est toujours un seul utilisateurs car ces données doivent etre uniques dans AD
    if (isset($_GET['dn'])){
      $dn=$_GET['dn'];
      $dn=escapeLdapFilter($dn);
      $filter = "(&(objectCategory=person)(distinguishedname=$dn))";
    }elseif (isset($_GET['id'])){
      $id=$_GET['id'];
      //filter on ID
      $filter = "(&(objectCategory=person)(sAMAccountName=$id))";
    }elseif (isset($_GET['dispName'])){
      $dispName = $_GET['dispName'];
      $filter = "(&(objectCategory=person)(displayname=$dispName))";
    }
    else{
      echo("<h1>erreur de filtre</h1>");
    }

    //Getting results
    $result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
    $data = ldap_get_entries($ldapconn, $result);

    if (!isset($data[0])){
      //bug in IE with utf-8 encoding
      $filter = utf8_encode($filter);
      $result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
      $data = ldap_get_entries($ldapconn, $result);
      //echo $filter;
    }

    //Now that we are connected, do a quick check to see if the account isn't in the refused OU paramater. 1st security of avoiding modifying sensative accounts
    if ( blacklistedDistinguishedname($data[0]["distinguishedname"][0],$refusedOU) ){
      header('Location: index.php');
      exit();
    }

    //grab all our required info
    //1st pannel
    if (isset($data[0]['thumbnailphoto'][0])){
      $thumbnailRaw = $data[0]['thumbnailphoto'][0];
      $thumbnailImg = '<img class="thumb" src="data:image/jpeg;base64,'. base64_encode($thumbnailRaw).'" /><br>';
    }else{
      $thumbnailRaw='';
      $thumbnailImg='<img class="thumb" src="img/user-icon.png" /><br>';
    }
    $displayName = getOr($data[0]['displayname'][0], "");
    $samaccountname = getOr($data[0]['samaccountname'][0], "");
    $nom = getOr($data[0]['sn'][0], "");
    $prenom = getOr($data[0]['givenname'][0], "");
    $nomPrenom = getOr($data[0]['sn'][0],"")." ".getOr($data[0]['givenname'][0],"");
    //$mail = getOr($data[0]['mail'][0],"Aucun mail");
    if (isset($data[0]['mail'][0])){
      $mail = $data[0]['mail'][0];
      $mail_link = "<a href=\"mailto:".$data[0]['mail'][0]."\"><i class='fa fa-envelope-o secIcon' aria-hidden='true' title='Envoyer Mail'></i></a>";
    }else{
      $mail = "";
      $mail_link = "";
    }
    $Matricule = getOr($data[0]["employeeid"][0],"");
    $description = getOr($data[0]["description"][0],"");
    if ($customRPPSField){$RPPS = getOr($data[0]["rpps"][0],"");}



    //3rd pannel

    if (isset($data[0]['manager'][0])){
      $manager = explodeCN($data[0]['manager'][0]);
      $managerDn = removeAccents($data[0]['manager'][0]);
    }
    else{
      $manager = "";
      $managerDn = "";
    }


    $title = getOr($data[0]['title'][0],"");
    $department = getOr($data[0]['department'][0],"");
    $company = getOr($data[0]['company'][0],"");
    $telephone = getOr($data[0]['telephonenumber'][0],"");
    $mobile = getOr($data[0]['mobile'][0],"");
    $fax = getOr($data[0]['facsimiletelephonenumber'][0],"");
    $office = getOr($data[0]['physicaldeliveryofficename'][0],"");
    $ville = getOr($data[0]['l'][0],"");
    $useraccountcontrol=$data[0]["useraccountcontrol"][0];
    $memberOf = $data[0]['memberof'];
    if (accountIsNotActive($useraccountcontrol)){
      $accountState = "<p id='accountStateIcon'><span class='glyphicon glyphicon-warning-sign secIcon'></span>Compte Desactive</p>";
      $desactivateButtonText = "Activer le compte";
      $desactivateButtonClass = "ActivateAccount";
      $dataActive = 0;
    }else{
      $accountState = "<p  id='accountStateIcon' class='hidden'><span class='glyphicon glyphicon-warning-sign secIcon'></span>Compte Desactive</p>";
      $desactivateButtonText = "Desactiver le compte";
      $desactivateButtonClass = "DesactivateAccount";
      $dataActive = 1;
    }

  } else {
    echo "LDAP bind failed...";
    //$data=null;
  }
}
 ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="dataTables/datatables.min.css">
<link rel="stylesheet" href="font-awesome-4.6.3/css/font-awesome.min.css">
<title>Comptes AD</title>
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->

<?php include 'favicon.php'; ?>
<link href="css/ripple.css" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
<link href="css/print.css" rel="stylesheet">
</head>
<body>
<?php include 'navBar.php'; ?>


<div class="container-fluid text-center">
  <form id="AdUpdateForm">
  <div class="row content equal">

    <div class="col-md-6">
      <div class="panel panel-default">
        <div class="panel-heading">
        <h3 class="panel-title"><?php echo($nomPrenom); ?></h3>
        </div>
        <div class="panel-body panelIcons">
          <?php
          echo('<div id="accountState">');
          echo $accountState;
          echo('</div>');
          echo $thumbnailImg;
          ?>
          <p>Changer Photo(non implementer)</p>
          <div class="form-group row RHEditRow"><label for="samaccountname-text" class="col-sm-2 col-form-label editRHLabel">Login</label> <div class="col-sm-10"><?php echo('<p class="form-control-static mb-0 noBorder" id="samaccountname-text" >'.$samaccountname.'</p>');?></div></div>
          <div class="form-group row RHEditRow hidden"><label for="samaccountname" class="col-sm-2 col-form-label editRHLabel">Nom</label> <div class="col-sm-10"><?php echo('<input type="text" class="form-control" id="samaccountname-hidden" name="samaccountname"  value="'.$samaccountname.'">');?></div></div>
          <div class="form-group row RHEditRow"><label for="nom" class="col-sm-2 col-form-label editRHLabel">Nom</label> <div class="col-sm-10"><?php echo('<input type="text" class="form-control" id="sn" name="sn"  value="'.$nom.'">');?></div></div>
          <div class="form-group row RHEditRow"><label for="prenom" class="col-sm-2 col-form-label editRHLabel">Prenom</label> <div class="col-sm-10"><?php echo('<input type="text" class="form-control" id="givenname" name="givenname" value="'.$prenom.'">');?></div></div>
          <div class="form-group row RHEditRow"><label for="nomPrenom" class="col-sm-2 col-form-label editRHLabel">Nom&nbsp;afficher</label> <div class="col-sm-10"><?php echo('<input readonly type="text" class="form-control" id="displayname" name="displayname" value="'.$displayName.'">');?></div></div>
          <div class="form-group row RHEditRow"><label for="mail" class="col-sm-2 col-form-label editRHLabel">Mail</label> <div class="col-sm-10"><?php echo('<p class="form-control-static mb-0 noBorder" id="mail" >'.$mail.'</p>');?></div></div>
          <div class="form-group row RHEditRow"><label for="matricule" class="col-sm-2 col-form-labe editRHLabell">Matricule</label> <div class="col-sm-10"><?php echo('<input type="text" class="form-control" id="employeeid" name="employeeid" value="'.$Matricule.'">');?></div></div>
          <?php
          if ($customRPPSField){
            echo('<div class="form-group row RHEditRow" id="rppsRow"><label for="rpps" class="col-sm-2 col-form-label editRHLabel">RPPS</label> <div class="col-sm-10"><input type="text" class="form-control" id="rpps" name="rpps" value="'.$RPPS.'"></div></div>');
          }
          ?>
          <div class="form-group row RHEditRow"><label for="Description" class="col-sm-2 col-form-label editRHLabel">Description</label> <div class="col-sm-10"><?php echo('<input type="text" class="form-control" id="description" name="description" value="'.$description.'">');?></div></div>

        </div>
      </div>
    </div>



    <div class="col-md-6">
      <div class="panel panel-default">
        <div class="panel-heading">
        <h3 class="panel-title">Organisation</h3>
        </div>
        <div class="panel-body">
          <div class="form-group row RHEditRow"><label for="Fonction" class="col-sm-2 col-form-label editRHLabel">Fonction</label> <div class="col-sm-10"><?php echo('<p class="form-control-static mb-0 noBorder" id="Fonction" >'.$title.'</p>');?></div></div>
          <div class="form-group row RHEditRow"><label for="Service" class="col-sm-2 col-form-label editRHLabel">Service</label> <div class="col-sm-10"><?php echo('<p class="form-control-static mb-0 noBorder" id="Service" >'.$department.'</p>');?></div></div>
          <div class="form-group row RHEditRow"><label for="Bureau" class="col-sm-2 col-form-label editRHLabel">Bureau</label> <div class="col-sm-10"><?php echo('<p class="form-control-static mb-0 noBorder" id="Bureau" >'.$office.'</p>');?></div></div>
          <div class="form-group row RHEditRow"><label for="Ville" class="col-sm-2 col-form-label editRHLabel">Ville</label> <div class="col-sm-10"><?php echo('<p class="form-control-static mb-0 noBorder" id="Ville" >'.$ville.'</p>');?></div></div>
          <div class="form-group row RHEditRow"><label for="Telephone" class="col-sm-2 col-form-label editRHLabel">Telephone</label> <div class="col-sm-10"><?php echo('<input type="text" class="form-control" id="telephonenumber" name="telephonenumber" value="'.$telephone.'">');?></div></div>
          <div class="form-group row RHEditRow"><label for="Mobile" class="col-sm-2 col-form-label editRHLabel">Mobile</label> <div class="col-sm-10"><?php echo('<input type="text" class="form-control" id="mobile" name="mobile" value="'.$mobile.'">');?></div></div>
          <div class="form-group row RHEditRow"><label for="Fax" class="col-sm-2 col-form-label editRHLabel">Fax</label> <div class="col-sm-10"><?php echo('<input type="text" class="form-control" id="facsimiletelephonenumber" name="facsimiletelephonenumber" value="'.$fax.'">');?></div></div>
          <div class="form-group row RHEditRow"><label for="Societe" class="col-sm-2 col-form-label editRHLabel">Societe</label> <div class="col-sm-10"><?php echo('<input type="text" class="form-control" id="company" name="company" value="'.$company.'">');?></div></div>
          <div class="form-group row RHEditRow"><label for="Gestionnaire" class="col-sm-2 col-form-label editRHLabel">Gestionnaire</label> <div class="col-sm-10"><?php echo('<p class="form-control-static mb-0 noBorder" id="Gestionnaire" >'.$manager.'</p>');?></div></div>

        </div>
      </div>
    </div>


  </div>
  <p class="hidden" id="rppsHelp">Un RPPS doit contenir 11 characteres</p>
  <button type="submit" class="btn btn-primary" name="btn-updateAD" id="btn-updateAD">Mettre a jour</button>
  <?php

  //check if the account can be disactivated
  if (!in_array($nonDisactivatableAccountGroup,$memberOf)){
    echo('<a href="#" type="submit" class="btn btn-primary '.$desactivateButtonClass.'" name="btn-desactivate" id="btn-desactivate" data-isactive="'.$dataActive.'" data-useraccountcontrol="'.$useraccountcontrol.'" data-samaccountname="'.$samaccountname.'">'.$desactivateButtonText.'</a>');
  }
  ?>
  </form>
</div>

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="js/jquery-2.2.4.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="js/bootstrap.min.js"></script>
<script src="js/script.js"></script>
<script>
//Update AD ajax request
function submitUpdate(){
  var $data = $("#AdUpdateForm").serialize();
  //console.log($data);
  var $response = null;

  //remove all updated class
  $(".updated").each(function(){
    $(this).removeClass('updated');
  });
  //ajax call
  $.ajax({
    type : 'POST',
    url : 'ajax/updateADRH-req.php',
    data : $data,
    //change icon to spinning
    beforeSend : function(){
      $("#btn-updateAD").html('<i class="fa fa-spinner fa-pulse" aria-hidden="true"></i> &nbsp; Mise a jour ...');
    },
    success : function ($responseJSON){
      $response = jQuery.parseJSON($responseJSON);
      console.log($response);
      //check state, modify logon pannel then hide and show loggedin pannel
      if ($response.state){
        $("#btn-updateAD").html('<i class="fa fa-user" aria-hidden="true"></i> &nbsp; Ok ...');
        //window.location.reload();
        //Add class to updated elements
        for($i=0;$i<$response.updatedKeys.length;++$i){
          var $updatedID = "#"+$response.updatedKeys[$i];
          $($updatedID).addClass('updated');
        }



      }else{
        //set error
        $("#btn-updateAD").html('<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> &nbsp; erreur ...');
        console.log($response.error);
      }
    }
  });
  return false;
}

function changeAccountState($isActive,$samaccountname){
  var $data={isActive:$isActive,samaccountname:$samaccountname};//need to serialize our data to send here
  $.ajax({
    type: 'POST',
    url : 'ajax/desactivateADRH-req.php',
    data : $data,
    beforeSend : function(){
      $("#btn-desactivate").html('<i class="fa fa-spinner fa-pulse" aria-hidden="true"></i> &nbsp; Mise a jour ...');
    },
    success : function ($responseJSON){
      $response = jQuery.parseJSON($responseJSON);
      console.log($response);
      //check state, modify logon pannel then hide and show loggedin pannel
      if ($response.state){

        //window.location.reload();
        //Add class to updated elements
        if($response.activated==1){
          $("#accountStateIcon").addClass("hidden");
          $("#accountStateIcon").removeClass("show");
          $("#btn-desactivate").addClass("DesactivateAccount");
          $("#btn-desactivate").removeClass("ActivateAccount");
          $("#btn-desactivate").html('<i class="fa fa-user" aria-hidden="true"></i> &nbsp; Activé');
          console.log("activated");
        }else{
          $("#accountStateIcon").removeClass("hidden");
          $("#accountStateIcon").addClass("show");
          $("#btn-desactivate").removeClass("DesactivateAccount");
          $("#btn-desactivate").addClass("ActivateAccount");
          $("#btn-desactivate").html('<i class="fa fa-user" aria-hidden="true"></i> &nbsp; Desactivé');
        }

      }else{
        //set error
        $("#btn-desactivate").html('<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> &nbsp; erreur ...');
        console.log($response.error);
      }
    }
  });

}

$("#btn-desactivate").click(function(){
  var $isActive = $(this).data("isactive");
  var $samaccountname = $(this).data("samaccountname");
  changeAccountState($isActive,$samaccountname);
});

// Dynamic update of Name Surname.
function updateNomPrenom(){
  var $nom = $("#nom").val();
  var $prenom = $("#prenom").val();
  $("#nomPrenom").val($nom+' '+$prenom);
}

$(document).ready(function() {
  //update
  $("#AdUpdateForm").submit(function(e){
    submitUpdate();
    e.preventDefault();
  });

  //modifier nomPrenom
  var $nom = $("#nom").val();
  var $prenom = $("prenom").val();
  $("#nom").keyup(function(){
    updateNomPrenom();
  });

  $("#prenom").keyup(function(){
    updateNomPrenom();
  });

  $("#rpps").keyup(function(){
    if($("#rpps").val().length != 11 && $("#rpps").val().length != 0 ){
      //console.log("not valid");
      $("#rpps").addClass('form-control-warning');
      $("#rppsRow").addClass('has-warning');
      $("#rppsHelp").removeClass('hidden');
      $("#btn-updateAD").addClass('disabled');
      $("#btn-updateAD").attr('disabled', 'disabled');
    }else{
      //console.log("valid");
      $("#rpps").removeClass('form-control-warning');
      $("#rppsRow").removeClass('has-warning');
      $("#rppsHelp").addClass('hidden');
      $("#btn-updateAD").removeClass('disabled');
      $("#btn-updateAD").removeAttr("disabled");
    }
  });

});

</script>
</body>
</html>
