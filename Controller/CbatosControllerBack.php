<?php

namespace Cbatos\Controller;

use Cbatos\Cbatos;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Translation\Translator;
use Thelia\Model\OrderQuery;
use Cbatos\Model\Config;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Model\OrderStatus;
use Thelia\Model\OrderStatusQuery;

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
$message="message=$date";
$pathfile="pathfile=".__DIR__."/../parm/pathfile.".$c["CBATOS_SIPSSOLUTIONS"]; //Auto search pathfile
$path_bin = __DIR__."/../bin/response"; //Auto search bin response

$result=exec("$path_bin $pathfile $message");
$tableau = explode ("!", $result);
$code = $tableau[1];
$error = $tableau[2];
$response_code = $tableau[11];
$customer_id = $tableau[26];
$order_id = $tableau[27];
if ($response_code == "02") { $tradcode = Translator::getInstance()->trans("Contact your banque for ask add amount limit on your card"); } elseif ($response_code == "03") { $tradcode = Translator::getInstance()->trans("Invalid contract"); } elseif ($response_code == "04") { $tradcode = Translator::getInstance()->trans("Hold card"); } elseif ($response_code == "05") { $tradcode = Translator::getInstance()->trans("Authorization declined"); } elseif ($response_code == "07") { $tradcode = Translator::getInstance()->trans("Hold card, Specials conditions"); } elseif ($response_code == "12") { $tradcode = Translator::getInstance()->trans("Invalid transaction, check the parameters passed in
request"); } elseif ($response_code == "33") { $tradcode = Translator::getInstance()->trans("Card is expired"); } elseif ($response_code == "34") { $tradcode = Translator::getInstance()->trans("Suspected fraud"); } elseif ($response_code == "41") { $tradcode = Translator::getInstance()->trans("Card is lost"); } elseif ($response_code == "43") { $tradcode = Translator::getInstance()->trans("Card stolen"); } elseif ($response_code == "51") { $tradcode = Translator::getInstance()->trans("Insufficient funds or limit exceeded"); } elseif ($response_code == "56") { $tradcode = Translator::getInstance()->trans("Card missing"); } elseif ($response_code == "57") { $tradcode = Translator::getInstance()->trans("Not allowed to carrier transaction"); } elseif ($response_code == "59") { $tradcode = Translator::getInstance()->trans("Suspected fraud"); } elseif ($response_code == "17") { $tradcode = Translator::getInstance()->trans("Your transaction has been canceled by yourself"); } else {
$tradcode = Translator::getInstance()->trans("unknown");
}
if(is_numeric($order_id))
$order_id=(int) $order_id;

$order = OrderQuery::create()->findPk($order_id);

if (( $code == "" ) && ( $error == "" ) ) {
$errormsg = "Error to call API RESPONSE ATOS<br>Execitable not found".$path_bin;
echo $errormsg;
} elseif ($code != 0) {
$errormsg = "Error in Call to API ATOS RESPONSE <br><br> Error :".$error;
echo $errormsg;
} else {

}

if ($response_code == "00") {
$info = Translator::getInstance()->trans("Your transaction is accept");
//si le paiement est accepter alors on fait ce qu'il faut
//le statut ne se change pas par le retour client
//mais par le retour IPN de ATOS (plus sécurisé)
//on fait uniquement une redirection sur order placed
    return $this->render("order-placed",
    array(
    "placed_order_id" => $order_id,
    ));

} elseif ($response_code =="17") {
        $token=null;
        $order = $this->checkorder($order_id,$token);
        $event = new OrderEvent($order);
        $event->setStatus(OrderStatusQuery::create()->findOneByCode(OrderStatus::CODE_CANCELED)->getId());
        $this->dispatch(TheliaEvents::ORDER_UPDATE_STATUS,$event);

        return $this->render("order-failed",
    array(
    "failed_order_id" => $order_id,
    "refusmotif" => $tradcode
    ));
} else {
    return $this->render("order-failed",
    array(
    "failed_order_id" => $order_id,
    "refusmotif" => $tradcode
    ));

}

//return $this->render("result",
//array(
//"order_id"=>$order_id,
//"msg"=>$info
//));
}

public function checkorder($order_id, &$token)
    {


        $customer_id = $this->getRequest()->getSession()->getCustomerUser()->getId();
        $order =OrderQuery::create()
            ->filterByCustomerId($customer_id)
            ->findPk($order_id);
        if ($order === null) {
            throw new \Exception("The order id is not valid. This order doesn't exists or doesn't belong to you.");
        }

        return $order;
    }

}
