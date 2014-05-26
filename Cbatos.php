<?php
namespace Cbatos;
use Cbatos\Model\Config;
use Symfony\Component\HttpFoundation\Request;

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
use Thelia\Controller\BaseController;
use Thelia\Tools\Redirect;
use Thelia\Core\Routing\RewritingRouter;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Model\Message;
use Thelia\Model\MessageQuery;
 
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
                ->save();
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
 
 		Redirect::exec(URL::getInstance()->absoluteUrl("/cbatos/paid/".$order->getId()));
 		 
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
