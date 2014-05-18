<?php

namespace Cbatos\Controller;

use Cbatos\Cbatos;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\Event\Cart\CartEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Translation\Translator;
use Thelia\Model\OrderQuery;
use Cbatos\Model\Config;

class CbatosControllerBack extends BaseFrontController
{
protected $config;
function manu()
{

$date = $_POST['DATA'];
if (!$date) {
echo Translator::getInstance()->trans("Access is denied");
exit;
}

$c = Config::read(Cbatos::JSON_CONFIG_PATH);
$pathinstallmodule = $c["CBATOS_PATHBIN"];
$message="message=$date";
$pathfile="pathfile=".$c["CBATOS_PATHBIN"]."parm/pathfile.".$c["CBATOS_SIPSSOLUTIONS"];
$path_bin = $c["CBATOS_PATHBIN"]."/bin/response";
$result=exec("$path_bin $pathfile $message");
$tableau = explode ("!", $result);
$code = $tableau[1];
$error = $tableau[2];

$response_code = $tableau[11];

$customer_id = $tableau[26];
$order_id = $tableau[27];

if ($response_code == "02") { $tradcode = Translator::getInstance()->trans("Contact your banque"); }
if ($response_code == "03") { $tradcode = Translator::getInstance()->trans("Invalid contract"); }
if ($response_code == "04") { $tradcode = Translator::getInstance()->trans("Hold card"); }
if ($response_code == "05") { $tradcode = Translator::getInstance()->trans("Not paid"); }
if ($response_code == "07") { $tradcode = Translator::getInstance()->trans("Hold card, Specials conditions"); }
if ($response_code == "33") { $tradcode = Translator::getInstance()->trans("Card is expired"); }
if ($response_code == "34") { $tradcode = Translator::getInstance()->trans("Suspected fraud"); }
if ($response_code == "41") { $tradcode = Translator::getInstance()->trans("Card is lost"); }
if ($response_code == "43") { $tradcode = Translator::getInstance()->trans("Card stolen"); }
if ($response_code == "51") { $tradcode = Translator::getInstance()->trans("Insufficient funds or limit exceeded"); }
if ($response_code == "56") { $tradcode = Translator::getInstance()->trans("Card missing"); }
if ($response_code == "57") { $tradcode = Translator::getInstance()->trans("Not allowed to carrier transaction"); }
if ($response_code == "59") { $tradcode = Translator::getInstance()->trans("Suspected fraud"); }
if ($response_code == "17") { $tradcode = Translator::getInstance()->trans("Your transaction has been canceled by yourself"); }

if(is_numeric($order_id))
$order_id=(int) $order_id;

$order = OrderQuery::create()->findPk($order_id);

if (( $code == "" ) && ( $error == "" ) ) {
$errormsg = "Error to call API RESPONSE ATOS<br>Execitable not found".$path_bin;
echo $errormsg;
}

else if ($code != 0) {
$errormsg = "Error in Call to API ATOS RESPONSE <br><br> Error :".$error;
echo $errormsg;
}
else {

}



if ($response_code == "00") {
$info = Translator::getInstance()->trans("Your transaction is accept");


} else {
$info = Translator::getInstance()->trans("Your transaction is declined");
}

return $this->render("result",
array(
"order_id"=>$order_id,
"msg"=>$info
));
}
}
