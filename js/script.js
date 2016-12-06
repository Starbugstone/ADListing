//Function to construct the DataTable and add the copy button
function constructUserTable($tableId){
  $($tableId).DataTable({
    "language": {
        "url": "dataTables/dataTables.french.lang"
    },
    paging: false
  });

  //construct export to excel button after ajax call and wait for the dataTable calls init so we can use the search ID.
  //called in doc ready will fail because the ID isn't present
  var tableExport = $($tableId).DataTable();
  tableExport.on( 'init', function(){
    var $tableIdButton = $tableId + "_filter>label";
    //construct export button after the search bar
    $($tableIdButton).after("<a href='#' id='csvExportButton' class='btn btn-default exportButton' title='Exporter vers CSV'><span class='glyphicon glyphicon-save-file'></span></a>");
    //add the on click to execute export
    $("#csvExportButton").on('click', function (event) {
        exportTableToCSV.apply(this, [$($tableId), 'ExportAD.csv']);
        // IF CSV, don't do event.preventDefault() or return false
        // We actually need this to be a typical hyperlink
    });
  });
}

function getPageName(url) {
    var index = url.lastIndexOf("/") + 1;
    var filenameWithExtension = url.substr(index);
    var filename = filenameWithExtension.split(".")[0]; // <-- added this line
    return filename;                                    // <-- added this line
}

//login ajax request
function submitLogin(){
  var $data = $("#login-nav").serialize();
  var $response = null;
  //ajax call
  $.ajax({
    type : 'POST',
    url : 'ajax/login-req.php',
    data : $data,
    //change icon to spinning
    beforeSend : function(){
      $("#btn-login").html('<i class="fa fa-spinner fa-pulse" aria-hidden="true"></i> &nbsp; Signing In ...');
    },
    success : function ($responseJSON){
      $response = jQuery.parseJSON($responseJSON);
      //check state, modify logon pannel then hide and show loggedin pannel
      if ($response.state){
        $("#btn-login").html('<i class="fa fa-user" aria-hidden="true"></i> &nbsp; Ok ...');
        $("#loginErrorMessage").html("");
        $("#loginDropdown").addClass("hidden");
        $("#logoutDropdown").removeClass("hidden");
        $("#nonUpdatedUser").html($response.responseName);
        $("#logedInUserName").html($response.responseName);
        //go through all spans and update all elements
        $(".logedinPannelElement").each(function(){
          var element = $(this);
          var elementSpan = $(".logedinPannelSpan:first",element);
          var elementSpanID = $(elementSpan).attr('id');
          elementSpanID = elementSpanID.replace('logedinPannelID-','');//remove the addid ID parameter. This was added because we had conflicting ID's
          $(elementSpan).html($response[elementSpanID]);
        });
      }else{
        //set error
        $("#btn-login").html('<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> &nbsp; erreur ...');
        $("#loginErrorMessage").html('<p>'+$response.error+'</p>');
      }
    }
  });
  return false;
}


//common JS to all pages
$(document).ready(function() {
  //log in
  $("#login-nav").submit(function(e){
    submitLogin();
    e.preventDefault();
  });

  //logout
  $("#logout").click(function(){
    //destroy session
    $.post("ajax/logout-req.php");
    //destroy response var
    var $response = null;
    //reset and show login form
    $("#btn-login").html('<span class="glyphicon glyphicon-log-in"></span> &nbsp;Connexion AD');
    $("#sAMAccountName").val("");
    $("#AD_Password").val("");
    $("#loginDropdown").removeClass("hidden");
    $("#logoutDropdown").addClass("hidden");
    //delete all info in loggedOn form
    $("#nonUpdatedUser").html('');
    //go through all spans and update all elements
    $(".logedinPannelElement").each(function(){
      var element = $(this);
      var elementSpan = $(".logedinPannelSpan:first",element);
      $(elementSpan).html('');
    });
    //redirect to index if on mod page
    var $urlName = window.location.pathname;
    $urlName = getPageName($urlName);
    if ($urlName == "modificationPerso"){
      window.location.replace("index.php");
    }
  });

});
