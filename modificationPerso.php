<?php
include 'php/config.php';
include 'php/functions.php';
include 'php/vars.php';

if(!isset($_SESSION))
  {
    session_start();
  }
/*if ( !isset($_SESSION["ADUserName"]) ){
  header("login.php");
  exit();
}*/

//need to check is has session
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
    <div class="col-xs-6 col-xs-offset-3">

      <h1>user modification</h1>
      <form>
        <?php
        foreach ($loggedinInfo as $row => $param){
          //is displayed
          $formDisableClass = '';
          if($param['isVisableModify']){
            if (!$param['isModifiable']){$formDisableClass='disabled';}
            echo "<div class='form-group'>";
            echo "<label for=\" ".$row." \">".$param['description']."</label>";
            echo "<input type='text' class='form-control modInput' id=\" ".$row." \" aria-describedby=\"".$row."help\" value=\"".$_SESSION[$row]."\" ".$formDisableClass.">";
            echo "<small id=\" ".$row."help\" class='form-control text-muted'>".$param['isModifiableText']."</small>";
            echo "</div>";

          }
        }


        //submit button needs to have a write to log attached. take care of it in the php update file
        ?>
        <button type="submit" class="btn btn-primary">Submit</button>
      </form>
    </div>
  </div>
</div>

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="js/jquery-2.2.4.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="js/bootstrap.min.js"></script>
<script src="js/table2csv.js"></script>
<script src="js/script.js"></script>
<script>
$(document).ready(function() {



});

</script>
</body>
</html>
