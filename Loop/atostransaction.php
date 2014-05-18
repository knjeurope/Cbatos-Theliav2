<?php

namespace Cbatos\Loop;
use Cbatos\Cbatos;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\ArraySearchLoopInterface;
use Thelia\Core\Translation\Translator;
use Thelia\Model\OrderQuery;
use Cbatos\Model\Config;
use Thelia\Model\ConfigQuery;
class atostransaction extends BaseLoop implements ArraySearchLoopInterface
{
	protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntTypeArgument("orderid",0, true)
        );
    }
	
    
    public function buildArray()
    {
	$order_id = $this->getOrderid();
	$order = OrderQuery::create()->findPk($order_id);	
		
        $ret = array();
        $dir = __DIR__."/../Transactions/";
        if (!is_readable($dir)) {
            $ret[] = array("ERRMES"=>Translator::getInstance()->trans("Can't read transaction directory file pls CHMOD 777"), "ERRFILE"=>"");
        }
        if (!is_writable($dir)) {
            $ret[] = array("ERRMES"=>Translator::getInstance()->trans("Can't write transaction directory file pls CHMOD 777"), "ERRFILE"=>"");
        }
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if (strlen($file) > 5 && substr($file, -5) === ".json") {
                    if (!is_readable($dir.$file)) {
                        $ret[] = array("ERRMES"=>Translator::getInstance()->trans("Can't read file"), "ERRFILE"=>"Cbatos/Config/".$file);
                    }
                    if (!is_writable($dir.$file)) {
                        $ret[] = array("ERRMES"=>Translator::getInstance()->trans("Can't write file"), "ERRFILE"=>"Cbatos/Config/".$file);
                    }
                }
            }
        }
		
         
		$transac = Config::read("/Transactions/Order-".$order->getId()."-".$order->getCustomerId().".json");
		
		$XMLContent= file("http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml");
        foreach ($XMLContent as $line) {
                if (ereg("currency='USD'",$line,$currencyCode)) {
                    if (ereg("rate='([[:graph:]]+)'",$line,$rate)) {

                        $usdtaux = $rate[1];

                    }
                }
        }
$cardnorme = str_replace(".", "XXXXXXXXXX", $transac["CARD"]);
//EUR ORIGINETranslator::getInstance()->trans("Access is denied")
$montantpaidEUR = number_format($transac["AMOUNT"]/100, 2, ',', ' ')." EUR";
$montantpaidUSD = number_format($montantpaidEUR/$usdtaux, 2, ',', ' ')." USD";
$montantpaidFRF = number_format($montantpaidEUR*6.55957, 2, ',', ' ')." FRF";
//FRF and //USD{$MONTANT_TRANS_EUR}
$datetrans = str_split($transac["DATE"], 2);
$timetrans = str_split($transac["TIME"], 2);


 

//recupinformationboutique
$store = ConfigQuery::create();
$storcpville = $store->read("store_zipcode")."".$store->read("store_city");
		$ret[] = array(
		"ORDERID"=>$order->getId(),
		"MESSAGE_HAUT_TICKET_ATOS"=>Translator::getInstance()->trans("WWW.YOURSITE.COM"),
		"METHOD_PAID"=>Translator::getInstance()->trans("CARTE BANCAIRE"),
		"LE"=>Translator::getInstance()->trans("LE"),
		"A"=>Translator::getInstance()->trans("A"),
		"FIN"=>Translator::getInstance()->trans("FIN"),
		"MONT"=>Translator::getInstance()->trans("MONTANT"),
		"INFO"=>Translator::getInstance()->trans("Pour information"),
		"BYE"=>Translator::getInstance()->trans("Au revoir"),
		"CONSERVE"=>Translator::getInstance()->trans("A CONSERVER"),
		"MESSAGE_TICKET_CLIENT"=>Translator::getInstance()->trans("TICKET CLIENT"),
		"autorisation"=>$transac["AUTO"],
		"MERCHANT"=>$transac["MARCHAND"],
		"CERTIFICAT"=>$transac["CERTIFICAT"],
		"TRANS_ID"=>$transac["REF"],
		"card"=>$transac["CARD"],
		"MONTANT_TRANS_EUR"=>$montantpaidEUR,
		"MONTANT_TRANS_FRF"=>$montantpaidFRF,
		"MONTANT_TRANS_USD"=>$montantpaidUSD,
		"CB_CRYPTE"=>$cardnorme,
		"STORE_NAME"=>$store->read("store_name"),
		"STORE_LINE1"=>$store->read("store_address1"),
		"STORE_CP"=>$storcpville,
		"DATE_TRANS"=>$datetrans[0]."/".$datetrans[2]."/".$datetrans[1],
		"TIME_TRANS"=>$timetrans[0].":".$timetrans[1].":".$timetrans[2],
		"order_ref"=>$order->getRef()
		);
		

        return $ret;
    }
 
  public function parseResults(LoopResult $loopResult)
    {
        foreach ($loopResult->getResultDataCollection() as $arr) {
            $loopResultRow = new LoopResultRow();
            $loopResultRow->set("ERRMES", $arr["ERRMES"])
                ->set("ERRFILE", $arr["ERRFILE"])
				->set("A", $arr["A"])
				->set("MESSAGE_HAUT_TICKET_ATOS", $arr["MESSAGE_HAUT_TICKET_ATOS"])
				->set("METHOD_PAID", $arr["METHOD_PAID"])
				->set("LE", $arr["LE"])
				->set("FIN", $arr["FIN"])
				->set("MONT", $arr["MONT"])
				->set("INFO", $arr["INFO"])
								->set("order_ref", $arr["order_ref"])

				->set("STORE_NAME", $arr["STORE_NAME"])
				->set("STORE_LINE1", $arr["STORE_LINE1"])
				->set("STORE_CP", $arr["STORE_CP"])
				->set("DATE_TRANS", $arr["DATE_TRANS"])
				->set("TIME_TRANS", $arr["TIME_TRANS"])
				
				->set("MONTANT_TRANS_EUR", $arr["MONTANT_TRANS_EUR"])
				->set("MONTANT_TRANS_FRF", $arr["MONTANT_TRANS_FRF"])
				->set("MONTANT_TRANS_USD", $arr["MONTANT_TRANS_USD"])
				->set("CB_CRYPTE", $arr["CB_CRYPTE"])
				
			 
				->set("BYE", $arr["BYE"])
				->set("CONSERVE", $arr["CONSERVE"])
				->set("MESSAGE_TICKET_CLIENT", $arr["MESSAGE_TICKET_CLIENT"])
				
				->set("autorisation", $arr["autorisation"])
				->set("MERCHANT", $arr["MERCHANT"])
				->set("CERTIFICAT", $arr["CERTIFICAT"])
				->set("TRANS_ID", $arr["TRANS_ID"])
				->set("card", $arr["card"])
				
				->set("ORDERID", $arr["ORDERID"]);
            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }   

     
}
