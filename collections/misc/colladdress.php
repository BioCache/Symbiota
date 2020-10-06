<?php
include_once('../../config/symbini.php');
include_once($SERVER_ROOT.'/classes/InstitutionManager.php');
header("Content-Type: text/html; charset=".$CHARSET);

if(!$SYMB_UID) header('Location: ../../profile/index.php?refurl=../collections/misc/colladdress.php?'.htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES));

$action = array_key_exists('action',$_REQUEST)?$_REQUEST['action']:'';
$collid = array_key_exists('collid',$_REQUEST)?$_REQUEST['collid']:0;

if(preg_match('/[^a-zA-Z\s]+/', $action)) $action = '';
if(!is_numeric($collid)) $collid = 0;

$isEditor = 0;
if($IS_ADMIN) $isEditor = 1;
elseif($collid){
	if(array_key_exists("CollAdmin",$USER_RIGHTS) && in_array($collid,$USER_RIGHTS["CollAdmin"])){
		$isEditor = 1;
	}
}

$addressManager = new InstitutionManager();
$addressManager->setCollid($collid);

$statusStr = '';
if($isEditor){
	if($action == 'Link Address'){
		if(!$addressManager->linkAddress($_POST['iid'])){
			$statusStr = $addressManager->getErrorMessage();
		}
	}
	elseif(array_key_exists('removeiid',$_GET)){
		if(!$addressManager->removeAddress($_GET['removeiid'])){
			$statusStr = $addressManager->getErrorMessage();
		}
	}
}
$collData = current($collManager->getCollectionMetadata());
$collManager->cleanOutArr($collData);
?>
<html>
<head>
	<title><?php echo $DEFAULT_TITLE." ".($collid?$collData["collectionname"]:"") ; ?> Mailing Address</title>
	<?php
	$activateJQuery = true;
	if(file_exists($SERVER_ROOT.'/includes/head.php')){
		include_once($SERVER_ROOT.'/includes/head.php');
	}
	else{
		echo '<link href="'.$CLIENT_ROOT.'/css/jquery-ui.css" type="text/css" rel="stylesheet" />';
		echo '<link href="'.$CLIENT_ROOT.'/css/base.css?ver=1" type="text/css" rel="stylesheet" />';
		echo '<link href="'.$CLIENT_ROOT.'/css/main.css?ver=1" type="text/css" rel="stylesheet" />';
	}
	?>
	<script src="../../js/jquery.js" type="text/javascript"></script>
	<script src="../../js/jquery-ui.js" type="text/javascript"></script>
	<script>

		$(function() {
			var dialogArr = new Array("instcode","collcode","pedits","pubagg","rights","rightsholder","accessrights","guid","colltype","management","icon","collectionid","sourceurl","sort");
			var dialogStr = "";
			for(i=0;i<dialogArr.length;i++){
				dialogStr = dialogArr[i]+"info";
				$( "#"+dialogStr+"dialog" ).dialog({
					autoOpen: false,
					modal: true,
					position: { my: "right", at: "center", of: "#"+dialogStr }
				});

				$( "#"+dialogStr ).click(function() {
					$( "#"+this.id+"dialog" ).dialog( "open" );
				});
			}

		});

		function openMappingAid() {
			mapWindow=open("../tools/mappointaid.php?errmode=0","mappointaid","resizable=0,width=800,height=700,left=20,top=20");
			if (mapWindow.opener == null) mapWindow.opener = self;
		}

		function verifyCollEditForm(f){
			if(f.institutioncode.value == ''){
				alert("Institution Code must have a value");
				return false;
			}
			else if(f.collectionname.value == ''){
				alert("Collection Name must have a value");
				return false;
			}
			else if(f.managementtype.value == "Snapshot" && f.guidtarget.value == "symbiotaUUID"){
				alert("The Symbiota Generated GUID option cannot be selected for a collection that is managed locally outside of the data portal (e.g. Snapshot management type). In this case, the GUID must be generated within the source collection database and delivered to the data portal as part of the upload process.");
				return false;
			}
			else if(!isNumeric(f.latitudedecimal.value) || !isNumeric(f.longitudedecimal.value)){
				alert("Latitdue and longitude values must be in the decimal format (numeric only)");
				return false;
			}
			else if(f.rights.value == ""){
				alert("Rights field (e.g. Creative Commons license) must have a selection");
				return false;
			}
			try{
				if(!isNumeric(f.sortseq.value)){
					alert("Sort sequence must be numeric only");
					return false;
				}
			}
			catch(ex){}
			return true;
		}

		function mtypeguidChanged(f){
			if(f.managementtype.value == "Snapshot" && f.guidtarget.value == "symbiotaUUID"){
				alert("The Symbiota Generated GUID option cannot be selected for a collection that is managed locally outside of the data portal (e.g. Snapshot management type). In this case, the GUID must be generated within the source collection database and delivered to the data portal as part of the upload process.");
			}
			else if(f.managementtype.value == "Aggregate" && f.guidtarget.value != "" && f.guidtarget.value != "occurrenceId"){
				alert("An Aggregate dataset (e.g. specimens coming from multiple collections) can only have occurrenceID selected for the GUID source");
				f.guidtarget.value = 'occurrenceId';
			}
			if(!f.guidtarget.value){
				f.publishToGbif.checked = false;
			}
		}

		function checkGUIDSource(f){
			if(f.publishToGbif.checked == true){
				if(!f.guidtarget.value){
					alert("You must select a GUID source in order to publish to data aggregators.");
					f.publishToGbif.checked = false;
				}
			}
		}

		function verifyAddAddressForm(f){
			if(f.iid.value == ""){
				alert("Select an institution to be linked");
				return false;
			}
			return true;
		}

		function toggle(target){
			var objDiv = document.getElementById(target);
			if(objDiv){
				if(objDiv.style.display=="none"){
					objDiv.style.display = "block";
				}
				else{
					objDiv.style.display = "none";
				}
			}
			else{
				var divs = document.getElementsByTagName("div");
				for (var h = 0; h < divs.length; h++) {
				var divObj = divs[h];
					if(divObj.className == target){
						if(divObj.style.display=="none"){
							divObj.style.display="block";
						}
						else {
							divObj.style.display="none";
						}
					}
				}
			}
		}

		function verifyIconImage(f){
			var iconImageFile = document.getElementById("iconfile").value;
			if(iconImageFile){
				var iconExt = iconImageFile.substr(iconImageFile.length-4);
				iconExt = iconExt.toLowerCase();
				if((iconExt != '.jpg') && (iconExt != 'jpeg') && (iconExt != '.png') && (iconExt != '.gif')){
					document.getElementById("iconfile").value = '';
					alert("The file you have uploaded is not a supported image file. Please upload a jpg, png, or gif file.");
				}
				else{
					var fr = new FileReader;
					fr.onload = function(){
						var img = new Image;
						img.onload = function(){
							if((img.width>350) || (img.height>350)){
								document.getElementById("iconfile").value = '';
								img = '';
								alert("The image file must be less than 350 pixels in both width and height.");
							}
						};
						img.src = fr.result;
					};
					fr.readAsDataURL(document.getElementById("iconfile").files[0]);
				}
			}
		}

		function verifyIconURL(f){
			var iconImageFile = document.getElementById("iconurl").value;
			if(iconImageFile && (iconImageFile.substr(iconImageFile.length-4) != '.jpg') && (iconImageFile.substr(iconImageFile.length-4) != '.png') && (iconImageFile.substr(iconImageFile.length-4) != '.gif')){
				alert("The url you have entered is not for a supported image file. Please enter a url for a jpg, png, or gif file.");
			}
		}

		function isNumeric(sText){
		   	var ValidChars = "0123456789-.";
		   	var IsNumber = true;
		   	var Char;

		   	for(var i = 0; i < sText.length && IsNumber == true; i++){
			   Char = sText.charAt(i);
				if(ValidChars.indexOf(Char) == -1){
					IsNumber = false;
					break;
			  	}
		   	}
			return IsNumber;
		}
	</script>
