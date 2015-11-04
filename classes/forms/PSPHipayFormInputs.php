<?php
/**
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2015 PrestaShop SA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

abstract class PSPHipayFormInputs {

    protected function generateFormSplit()
    {
        $params = array('col' => 12, 'offset' => 0);

        return $this->generateInput('free', 'input_split', null, $params);
    }

    protected function generateInput($type, $name, $label = false, $params = array())
    {
        $input = array(
            'type' => $type,
            'label' => $label,
            'name' => $name,
        );

        if (is_array($params) === true) {
            foreach ($params as $key => $value) {
                $input[$key] = $value;
            }
        }

        return $input;
    }

    protected function generateInputEmail($name, $title, $description)
    {
        return $this->generateInputText($name, $title, array(
            'required' => true,
            'desc' => $description,
            'placeholder' => $this->module->l('email@domain.com'),
        ));
    }

    protected function generateInputFree($name, $label = false, $params = array())
    {
        return $this->generateInput('free', $name, $label, $params);
    }

    protected function generateInputText($name, $label = false, $params = array())
    {
        return $this->generateInput('text', $name, $label, $params);
    }

    public function generateLegend($title, $icon = false)
    {
        return array(
            'title' => $title,
            'icon' => $icon,
        );
    }

    protected function generateSubmitButton($title, $params = array())
    {
        $input = array(
            'title' => $title,
            'type' => 'submit',
            'class' => 'btn btn-default pull-right'
        );

        if (is_array($params) === true) {
            foreach ($params as $key => $value) {
                $input[$key] = $value;
            }
        }

        return $input;
    }

    protected function generateSwitchButton($name, $title, $params = array())
    {
        $input = array(
            'type' => 'switch',
            'label' => $title,
            'name' => $name,
            'is_bool' => true,
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->module->l('Enabled')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->module->l('Disabled')
                ),
            ),
        );

        if (is_array($params) === true) {
            foreach ($params as $key => $value) {
                $input[$key] = $value;
            }
        }

        return $input;
    }

}
