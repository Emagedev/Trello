<?php
/**
 * Emagedev extension for Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Emagedev Trello module to newer versions in the future.
 * If you wish to customize the Emagedev Trello module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (C) Emagedev, LLC (https://www.emagedev.com/)
 * @license    https://opensource.org/licenses/BSD-3-Clause     New BSD License
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
class Emagedev_Trello_Block_Adminhtml_Member_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
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

        $fieldset->addField('admin_user_id', 'select', array(
            'name'     => 'admin_user_id',
            'label'    => Mage::helper('trello')->__('Admin User'),
            'title'    => Mage::helper('trello')->__('Admin User'),
            'values'   => Mage::getSingleton('trello/source_admin_user')->toOptionArray(),
            'required' => true,
        ));

        $fieldset->addField('full_name', 'text', array(
            'name'     => 'full_name',
            'label'    => Mage::helper('trello')->__('Full Name'),
            'title'    => Mage::helper('trello')->__('Full Name'),
            'required' => true,
        ));

        $fieldset->addField('trello_member_id', 'text', array(
            'name'     => 'trello_member_id',
            'label'    => Mage::helper('trello')->__('Trello Member ID'),
            'title'    => Mage::helper('trello')->__('Trello Member ID'),
            'disabled' => true,
            'required' => true,
        ));

        $fieldset->addField('active', 'select', array(
            'name'   => 'active',
            'label'  => Mage::helper('trello')->__('Active'),
            'title'  => Mage::helper('trello')->__('Disabled'),
            'values' => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $data = Mage::registry('current_trello_member');
        if ($data) {
            $form->setValues($data->getData());
        }

        parent::_prepareForm();
    }
}
