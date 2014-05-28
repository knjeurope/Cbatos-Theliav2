<?php

namespace Cbatos\Controller;

use Cbatos\Cbatos;
use Thelia\Controller\Admin\BaseAdminController;
use Cbatos\Model\Config;
use Cbatos\Form\ConfigureCbatos;
use Thelia\Tools\URL;
use Symfony\Component\Routing\Router; 
class CbatosAdminSave extends BaseAdminController
{
function save()
{

		$error_message="";
        $conf = new Config();
        $form = new ConfigureCbatos($this->getRequest());
        $vform = $this->validateForm($form);
		$conf->setCBATOSMERCHANTID($vform->get('MerchantId')->getData())
                ->setCBATOSSIPSSOLUTIONS($vform->get('SipsSolutions')->getData())
				->setCBATOSCAPTUREDAYS($vform->get('Capturedays')->getData())
				->setCBATOSDEVISES($vform->get('Devises')->getData())
				->setCBATOSMODEDEBUG($vform->get('Modedebug')->getData())
                ->write(Cbatos::JSON_CONFIG_PATH);

//Generate PathFile
//Open or Create File if not exist
$WpAtH = fopen(__DIR__.'/../parm/pathfile.'.$vform->get('SipsSolutions')->getData(), 'a+');
//Write information
ftruncate($WpAtH,0);
fputs($WpAtH, '
DEBUG!YES!
D_LOGO!../../../logo/!
D_PARM!'.__DIR__.'/../!
F_DEFAULT!D_PARM!parm/parmcom.sherlocks!
F_PARAM!D_PARM!parm/parmcom!
F_CERTIFICATE!D_PARM!parm/certif!
');
//Close file
fclose($WpAtH);

//Generate GoodParcom Merchand Id
$myRouter = $this->container->get('router.Cbatos');

$WpAtHPARMCOM = fopen(__DIR__.'/../parm/parmcom.'.$vform->get('MerchantId')->getData(), 'a+');
ftruncate($WpAtHPARMCOM,0);
fputs($WpAtHPARMCOM, '
AUTO_RESPONSE_URL!'.URL::getInstance()->absoluteUrl($myRouter->generate("Cbatos.answer", array(), Router::ABSOLUTE_URL)).'!
CANCEL_URL!'.URL::getInstance()->absoluteUrl($myRouter->generate("Cbatos.manuel", array(), Router::ABSOLUTE_URL)).'!
RETURN_URL!'.URL::getInstance()->absoluteUrl($myRouter->generate("Cbatos.manuel", array(), Router::ABSOLUTE_URL)).'!
');
fclose($WpAtHPARMCOM);
$this->redirectToRoute("admin.module.configure",array(),
array ( 'module_code'=>"Cbatos",
'_controller' => 'Thelia\\Controller\\Admin\\ModuleController::configureAction'));
}
}
