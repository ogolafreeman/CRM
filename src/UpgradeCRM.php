<?php

// Include the function library
require 'Include/Config.php';
$bSuppressSessionTests = TRUE;
require 'Include/Functions.php';

// Set the page title and include HTML header
$sPageTitle = gettext("Upgrade ChurchCRM");

if (!$_SESSION['bAdmin'])
{
  Redirect("index.php");
  exit;
}

require ("Include/HeaderNotLoggedIn.php");
?>
<div class="col-lg-8 col-lg-offset-2">
  <ul class="timeline">
    <li class="time-label">
        <span class="bg-red">
            <?= gettext("Upgrade ChurchCRM") ?>
        </span>
    </li>
    <li>
      <i class="fa fa-database bg-blue"></i>
      <div class="timeline-item" >
        <h3 class="timeline-header"><?= gettext('Step 1: Backup Database') ?></h3>
        <div class="timeline-body" id="backupPhase">
          <input type="button" class="btn btn-primary" id="doBackup" <?= 'value="' . gettext("Generate Database Backup") . '"' ?>>
          <span id="backupStatus"></span>
          <div id="resultFiles">
          </div>
        </div>
      </div>
    </li>
    <li>
      <i class="fa fa-cloud-download bg-blue"></i>
      <div class="timeline-item" >
        <h3 class="timeline-header"><?= gettext('Step 2: Fetch Update Package on Server') ?></h3>
        <div class="timeline-body" id="fetchPhase" style="display: none">
          <input type="button" class="btn btn-primary" id="fetchUpdate" <?= 'value="' . gettext("Fetch Update Files") . '"' ?> >
        </div>
      </div>
    </li>
    <li>
      <i class="fa fa-cogs bg-blue"></i>
      <div class="timeline-item" >
        <h3 class="timeline-header"><?= gettext('Step 3: Apply Update Package on Server') ?></h3>
        <div class="timeline-body" id="updatePhase" style="display: none">
          <ul>
            <li><? gettext("File Name:")?> <span id="updateFileName"> </span></li>
            <li><? gettext("Full Path:")?> <span id="updateFullPath"> </span></li>
            <li><? gettext("SHA1:")?> <span id="updateSHA1"> </span></li>
          </ul>
          <input type="button" class="btn btn-warning" id="applyUpdate" value="<?= gettext("Upgrade System") ?>">
        </div>
      </div>
    </li>
    <li>
      <i class="fa fa-sign-in bg-blue"></i>
      <div class="timeline-item" >
        <h3 class="timeline-header"><?= gettext('Step 4: Login') ?></h3>
        <div class="timeline-body" id="finalPhase" style="display: none">
          <a href="Login.php?Logoff=True" class="btn btn-primary"><?= gettext("Login to Upgraded System") ?> </a>
        </div>
      </div>
    </li>
  </ul>
</div>
<script>
 $("#doBackup").click(function(){
   $.ajax({
      type        : 'POST', // define the type of HTTP verb we want to use (POST for our form)
      url         : window.CRM.root +'/api/database/backup', // the url where we want to POST
      data        : JSON.stringify({
        'iArchiveType'              : 3
      }), // our data object
      dataType    : 'json', // what type of data do we expect back from the server
      encode      : true,
      contentType: "application/json; charset=utf-8"
    })
    .done(function(data) {
      console.log(data);
      var downloadButton = "<button class=\"btn btn-primary\" id=\"downloadbutton\" role=\"button\" onclick=\"javascript:downloadbutton('"+data.filename+"')\"><i class='fa fa-download'></i>  "+data.filename+"</button>";
      $("#backupstatus").css("color","green");
      $("#backupstatus").html("<?= gettext("Backup Complete, Ready for Download.") ?>");
      $("#resultFiles").html(downloadButton);
      $("#downloadbutton").click(function(){
        $("#fetchPhase").show("slow");
        $("#backupPhase").slideUp();
      });
    }).fail(function()  {
      $("#backupstatus").css("color","red");
      $("#backupstatus").html("<?= gettext("Backup Error.") ?>");
    });
   
 });
 
 $("#fetchUpdate").click(function(){
    $.ajax({
      type : 'GET',
      url  : window.CRM.root +'/api/systemupgrade/downloadlatestrelease', // the url where we want to POST
      dataType    : 'json' // what type of data do we expect back from the server
    }).done(function(data){
      console.log(data);
      window.CRM.updateFile=data;
      $("#updateFileName").text(data.fileName);
      $("#updateFullPath").text(data.fullPath);
      $("#updateSHA1").text(data.sha1);
      $("#fetchPhase").slideUp();
      $("#updatePhase").show("slow");
    });
   
 });
 
 $("#applyUpdate").click(function(){
   $.ajax({
      type : 'POST',
      url  : window.CRM.root +'/api/systemupgrade/doupgrade', // the url where we want to POST
      data        : JSON.stringify({
        fullPath: window.CRM.updateFile.fullPath,
        sha1: window.CRM.updateFile.sha1
      }), // our data object
      dataType    : 'json', // what type of data do we expect back from the server
      encode      : true,
      contentType: "application/json; charset=utf-8"
    }).done(function(data){
      console.log(data);
      $("#updatePhase").slideUp();
      $("#finalPhase").show("slow");
    });
 });
 
function downloadbutton(filename) {
    window.location = window.CRM.root +"/api/database/download/"+filename;
    $("#backupstatus").css("color","green");
    $("#backupstatus").html("<?= gettext("Backup Downloaded, Copy on server removed") ?>");
    $("#downloadbutton").attr("disabled","true");
}
</script>

<?php
// Add the page footer
require ("Include/FooterNotLoggedIn.php");

// Turn OFF output buffering
ob_end_flush();
?>
