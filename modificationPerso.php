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
if ( !isset($_SESSION['domainsAMAccountName']) ){
  header('Location: index.php');
  exit();
}
//register the samaccounname from the session for a return link
$samaccountname = $_SESSION['sAMAccountName'];
//need to check is has session
//also need to grab info from AD and not the session. but ok for the moment while testing
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
<link href="css/toastr.min.css" rel="stylesheet">
<link href="css/ripple.css" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
<link href="css/print.css" rel="stylesheet">
</head>
<body>
<?php include 'navBar.php'; ?>


<div class="container-fluid text-center">


  <h1>Modification du compte <?php echo ($_SESSION['sAMAccountName']); ?></h1>
  <form id="AdUpdateForm">
    <div class="row content">
      <div class="col-md-6">
        <?php
        $i=0;
        //separer en deux colonnes
        $halfInfoCount=ceil(count($loggedinInfo)/2);


        foreach ($loggedinInfo as $row => $param){
          $i+=1;
          if ($i == $halfInfoCount){
            echo('</div><div class="col-md-6">');
          }
          echo('<div class="form-group row">');
          //is displayed
          $formDisableClass = '';
          if($param['isVisableModify']){
            if (!$param['isModifiable']){$formDisableClass='disabled';}
            //echo "<div class='form-group row'>";
            echo ('<label for="'.$row.'" class="col-sm-2 formLabelAlignRight col-form-label">'.$param['description'].'</label>');
            //echo " <div class=\"col-sm-9\">";
            echo ('<div class="col-sm-10"><input type="text" class="form-control modInput" id="'.$param['ldapName'].'" name="'.$row.'" aria-describedby="'.$row.'help" value="'.$_SESSION[$row].'" '.$formDisableClass.'>');
            echo ('<small id="'.$row.'help" class="form-control text-muted modHelp">'.$param['isModifiableText'].'</small></div>');
            //echo "</div>";
            //echo "</div>";

          }
          echo('</div>');

        }


        //submit button needs to have a write to log attached. take care of it in the php update file
        ?>
        <button type="submit" class="btn btn-primary" name="btn-updateAD" id="btn-updateAD">Mettre a jour</button>
        <?php
        echo('<a href="detailCompte.php?id='.$samaccountname.'" class="btn btn-primary" style="margin-left:4px;">Retour fiche utilisateur</a>');
        ?>
      </div>
    </div>
  </form>

</div>

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="js/jquery-2.2.4.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="js/jquery-ui.min.js"></script>
<script src="js/toastr.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/script.js"></script>
<script>
toastr.options = {
  "closeButton": true,
  "debug": false,
  "newestOnTop": false,
  "progressBar": true,
  "positionClass": "toast-bottom-right",
  "preventDuplicates": false,
  "onclick": null,
  "showDuration": "300",
  "hideDuration": "1000",
  "timeOut": "5000",
  "extendedTimeOut": "1000",
  "showEasing": "swing",
  "hideEasing": "linear",
  "showMethod": "fadeIn",
  "hideMethod": "fadeOut"
}
//Update AD ajax request
function submitUpdate(){
  var $data = $("#AdUpdateForm").serialize();
  var $response = null;
  //remove style of updated inputs
  $(".updated").each(function(){
    $(this).removeClass('updated');
  });
  //ajax call
  $.ajax({
    type : 'POST',
    url : 'ajax/updateAD-req.php',
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
        $("#btn-updateAD").html('<i class="fa fa-user" aria-hidden="true"></i> &nbsp; Mettre à jour');
        //window.location.reload();
        //Add class to updated elements
        for($i=0;$i<$response.updatedKeys.length;++$i){
          var $updatedID = "#"+$response.updatedKeys[$i];
          $($updatedID).addClass('updated',200); //annimation effect added by jquery-ui
          $($updatedID).addClass('updatedBorder',200);
          $($updatedID).removeClass('updated',1500);
          toastr["success"]($response.updatedKeys[$i], "Champ mis a jour");
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

$(document).ready(function() {


  //update
  $("#AdUpdateForm").submit(function(e){
    submitUpdate();
    e.preventDefault();
  });


});

</script>
</body>
</html>
