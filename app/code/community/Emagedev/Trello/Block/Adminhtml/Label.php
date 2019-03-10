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
class Emagedev_Trello_Block_Adminhtml_Label extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Init grid container
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_label';
        $this->_blockGroup = 'trello';
        $this->_headerText = $this->__('Trello Label');

        /** @var Emagedev_Trello_Helper_Data $helper */
        $helper = Mage::helper('trello');

        $this->_addButton('fetch', array(
            'label'     => $helper->isConnectionSet() ? $this->__('Fetch Labels From Board') : $this->__('Please Set Up Connection'),
            'onclick'   => 'setLocation(\'' . $this->getFetchUrl() .'\')',
            'class'     => 'add',
            'disabled'  => !$helper->isConnectionSet()
        ));

        parent::__construct();

        $this->removeButton('add');
    }

    protected function getFetchUrl()
    {
        return $this->getUrl('*/*/fetchLabels');
    }
}
