<?php
/**
 * J.R. Dunn Jewelers. extension for Magento
 *
 * Long description of this file (if any...)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Emagedev Trello module to newer versions in the future.
 * If you wish to customize the Emagedev Trello module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Emagedev
 * @package    Emagedev_Trello
 * @copyright  Copyright (C) 2018 J.R. Dunn Jewelers.
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Short description of the class
 *
 * Long description of the class (if any...)
 *
 * @category   Emagedev
 * @package    Emagedev_Trello
 * @subpackage Block
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */
class Emagedev_Trello_Block_Adminhtml_Label_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare the form
     *
     * @return Mage_Adminhtml_Block_Widget_Form|void
     */
    protected function _prepareForm()
    {
        //add form
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id', null))),
            'method' => 'post'
        ));
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset(
            'main_field_set',
            array('legend' => $this->__('Main Content'))
        );

        $fieldset->addField('name', 'text', array(
            'name'     => 'name',
            'label'    => Mage::helper('trello')->__('Label Name'),
            'title'    => Mage::helper('trello')->__('Label Name'),
            'required' => true,
        ));

        $fieldset->addField('trello_label_id', 'text', array(
            'name'     => 'trello_label_id',
            'label'    => Mage::helper('trello')->__('Trello Label ID'),
            'title'    => Mage::helper('trello')->__('Trello Label ID'),
            'disabled' => true,
            'required' => true,
        ));

        $fieldset->addField('color', 'text', array(
            'name'     => 'color',
            'label'    => Mage::helper('trello')->__('Trello Label Color'),
            'title'    => Mage::helper('trello')->__('Trello Label Color'),
            'disabled' => true,
            'required' => true,
        ));

        $fieldset->addField('active', 'select', array(
            'name'   => 'active',
            'label'  => Mage::helper('trello')->__('Active'),
            'title'  => Mage::helper('trello')->__('Disabled'),
            'values' => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $data = Mage::registry('current_trello_label');
        if ($data) {
            $form->setValues($data->getData());
        }

        parent::_prepareForm();
    }
}
