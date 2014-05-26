<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.     */
/*                                                                                   */
/*************************************************************************************/

namespace Cbatos\Form;

use Cbatos\Cbatos;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Form\BaseForm;
use Thelia\Core\Translation\Translator;

class ConfigureCbatos extends BaseForm
{
    public function getName()
    {
        return "configurecbatos";
    }

    protected function buildForm()
    {
        $values = null;
        $path = __DIR__."/../".Cbatos::JSON_CONFIG_PATH;
        if (is_readable($path)) {
            $values = json_decode(file_get_contents($path),true);
        }
        $this->formBuilder

            ->add('MerchantId', 'text', array(
                'label' => Translator::getInstance()->trans('MerchantId'),
                'label_attr' => array(
                    'for' => 'MerchantId'
                ),
                'data' => (null === $values ?'':$values["CBATOS_MERCHANTID"]),
                'constraints' => array(
                    new NotBlank()
                )
            ))

            ->add('SipsSolutions', 'text', array(
                                'label' => Translator::getInstance()->trans('SipsSolutions'),
                                'label_attr' => array(
                                        'for' => 'SipsSolutions'
                                ),
                'data' => (null === $values ?'':$values["CBATOS_SIPSSOLUTIONS"]),
                'constraints' => array(
                                        new NotBlank()
                                )
                        ))

             

    

->add('Capturedays', 'text', array(
                                'label' => Translator::getInstance()->trans('Capturedays'),
                                'label_attr' => array(
                                        'for' => 'Capturedays'
                                ),
                'data' => (null === $values ?'':$values["CBATOS_CAPTUREDAYS"]),
                'constraints' => array(
                                        new NotBlank()
                                )
                        ))

->add('Devises', 'text', array(
                                'label' => Translator::getInstance()->trans('Devises'),
                                'label_attr' => array(
                                        'for' => 'Devises'
                                ),
                'data' => (null === $values ?'':$values["CBATOS_DEVISES"]),
                'constraints' => array(
                                        new NotBlank()
                                )
                        ))
->add('Modedebug', 'text', array(
                                'label' => Translator::getInstance()->trans('Modedebug'),
                                'label_attr' => array(
                                        'for' => 'Modedebug'
                                ),
                'data' => (null === $values ?'':$values["CBATOS_MODEDEBUG"]),
                'constraints' => array(
                                        new NotBlank()
                                )
                        ))

        ;
    }
}
