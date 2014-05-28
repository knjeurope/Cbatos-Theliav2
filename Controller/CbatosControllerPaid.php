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
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Model\OrderStatus;
use Thelia\Model\OrderStatusQuery;
use Thelia\Core\Template\TemplateHelper;
use Thelia\Tools\URL;
use Symfony\Component\Routing\Router; 

class CbatosControllerPaid extends BaseFrontController
{
//START CLASS DO NOT MODIFY
protected $config;

 function paid($order_id)
{
	$myRouter = $this->container->get('router.Cbatos');
	$order = OrderQuery::create()->findPk($order_id);
if(empty($order)) {
	echo 'Erreur, Demande de paiement non valide, OrderId inexistant...<br>Error, Payment request not valid, OrderId not found !';
	exit;
	}

	//STARTING API REQUEST TO BANKING


/* READ CONFIG FILE ATOS THELIA */
$c = Config::read(Cbatos::JSON_CONFIG_PATH);
/* -----------END --------------*/	
// Selector Routing //

	
 

/* CREATE PARM VARS WITH ALL NEEDED INFORMATION BY ATOS API REQUEST */
$parm="merchant_id=".$c["CBATOS_MERCHANTID"]; //Contract Number with Bank
$parm="$parm merchant_country=fr"; // Country Merchant Acceptance
$parm="$parm amount=".$order->getTotalAmount()*100; // Amount for paid 
if ($c["CBATOS_CAPTUREDAYS"] > "0") { $parm="$parm capture_day=".$c["CBATOS_CAPTUREDAYS"];  } // Differed capture days
$parm="$parm currency_code=".$c["CBATOS_DEVISES"]; // Currency
$parm="$parm customer_email=".$this->getRequest()->getSession()->getCustomerUser()->getEmail(); // Customer email
$parm="$parm customer_id=".$this->getRequest()->getSession()->getCustomerUser()->getId(); //Customer Id
$parm="$parm customer_ip_address=".$_SERVER['REMOTE_ADDR']; // Customer ip
$parm="$parm language=".$this->getRequest()->getSession()->getLang()->getCode();
$parm="$parm order_id=".$order_id;
$parm="$parm pathfile=".__DIR__."/../parm/pathfile.".$c["CBATOS_SIPSSOLUTIONS"]; //Auto search pathfile
$parm="$parm normal_return_url=".URL::getInstance()->absoluteUrl($myRouter->generate("Cbatos.manuel", array(), Router::ABSOLUTE_URL)).""; //Auto defined return url
$parm="$parm cancel_return_url=".URL::getInstance()->absoluteUrl($myRouter->generate("Cbatos.manuel", array(), Router::ABSOLUTE_URL)).""; //Auto defined return url
$parm="$parm automatic_response_url=".URL::getInstance()->absoluteUrl($myRouter->generate("Cbatos.answer", array(), Router::ABSOLUTE_URL)).""; //Auto defined return url ipn
$parm="$parm transaction_id=".self::harmonise(date("is").$order->getId(),'numeric',6); // Transaction ID
$path_bin = __DIR__."/../bin/request"; //Auto search bin request



$result=exec("$path_bin $parm");

$tableau = explode ("!", "$result");

 
// MODE DEBUG OR NO //
if ($c["CBATOS_MODEDEBUG"] == "2") { $vars["ERRORATOS"] = $tableau[2]; } else { $vars["MESSAGE"] = $tableau[3]; }
// RETRIEVE ANSWER RESPONSES CODE ATOS
$vars["CODEATOS"] = $tableau[1];

 
return $this->render("order-payment", $vars);
			
	
}

  /**
* @return ParserInterface instance parser
*/
    protected function getParser($template = null)
    {
        $parser = $this->container->get("thelia.parser");

        // Define the template that should be used
        $parser->setTemplateDefinition($template ?: TemplateHelper::getInstance()->getActiveFrontTemplate());

        return $parser;
    }
	 /**
* Render the given template, and returns the result as an Http Response.
*
* @param $templateName the complete template name, with extension
* @param array $args the template arguments
* @param int $status http code status
* @return \Thelia\Core\HttpFoundation\Response
*/
    protected function render($templateName, $args = array(), $status = 200)
    {
        return Response::create($this->renderRaw($templateName, $args), $status);
    }
	 /**
* Render the given template, and returns the result as a string.
*
* @param $templateName the complete template name, with extension
* @param array $args the template arguments
* @param null $templateDir
*
* @return string
*/
    protected function renderRaw($templateName, $args = array(), $templateDir = null)
    {

        // Add the template standard extension
        $templateName .= '.html';

        $session = $this->getRequest()->getSession();

        // Prepare common template variables
        $args = array_merge($args, array(
                'locale' => $session->getLang()->getLocale(),
                'lang_code' => $session->getLang()->getCode(),
                'lang_id' => $session->getLang()->getId(),
                'current_url' => $this->getRequest()->getUri()
            ));

        // Render the template.
        $data = $this->getParser($templateDir)->render($templateName, $args);

        return $data;
    }
	
//ARMONIZE FUNCTION DO NOT MODIFY
public static function harmonise($value, $type, $len)
    {
        switch ($type) {
            case 'numeric':
                $value = (string) $value;
                if(mb_strlen($value, 'utf8') > $len);
                $value = substr($value, 0, $len);
                for ($i = mb_strlen($value, 'utf8'); $i < $len; $i++) {
                    $value = '0' . $value;
                }
                break;
            case 'alphanumeric':
                $value = (string) $value;
                if(mb_strlen($value, 'utf8') > $len);
                $value = substr($value, 0, $len);
                for ($i = mb_strlen($value, 'utf8'); $i < $len; $i++) {
                    $value .= ' ';
                }
                break;
        }

        return $value;
    }
//END CLASS DO NOT MODIFY
}
