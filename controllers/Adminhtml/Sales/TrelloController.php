<?php
/**
 * Emagedev extension for Magento
 *
 * NOTICE OF LICENSE
 *
 * Copyright (C) Effdocs, LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 *
 * This source file is proprietary and confidential
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Omedrec Startpage module to newer versions in the future.
 *
 * @copyright  Copyright (C) Effdocs, LLC
 * @license    http://www.binpress.com/license/view/l/45d152a594cd48488fda1a62931432e7
 */

/**
 * @category   Omedrec
 * @package    Omedrec_Trello
 * @subpackage Controller
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Omedrec_Trello_Helper_Data
 *
 * Mass action controller methods
 */
class Omedrec_Trello_Adminhtml_Sales_TrelloController extends Mage_Adminhtml_Controller_Action
{
    public function massUpdateAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids', array());

        /** @var Omedrec_Trello_Helper_Order $helper */
        $helper = Mage::helper('trello/order');
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);

            $helper->updateOrderStatusList($order, true);
        }

        $this->_getSession()->addSuccess($this->__('%s order(s) updated in Trello board.', count($orderIds)));

        $this->_redirect('*/sales_order/');
    }
}
