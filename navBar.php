<?php
if ( !isset($_SESSION["sAMAccountName"]) ){
  $ADSession=FALSE;
}else{
  $ADSession=TRUE;
}
include_once 'php/config.php';
include_once 'php/functions.php';
include_once 'php/vars.php';
?>
<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="index.php"><img src="img/logoAS-small.png" alt="AiderSante" width="25px" height="25px"></a>
    </div>
    <div class="collapse navbar-collapse" id="myNavbar">
      <ul class="nav navbar-nav">
        <li id="nav1"><a href="index.php">Comptes</a></li>
        <li id="nav2"><a href="groupes.php">Groupes</a></li>
        <?php
        if(CheckIfAdmin()){
          echo '<li id="nav3"><a href="desactiver.php">Desactiver</a></li>';
        }
        ?>
        <li id="nav4"><a href="tableauComplet.php">Tableau detail</a></li>
      </ul>

      <ul class='nav navbar-nav navbar-right'>

        <li id="loginDropdown" class="dropdown <?php if($ADSession){echo 'hidden';}?>">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <span class="glyphicon glyphicon-log-in"></span> Login
          </a>
          <ul id="login-dp" class="dropdown-menu login-dp">
            <li>
               <div class="row">
                  <div class="col-md-12">
                     <form class="form" role="form" method="post" accept-charset="UTF-8" id="login-nav">
                        <div class="form-group">
                           <label class="sr-only" for="sAMAccountName">Login AD</label>
                           <input type="text" class="form-control" id="sAMAccountName" placeholder="Login AD" required name="sAMAccountName">
                        </div>
                        <div class="form-group">
                           <label class="sr-only" for="AD_Password">Mot de passe</label>
                           <input type="password" class="form-control" id="AD_Password" placeholder="Mot de passe" required name="password">
                        </div>
                        <div id="loginErrorMessage"></div>
                        <div class="form-group">
                           <button type="submit" class="btn btn-primary btn-block" name="btn-login" id="btn-login"><span class="glyphicon glyphicon-log-in"></span> &nbsp;Connexion AD</button>
                        </div>
                     </form>
                  </div>
               </div>
            </li>
          </ul>
        </li>

        <li id="logoutDropdown" class="dropdown <?php if(!$ADSession){echo 'hidden';}?>">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" id="logedInUserName">
            <span class="glyphicon glyphicon-user"></span>

            <?php
            if ($ADSession) {
              echo ($_SESSION['fullName']);
            }else {
              echo '<span id="nonUpdatedUser">User</span>';
            } ?>
          </a>
          <ul id="logedin-dp" class="dropdown-menu">
            <li>
               <div class="row">
                  <div class="col-md-12">
                    <?php
                    foreach ($loggedinInfo as $row => $param){
                      //is displayed
                      if($param['isVisablePannel']){
                        echo "<p class=\"logedinPannelElement\">";
                          echo "<b>".$param['description']."&nbsp;: </b>";
                          echo "<span class=\"logedinPannelSpan\" id=\"".$row."\">";
                            if ($ADSession) {echo ($_SESSION[$row]);}
                          echo "</span>";
                        echo "</p>";
                      }
                    }
                    ?>
                    <p><a href="modificationPerso.php" class="btn btn-primary btn-block">Modifier votre compte</a></p>
                    <p><a href="#" id="logout" class="btn btn-primary btn-block">Deconnexion</a></p>
                  </div>
               </div>
            </li>
          </ul>
        </li>

      </ul>

      <!--<ul class="nav navbar-nav navbar-right">
        <li><a href="#"><span class="glyphicon glyphicon-log-in"></span> Login</a></li>
      </ul>-->
    </div>
  </div>
</nav>
