<?php
namespace Cbatos\Controller;
use Cbatos\Cbatos;
use Thelia\Core\Translation\Translator;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Model\OrderQuery;
use Cbatos\Model\Config;
use Thelia\Tools\URL;
use Thelia\Model\OrderStatusQuery;

class CbatosControllerCall extends BaseFrontController {


protected $config;

function pay($order)
{

$ord = OrderQuery::create()->findPk($order);
       if($ord->getCustomerId() != $this->getSession()->getCustomerUser()->getId() ||
       $ord->getOrderStatus()->getCode() != Cbatos::ORDER_NOT_PAID)
       $ord = null;
if($ord !== null) {
$comid = $ord->getId(); 
$c = Config::read(Cbatos::JSON_CONFIG_PATH);
$montant = $ord->getTotalAmount();
$montantatos = $montant*100;
$reference = $comid.''.date("s");
$langclient = $this->getSession()->getLang()->getCode();
$emailclient = $this->getSession()->getCustomerUser()->getEmail();
$customid = $ord->getCustomerId();
$merchantid = $c["CBATOS_MERCHANTID"];
$urlautoma = $c["CBATOS_URLAUTOMATIC"];
$urlretour = $c["CBATOS_URLRETOUR"];
$jourcapt = $c["CBATOS_CAPTUREDAYS"];
$devic = $c["CBATOS_DEVISES"];
$ipcustom = $_SERVER['REMOTE_ADDR'];
//GENERATION DU FORMULAIRE DE PAIEMENT ATOS


$parm="merchant_id=$merchantid"; //Config Json OK
$parm="$parm merchant_country=fr"; //NE PAS TOUCHER PAYS DU MARCHAND PAR DEFAULT FR pour la FRANCE
$parm="$parm amount=$montantatos"; //NE PAS TOUCHER CALCUL DU MONTANT AU FORMAT ATOS

if($jourcapt > "0")
{
$parm="$parm capture_day=$jourcapt"; //Config Json OK        
}
$parm="$parm currency_code=$devic"; //Config Json OK
	

//PARAMETRE CONDITIONNELLE
//A CONFIGURER DANS BACKOFFICE

if($c["CBATOS_CUSTOMERMAIL"] == "2")
{
	$parm="$parm customer_email=$emailclient"; //ENVOIE OU PAS LE MAIL DU CLIENT DANS LA TRANSACTION ATOS
}
if($c["CBATOS_CUSTOMERID"] == "2")
{
	$parm="$parm customer_id=$customid"; //ENVOIE OU PAS LE NUMERO DU CLIENT DANS LA TRANSACTION ATOS
}
if($c["CBATOS_CUSTOMERIP"] == "2")
{
$parm="$parm customer_ip_address=$ipcustom"; //ENVOIE OU PAS LIP DU CLIENT DANS LA TRANSACTION ATOS
}
//FIN DE CONFIGURER BACKOFFICE


$parm="$parm language=$langclient"; //NE PAS TOUCHER PERMET LA TRADUCTION EN FONCTION DE LA LANGUE DU CLIENT AUTOMATIQUEMENT
$parm="$parm order_id=$comid"; //NE PAS TOUCHER PRIMORDIALE AU BON FONCTIONNEMENT DU MODULE NUMERO DE COMMANDE      
$parm="$parm pathfile=/home/knjhairf/knjhair.fr/html/local/modules/Cbatos/parm/pathfile"; //NE PAS TOUCHER FICHIER CONFIG ET CERTIF DANS REPERTOIRE PARM
$parm="$parm normal_return_url=$urlretour"; // Config Json Ok
$parm="$parm cancel_return_url=$urlretour"; // Config Json OK
$parm="$parm automatic_response_url=$urlautoma"; // Config Json Ok
$parm="$parm transaction_id=" . $reference; // GENERATION NUMERODECOMMANDE ET SECONDE DE LA COMMANDE NE PAS TOUCHER RISQUE DE BLOCAGE ATOS
$path_bin = "/home/knjhairf/knjhair.fr/html/local/modules/Cbatos/bin/request"; //NE PAS TOUCHER FICHIER API REQUEST DEJA INCLUS ATTENTION TRANSFERT FTP BINAIRE
        
// APPEL ATOS
$parm = escapeshellcmd($parm);
$result=exec("$path_bin $parm");
 
 
$tableau = explode ("!", "$result");
$code = $tableau[1];
$error = $tableau[2];
$message = $tableau[3];
if (( $code == "" ) && ( $error == "" ) )
{
$erroratos = "<B>Error to connect API</B><br>Request not found :  $path_bin";
}
else if ($code != 0){
$erroratos = "<b>Error in Api request</b><br><br>Message Atos : $error <br>";
}
else {
        $formpaiement = $message;
}

// MODE DEBUG ACTIF OU PAS SUIVANT CONFIGURATION BACKOFFICE

if($c["CBATOS_MODEDEBUG"] == "2")
{

$erroratos = $error;
}
  return $this->render(
       "atos", 
       array(
           'formulaire' => $message,
           'error' => $erroratos 
    ));}
exit;
}
}
