<?php
//Update V1.4
//Atos Cb by KnjEurope
//Changelog : Delete path predefinied for pathfilbin , now is automatically indicate with __DIR__
//			  +++ Automatic search return url and ipn url
//			  +++ Automatic search parm file
//			  --- DELETE choice if customer id is sent or no to atos, now IS REQUIRED

namespace Cbatos;

use Cbatos\Model\Config;
use Propel\Runtime\Connection\ConnectionInterface;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Template\TemplateDefinition;
use Thelia\Model\Base\Template;
use Thelia\Model\ModuleImageQuery;
use Thelia\Model\Order;
use Thelia\Module\AbstractPaymentModule;
use Thelia\Module\BaseModule;
use Thelia\Module\PaymentModuleInterface;
use Thelia\Tools\URL;
use Symfony\Component\Routing\Router;

class Cbatos extends AbstractPaymentModule
{
const JSON_CONFIG_PATH = "/Config/config.json";
const CONFIRMATION_MESSAGE_NAME = 'atos_payment_confirmation';
protected $_sKey;
protected $_sUsableKey;
protected $config;




public function postActivation(ConnectionInterface $con = null)
{
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

 
$module = $this->getModuleModel();
if (ModuleImageQuery::create()->filterByModule($module)->count() == 0) {
$this->deployImageFolder($module, sprintf('%s/images', __DIR__), $con);
}
}
public function destroy(ConnectionInterface $con = null, $deleteModuleData = false)
    {
        if ($deleteModuleData) {
            MessageQuery::create()->findOneByName(self::CONFIRMATION_MESSAGE_NAME)->delete();
        }
    }



  public function pay(Order $order)
    {
        return $this->doPay($order, 'SINGLE');
    }
	
	
 protected function doPay(Order $order)
{
 
 
$c = Config::read(Cbatos::JSON_CONFIG_PATH);
$parm="merchant_id=".$c["CBATOS_MERCHANTID"];
$parm="$parm merchant_country=fr";
$parm="$parm amount=".$order->getTotalAmount()*100;
if ($c["CBATOS_CAPTUREDAYS"] > "0") { $parm="$parm capture_day=".$c["CBATOS_CAPTUREDAYS"];  }
$parm="$parm currency_code=".$c["CBATOS_DEVISES"];
if ($c["CBATOS_CUSTOMERMAIL"] == "2") { $parm="$parm customer_email=".$this->getRequest()->getSession()->getCustomerUser()->getEmail(); }
$parm="$parm customer_id=".$this->getRequest()->getSession()->getCustomerUser()->getId(); //Id customer Required
if ($c["CBATOS_CUSTOMERIP"] == "2") { $parm="$parm customer_ip_address=".$_SERVER['REMOTE_ADDR']; }
$parm="$parm language=".$this->getRequest()->getSession()->getLang()->getCode();
$parm="$parm order_id=".$order->getId();
$parm="$parm pathfile=".__DIR__."/parm/pathfile.".$c["CBATOS_SIPSSOLUTIONS"]; //Auto search pathfile
$parm="$parm normal_return_url=http://".$_SERVER['SERVER_NAME']."/cbatos/manuel"; //Auto defined return url
$parm="$parm cancel_return_url=http://".$_SERVER['SERVER_NAME']."/cbatos/manuel"; //Auto defined return url
$parm="$parm automatic_response_url=http://".$_SERVER['SERVER_NAME']."/cbatos/answer"; //Auto defined return url ipn
$parm="$parm transaction_id=".self::harmonise($order->getId(),'numeric',6);
$path_bin = __DIR__."/bin/request"; //Auto search bin request
 

 
$result=exec("$path_bin $parm");
$tableau = explode ("!", "$result");
if (empty($tableau[3]))  { 
echo 'Français : <br>Il semblerait que nous rencontrions un problème avec l\'appel du script request merci de vérifier que:<br>Le fichier soit bien présent<br>Que le Chmod est bien 755<br><br>Pour rappel voici le chemin absolue que nous essayons d\'appeler<br>'.$path_bin.'<br>ATTENTION : Nous vous rappelons que le fichier REQUEST et RESPONSE Doivent être uploader en MODE BINARY<br>Sans quoi le script ne pourras pas fonctionner <br><br><hr>English <br>It seems that we encounter a problem with the script call request thank you to verify that:<br>The file is indeed present<br>The chmod is 755<br>As a reminder here is the absolute way that we try to call<br>'.$path_bin.'<br>ATTENTION: Please note that the REQUEST and RESPONSE file uploader Must be in BINARY MODE<br>Otherwise the script will not be able to function'; 
 
exit;
}
 else   {
	
$code = $tableau[1];
if ($c["CBATOS_MODEDEBUG"] == "2") { $vars["ERRORATOS"] = $tableau[2]; } else { $vars["MESSAGE"] = $tableau[3]; }
$vars["CODEATOS"] = $tableau[1];



$parser = $this->container->get("thelia.parser");
$parser->setTemplateDefinition(
new TemplateDefinition(
'module_atos',
TemplateDefinition::FRONT_OFFICE
)
);
  
 $render = $parser->render("atos.html",$vars);

 return Response::create($render);
 
}
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
