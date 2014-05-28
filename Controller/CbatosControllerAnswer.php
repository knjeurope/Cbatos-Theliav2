<?php
namespace Cbatos\Controller;
use Cbatos\Cbatos;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Model\OrderQuery;
use Cbatos\Model\Config;
use Cbatos\Model\AtosTransactions;
use Thelia\Model\OrderStatus;
use Thelia\Model\OrderStatusQuery;
class CbatosControllerAnswer extends BaseFrontController
{
protected $config;

function resp()
{
$cb = Config::read(Cbatos::JSON_CONFIG_PATH);
$c = AtosTransactions::read(Cbatos::JSON_CONFIG_PATH);
$date = $_POST['DATA'];
$message="message=$date";
$pathfile="pathfile=".__DIR__."/../parm/pathfile.".$cb["CBATOS_SIPSSOLUTIONS"]; //Auto search pathfile
$path_bin = __DIR__."/../bin/response"; //Auto search bin request
$result=exec("$path_bin $pathfile $message");
$tableau = explode ("!", $result);
$code = $tableau[1];
$error = $tableau[2];
$response_code = $tableau[11];
$techno = "EMTPV";
$order_id = $tableau['27'];
$conf = new AtosTransactions();
$conf->setMARCHAND($tableau['3'])
->setDATE($tableau[10])
->setTIME($tableau[9])
->setCARD($tableau[15])
->setAUTO($tableau[13])
->setAMOUNT($tableau[5])
->setREF($tableau[6])
->setCURRENCY($tableau[14])
->setIPCUSTOMER($tableau[29])
->setEMAILCUSTOMER($tableau[28])
->setORDERID($tableau[27])
->setCUSTOMERID($tableau[26])
->setBANKRESPONSESCODE($tableau[18])
->setCERTIFICAT($tableau[12])
->setETP($techno)
->setCVVCODE($tableau[17])
->write("/Transactions/Order-".$order_id."-".$tableau[26].".json")
;

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
if ($response_code == "00") {
$code = $response_code;
$msg = "";
$event = new OrderEvent($order);
$event->setStatus(OrderStatusQuery::create()->findOneByCode(OrderStatus::CODE_PAID)->getId());
$order->setTransactionRef("$tableau[6]");
switch ($code) {

            case "00":
               $msg = "The payment of the order ".$order->getRef()." has been successfully released. ";
               $this->dispatch(TheliaEvents::ORDER_UPDATE_STATUS, $event);

            break;
               default:
           $msg = "Your payment is declined <br> Motif : Code $code , Code banque $bank_response_code";

            }
}
}

print($msg);
exit;

}

}
