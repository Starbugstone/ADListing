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


<link href="css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navBar.php'; ?>


<div class="container-fluid text-center">
  <div class="row content">
    <div class="col-xs-8 col-xs-offset-2">

      <h1>Modification du compte <?php echo ($_SESSION['sAMAccountName']); ?></h1>
      <form id="AdUpdateForm">
        <?php
        foreach ($loggedinInfo as $row => $param){
          //is displayed
          $formDisableClass = '';
          if($param['isVisableModify']){
            if (!$param['isModifiable']){$formDisableClass='disabled';}
            echo "<div class='form-group row'>";
            echo "<label for=\"".$row."\" class=\"col-sm-3 formLabelAlignRight\">".$param['description']."</label>";
            echo " <div class=\"col-sm-9\">";
            echo "<input type='text' class='form-control modInput' id=\"".$row."\" name=\"".$row."\" aria-describedby=\"".$row."help\" value=\"".$_SESSION[$row]."\" ".$formDisableClass.">";
            echo "<small id=\"".$row."help\" class='form-control text-muted'>".$param['isModifiableText']."</small>";
            echo "</div>";
            echo "</div>";

          }
        }


        //submit button needs to have a write to log attached. take care of it in the php update file
        ?>
        <button type="submit" class="btn btn-primary" name="btn-updateAD" id="btn-updateAD">Mettre a jour</button>
      </form>
    </div>
  </div>
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
  var $response = null;
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
      //check state, modify logon pannel then hide and show loggedin pannel
      if ($response.state){
        $("#btn-updateAD").html('<i class="fa fa-user" aria-hidden="true"></i> &nbsp; Ok ...');
        window.location.reload();



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
