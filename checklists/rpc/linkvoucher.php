<?php
include_once('../../config/symbini.php');
include_once($SERVER_ROOT.'/classes/ChecklistVoucherAdmin.php');
header("Content-Type: text/html; charset=".$CHARSET);

$sciname = $_POST['sciname'];
$occid = $_POST['occid'];
$clid = $_POST['clid'];

$status = 0;
if($sciname && is_numeric($occid) && is_numeric($clid)){
	$clManager = new ChecklistVoucherAdmin();
	$clManager->setClid($clid);
	$status = $clManager->linkVoucher($sciname,$occid);
}
echo $status;
?>