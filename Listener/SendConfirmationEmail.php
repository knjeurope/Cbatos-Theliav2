<?php
//update 15 May 2014//

namespace Cbatos\Listener;

use Cbatos\Cbatos;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Action\BaseAction;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Template\ParserInterface;
use Thelia\Log\Tlog;
use Thelia\Mailer\MailerFactory;
use Thelia\Model\ConfigQuery;
use Thelia\Model\MessageQuery;
use Cbatos\Model\Config;
use Thelia\Core\Translation\Translator;
/**
* Cbatos payment module
*
*/
class SendConfirmationEmail extends BaseAction implements EventSubscriberInterface
{
    /**
* @var MailerFactory
*/
    protected $mailer;
    /**
* @var ParserInterface
*/
    protected $parser;

    public function __construct(ParserInterface $parser, MailerFactory $mailer)
    {
        $this->parser = $parser;
        $this->mailer = $mailer;
    }

    /**
* @return \Thelia\Mailer\MailerFactory
*/
    public function getMailer()
    {
        return $this->mailer;
    }

    /**
* Checks if we are the payment module for the order, and if the order is paid,
* then send a confirmation email to the customer.
*
* @params OrderEvent $order
*/
    public function update_status(OrderEvent $event)
    {
        $cbatos = new Cbatos();

        if ($event->getOrder()->isPaid() && $cbatos->isPaymentModuleFor($event->getOrder())) {

            $contact_email = ConfigQuery::read('store_email', false);

            Tlog::getInstance()->debug("Sending confirmation email from store contact e-mail $contact_email");

            if ($contact_email) {
                $message = MessageQuery::create()
                    ->filterByName(Cbatos::CONFIRMATION_MESSAGE_NAME)
                    ->findOne();

                if (false === $message) {
                    throw new \Exception(sprintf("Failed to load message '%s'.", Payzen::CONFIRMATION_MESSAGE_NAME));
                }



                $order = $event->getOrder();
                $customer = $order->getCustomer();

$transac = Config::read("/Transactions/Order-".$order->getId()."-".$customer->getID().".json");
//ecuperation des valeurs de la transaction
//on decrypte la date
$datetrans = str_split($transac["DATE"], 2);
$timetrans = str_split($transac["TIME"], 2);
$cardnorme = str_replace(".", "XXXXXXXXXX", $transac["CARD"]);
//Conversion des montant
//on recupere le taux de la BCE (banque europenne)
// fichier http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml
$XMLContent= file("http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml");
        foreach ($XMLContent as $line) {
                if (ereg("currency='USD'",$line,$currencyCode)) {
                    if (ereg("rate='([[:graph:]]+)'",$line,$rate)) {

                        $usdtaux = $rate[1];

                    }
                }
        }

//EUR ORIGINETranslator::getInstance()->trans("Access is denied")
$montantpaidEUR = number_format($transac["AMOUNT"]/100, 2, ',', ' ')." EUR";
$montantpaidUSD = number_format($montantpaidEUR/$usdtaux, 2, ',', ' ')." USD";
$montantpaidFRF = number_format($montantpaidEUR*6.55957, 2, ',', ' ')." FRF";
//FRF and //USD{$MONTANT_TRANS_EUR}
//on recup les infos de la config
$store = ConfigQuery::create();
$this->parser->assign('STORE_NAME', $store->read("store_name"));
$this->parser->assign('STORE_LINE1', $store->read("store_address1"));
$storcpville = $store->read("store_zipcode")."".$store->read("store_city");
$this->parser->assign('STORE_CP', $storcpville);

 $this->parser->assign('autorisation', $transac["AUTO"]);
 $this->parser->assign('MERCHANT', $transac["MARCHAND"]);
 $this->parser->assign('CB_CRYPTE', $cardnorme);
 $this->parser->assign('CERTIFICAT', $transac["CERTIFICAT"]);
  $this->parser->assign('TRANS_ID', $transac["REF"]);
 //on fait passer les valeurs de traduction des messages

$this->parser->assign('MESSAGE_HAUT_TICKET_ATOS', Translator::getInstance()->trans("WWW.YOURSITE.COM"));
$this->parser->assign('METHOD_PAID', Translator::getInstance()->trans("CARTE BANCAIRE"));
$this->parser->assign('LE', Translator::getInstance()->trans("LE"));
$this->parser->assign('A', Translator::getInstance()->trans("A"));
  $this->parser->assign('FIN', Translator::getInstance()->trans("FIN"));
  $this->parser->assign('MONT', Translator::getInstance()->trans("MONTANT"));
  $this->parser->assign('INFO', Translator::getInstance()->trans("Pour information"));
  $this->parser->assign('MESSAGE_TICKET_CLIENT', Translator::getInstance()->trans("TICKET CLIENT"));
  $this->parser->assign('CONSERVE', Translator::getInstance()->trans("A CONSERVER"));
  $this->parser->assign('BYE', Translator::getInstance()->trans("Au revoir"));

  $this->parser->assign('MONTANT_TRANS_EUR', $montantpaidEUR);
  $this->parser->assign('MONTANT_TRANS_FRF', $montantpaidFRF);
  $this->parser->assign('MONTANT_TRANS_USD', $montantpaidUSD);

$this->parser->assign('DATE_TRANS', $datetrans[0]."/".$datetrans[2]."/".$datetrans[1]);
$this->parser->assign('TIME_TRANS', $timetrans[0].":".$timetrans[1].":".$timetrans[2]);
$this->parser->assign('card', $transac["CARD"]);


                $this->parser->assign('order_id', $order->getId());
                $this->parser->assign('order_ref', $order->getRef());

                $message
                    ->setLocale($order->getLang()->getLocale());

                $instance = \Swift_Message::newInstance()
                    ->addTo($customer->getEmail(), $customer->getFirstname()." ".$customer->getLastname())
                    ->addFrom($contact_email, ConfigQuery::read('store_name'))
                ;

                // Build subject and body
                $message->buildMessage($this->parser, $instance);

                $this->getMailer()->send($instance);

                Tlog::getInstance()->debug("Confirmation email sent to customer ".$customer->getEmail());
            }
        }
        else {
            Tlog::getInstance()->debug("No confirmation email sent (order not paid, or not the proper payement module.");
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            TheliaEvents::ORDER_UPDATE_STATUS => array("update_status", 128)
        );
    }
}