</head>
<body>
	<?php
	$displayLeftMenu = false;
	include($SERVER_ROOT.'/includes/header.php');
	echo '<div class="navpath">';
	echo '<a href="../../index.php">Home</a> &gt;&gt; ';
	if($collid){
		echo '<a href="collprofiles.php?collid='.$collid.'&emode=1">Collection Management</a> &gt;&gt; ';
		echo '<b>'.$collData['collectionname'].' Mailing Address </b>';
	}
	else echo '<b>Mailing Addresses</b>';
	echo '</div>';
	?>
	<!-- This is inner text! -->
	<div id="innertext">
		<?php
		if($statusStr){
			?>
			<hr />
			<div style="margin:20px;font-weight:bold;color:red;">
				<?php echo $statusStr; ?>
			</div>
			<hr />
			<?php
		}
		if($isEditor){
			if($collid) echo '<h1>'.$collData['collectionname'].(array_key_exists('institutioncode',$collData)?' ('.$collData['institutioncode'].')':'').'</h1>';
			?>
			<div>
				<fieldset style="background-color:#FFF380;">
					<legend><b>Mailing Address</b></legend>
					<?php
					if($instArr = $addressManager->getAddress()){
						?>
						<div style="margin:25px;">
							<?php
							echo '<div>';
							echo $instArr['institutionname'].($instArr['institutioncode']?' ('.$instArr['institutioncode'].')':'');
							?>
							<a href="institutioneditor.php?emode=1&targetcollid=<?php echo $collid.'&iid='.$instArr['iid']; ?>" title="Edit institution address">
								<img src="../../images/edit.png" style="width:14px;" />
							</a>
							<a href="collmetadata.php?collid=<?php echo $collid.'&removeiid='.$instArr['iid']; ?>" title="Unlink institution address">
								<img src="../../images/drop.png" style="width:14px;" />
							</a>
							<?php
							echo '</div>';
							if($instArr['address1']) echo '<div>'.$instArr['address1'].'</div>';
							if($instArr['address2']) echo '<div>'.$instArr['address2'].'</div>';
							if($instArr['city'] || $instArr['stateprovince']) echo '<div>'.$instArr['city'].', '.$instArr['stateprovince'].' '.$instArr['postalcode'].'</div>';
							if($instArr['country']) echo '<div>'.$instArr['country'].'</div>';
							if($instArr['phone']) echo '<div>'.$instArr['phone'].'</div>';
							if($instArr['contact']) echo '<div>'.$instArr['contact'].'</div>';
							if($instArr['email']) echo '<div>'.$instArr['email'].'</div>';
							if($instArr['url']) echo '<div><a href="'.$instArr['url'].'">'.$instArr['url'].'</a></div>';
							if($instArr['notes']) echo '<div>'.$instArr['notes'].'</div>';
							?>
						</div>
						<?php
					}
					else{
						//Link new institution
						?>
						<div style="margin:40px;"><b>No addesses linked</b></div>
						<div style="margin:20px;">
							<form name="addaddressform" action="collmetadata.php" method="post" onsubmit="return verifyAddAddressForm(this)">
								<select name="iid" style="width:425px;">
									<option value="">Select Institution Address</option>
									<option value="">------------------------------------</option>
									<?php
									$addrArr = $addressManager->getInstitutionArr();
									foreach($addrArr as $iid => $name){
										echo '<option value="'.$iid.'">'.$name.'</option>';
									}
									?>
								</select>
								<input name="collid" type="hidden" value="<?php echo $collid; ?>" />
								<input name="action" type="submit" value="Link Address" />
							</form>
							<div style="margin:15px;">
								<a href="institutioneditor.php?emode=1&targetcollid=<?php echo $collid; ?>" title="Add a new address not on the list">
									<b>Add an institution not on list</b>
								</a>
							</div>
						</div>
						<?php
					}
					?>
				</fieldset>
			</div>
			<?php
		}
		?>
	</div>
	<?php
	include($SERVER_ROOT.'/includes/footer.php');
	?>
</body>
</html>