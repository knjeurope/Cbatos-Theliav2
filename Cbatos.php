<?php
namespace Cbatos;

use Cbatos\Model\Config;
use Thelia\Core\Template\TemplateDefinition;
use Thelia\Model\Base\Template;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Propel;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Model\Message;
use Thelia\Model\MessageQuery;
use Thelia\Model\ModuleImageQuery;
use Thelia\Model\Order;
use Thelia\Module\AbstractPaymentModule;
use Thelia\Model\Base\ModuleQuery;
class Cbatos extends AbstractPaymentModule
{
const JSON_CONFIG_PATH = "/Config/config.json";
const CONFIRMATION_MESSAGE_NAME = 'atos_payment_confirmation';

    protected $_sKey;
    protected $_sUsableKey;

    protected $config;

public function postActivation(ConnectionInterface $con = null)
{

/* add template mail generate --  Modify for Thelia V2.0.1 */
/* Update 15 May 2014 */
$email_templates_dir = __DIR__.DS.'I18n'.DS.'email-templates'.DS;
if (null === MessageQuery::create()->findOneByName(self::CONFIRMATION_MESSAGE_NAME)) {

            $message = new Message();

            $message
                ->setName(self::CONFIRMATION_MESSAGE_NAME)

                ->setLocale('en_US')
                ->setTitle('Ticket Payment Credit Card ATOS')
                ->setSubject('Ticket Payment Credit Card - Order No. {$order_ref}')
                ->setHtmlMessage(file_get_contents($email_templates_dir.'en.html'))
                ->setTextMessage(file_get_contents($email_templates_dir.'en.txt'))

                ->setLocale('fr_FR')
                ->setTitle('Ticket de paiement carte bancaire ATOS')
                ->setSubject('Ticket de paiement carte bancaire - Commande N° {$order_ref}')
                ->setHtmlMessage(file_get_contents($email_templates_dir.'fr.html'))
                ->setTextMessage(file_get_contents($email_templates_dir.'fr.txt'))

                ->save()
            ;
        }

/* insert image module */
$module = $this->getModuleModel();
if (ModuleImageQuery::create()->filterByModule($module)->count() == 0) {
$this->deployImageFolder($module, sprintf('%s/images', __DIR__), $con);
}
}

 public function destroy(ConnectionInterface $con = null, $deleteModuleData = false)
    {
        // Delete config table and messages if required
        // update 15 may 2014
        if ($deleteModuleData) {
            MessageQuery::create()->findOneByName(self::CONFIRMATION_MESSAGE_NAME)->delete();
        }
    }

function pay(Order $order)
{
$c = Config::read(Cbatos::JSON_CONFIG_PATH);
//Variable for Atos Call Api
$parm="merchant_id=".$c["CBATOS_MERCHANTID"];
$parm="$parm merchant_country=fr";
$parm="$parm amount=".$order->getTotalAmount()*100;
if ($c["CBATOS_CAPTUREDAYS"] > "0") { $parm="$parm capture_day=".$c["CBATOS_CAPTUREDAYS"];  }
$parm="$parm currency_code=".$c["CBATOS_DEVISES"];
if ($c["CBATOS_CUSTOMERMAIL"] == "2") { $parm="$parm customer_email=".$this->getRequest()->getSession()->getCustomerUser()->getEmail(); }
if ($c["CBATOS_CUSTOMERID"] == "2") { $parm="$parm customer_id=".$this->getRequest()->getSession()->getCustomerUser()->getId(); }
if ($c["CBATOS_CUSTOMERIP"] == "2") { $parm="$parm customer_ip_address=".$_SERVER['REMOTE_ADDR']; }
$parm="$parm language=".$this->getRequest()->getSession()->getLang()->getCode();
$parm="$parm order_id=".$order->getId();
$parm="$parm pathfile=".$c["CBATOS_PATHBIN"]."parm/pathfile.".$c["CBATOS_SIPSSOLUTIONS"];
$parm="$parm normal_return_url=".$c["CBATOS_URLRETOUR"];
$parm="$parm cancel_return_url=".$c["CBATOS_URLRETOUR"];
$parm="$parm automatic_response_url=".$c["CBATOS_URLAUTOMATIC"];
$parm="$parm transaction_id=".self::harmonise($order->getId(),'numeric',6);
$path_bin = $c["CBATOS_PATHBIN"]."/bin/request";

//Call to Api Request Atos
$result=exec("$path_bin $parm");
$tableau = explode ("!", "$result");
$code = $tableau[1];

if ($c["CBATOS_MODEDEBUG"] == "2") { $vars["ERRORATOS"] = $tableau[2];} else {

$vars["MESSAGE"] = $tableau[3];
}

$vars["CODEATOS"] = $tableau[1];

if (isset($error) && isset($error) ) { $erroratos = "<B>Error to connect API</B><br>Request not found :  $path_bin"; } elseif ($code != 0) { $erroratos = "<b>Error in Api request</b><br><br>Message Atos : $error <br>"; } else {    }

//Call Template Page for display Form Atos

$parser = $this->container->get("thelia.parser");
$parser->setTemplateDefinition(
            new TemplateDefinition(
                '01',
                TemplateDefinition::FRONT_OFFICE
           )
       );
 $render = $parser->render("atos.html",$vars);

 return Response::create($render);

}
public static function HtmlEncode($data)
    {
        $SAFE_OUT_CHARS = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890._-";
        $result = "";
        for ($i=0; $i<strlen($data); $i++) {
            if (strchr($SAFE_OUT_CHARS, $data{$i})) {
                $result .= $data{$i};
            } elseif (($var = bin2hex(substr($data,$i,1))) <= "7F") {
                $result .= "&#x" . $var . ";";
            } else
                $result .= $data{$i};

        }

        return $result;
    }
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
    public function getRequest()
    {
        return $this->container->get('request');
    }
/**
*
* This method is call on Payment loop.
*
* If you return true, the payment method will de display
* If you return false, the payment method will not be display
*
* @return boolean
*/

// Modify Update 15 May 2014 //
// Capable THELIA v2.0.1 //

public function isValidPayment()
    {
        return true;
    }

 public function getCode()
    {
        return 'Cbatos';
    }
public static function getModCode($flag=false)
    {
        $obj = new Cbatos();
        $mod_code = $obj->getCode();
        if($flag) return $mod_code;
        $search = ModuleQuery::create()
            ->findOneByCode($mod_code);

        return $search->getId();
    }
}
