<?php

namespace Cbatos\Controller;

use Cbatos\Cbatos;
use Thelia\Controller\Admin\BaseAdminController;
use Cbatos\Model\Config;
use Cbatos\Form\ConfigureCbatos;

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
                ->write(Cbatos::JSON_CONFIG_PATH)
                ;
$this->redirectToRoute("admin.module.configure",array(),
array ( 'module_code'=>"Cbatos",
'_controller' => 'Thelia\\Controller\\Admin\\ModuleController::configureAction'));
}
}
