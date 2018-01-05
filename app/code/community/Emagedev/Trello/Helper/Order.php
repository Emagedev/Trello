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
 * the Omedrec Welcome module to newer versions in the future.
 * If you wish to customize the Omedrec Welcome module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (C) Emagedev, LLC (https://www.emagedev.com/)
 * @license    https://opensource.org/licenses/BSD-3-Clause     New BSD License
 */

/**
 * @category   Emagedev
 * @package    Emagedev_Trello
 * @subpackage Helper
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Emagedev_Trello_Helper_Order
 *
 * Control order cards
 */
class Emagedev_Trello_Helper_Order extends Mage_Core_Helper_Abstract
{
    /**
     * Get trello card connected to order
     *
     * @param Mage_Sales_Model_Order $order
     * @param bool                   $create
     *
     * @return bool|Emagedev_Trello_Model_Order
     */
    public function getOrderCard(Mage_Sales_Model_Order $order, $create = false)
    {
        /** @var Emagedev_Trello_Model_Order $orderCardLink */
        $orderCardLink = Mage::getModel('trello/order')->load($order->getId(), 'order_id');

        if (!$orderCardLink || !$orderCardLink->getId()) {
            if (!$create) {
                return false;
            }

            $orderCardLink = $this->createOrderCard($order);
        }

        return $orderCardLink;
    }

    /**
     * Create new Trello card and fill it with order info,
     * connect this card and order
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return Emagedev_Trello_Model_Order
     */
    public function createOrderCard(Mage_Sales_Model_Order $order)
    {
        $status = $order->getStatus();

        // Fix cases, when order only created: use default status
        // (may vary based on order type, may be needs a fix)
        if (is_null($status) || $status == '') {
            $status = 'pending';
        }

        $statusList = $this->getDataHelper()->getStatusListId($status);

        if (!$statusList) {
            $statusList = $this->createStatusList($status);
        }

        $dateTime = new DateTime($order->getCreatedAt(), new DateTimeZone('UTC'));

        $trelloCard = $this->getApi()->createCard(
            array(
                'idList'      => $statusList->getListId(),
                'name'        => $this->__('Order #%d', $order->getIncrementId()),
                'desc'        => $this->getOrderCardDescription($order),
                'due'         => $dateTime->format(DateTime::W3C),
                'dueComplete' => 'true'
            )
        );

        $trelloCard = new Varien_Object($trelloCard);

        /** @var Emagedev_Trello_Model_Order $orderCardLink */
        $orderCardLink = Mage::getModel('trello/order');

        $orderCardLink
            ->setOrderId($order->getId())
            ->setCardId($trelloCard->getId());

        $orderCardLink
            ->save();

        return $orderCardLink;
    }

    /**
     * Update order card and set new params
     *
     * @param Mage_Sales_Model_Order $order
     * @param array                  $params
     *
     * @return Varien_Object
     */
    public function updateOrderCard(Mage_Sales_Model_Order $order, $params)
    {
        $card = $this->getOrderCard($order);

        if (!$card) {
            return false;
        }

        $trelloCard = $this->getApi()
            ->updateCard(
                $card->getCardId(),
                $params
            );

        return new Varien_Object($trelloCard);
    }

    /**
     * Mark order card as archived (update with archived param)
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return Varien_Object
     */
    public function archiveOrder(Mage_Sales_Model_Order $order)
    {
        $card = $this->getOrderCard($order);

        $trelloCard = $this->getApi()
            ->archiveCard(
                $card->getCardId()
            );

        $card
            ->setArchived('1')
            ->save();

        return new Varien_Object($trelloCard);
    }

    /**
     * Get order card and put it in some list
     *
     * @param Mage_Sales_Model_Order $order
     * @param bool                   $create
     *
     * @return bool|Varien_Object
     */
    public function updateOrderStatusList($order, $create = false)
    {
        $orderCard = $this->getOrderCard($order, $create);

        if (!$orderCard) {
            return false;
        }

        $status = $order->getStatus();

        if (is_null($status) || $status == '') {
            $status = 'pending';
        }

        $statusList = $this->getDataHelper()->getStatusListId($status);

        if (!$statusList) {
            $statusList = $this->createStatusList($status);
        }

        $trelloCard = $this->getApi()->updateCard(
            $orderCard->getCardId(),
            array(
                'idList' => $statusList->getListId(),
            )
        );

        return new Varien_Object($trelloCard);
    }

    /**
     * Create Trello list for status
     *
     * @param $status
     *
     * @return Emagedev_Trello_Model_List
     */
    public function createStatusList($status)
    {
        if (!($status instanceof Mage_Sales_Model_Order_Status)) {
            /** @var Mage_Sales_Model_Order_Status $status */
            $status = Mage::getModel('sales/order_status')->load($status, 'status');
        }

        $trelloList = $this->getApi()->createList(
            array(
                'name'    => $status->getStoreLabel(Mage::app()->getStore()),
                'idBoard' => $this->getDataHelper()->getBoardId()
            )
        );

        $trelloList = new Varien_Object($trelloList);

        /** @var Emagedev_Trello_Model_List $statusListLink */
        $statusListLink = Mage::getModel('trello/list');

        $statusListLink
            ->setStatus($status->getStatus())
            ->setListId($trelloList->getId())
            ->save();

        $this->getDataHelper()->dropListCache();

        return $statusListLink;
    }

    /**
     * Set uncompeded due date on order card in Trello
     * so it marks with red-labeled due tag
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return Varien_Object
     */
    public function markOrderOutdated($order)
    {
        return $this->updateOrderCard($order, array('dueComplete' => false));
    }

    /**
     * Create description for order card
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return string
     */
    public function getOrderCardDescription(Mage_Sales_Model_Order $order)
    {
        $name = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();

        $cardDescriptionTemplate = Mage::getStoreConfig('trello_api/order_status/card_template');

        $variables = new Varien_Object(array(
            'customer' => $name,
            'grand_total' => $order->getGrandTotal(),
            'created_at' => $order->getCreatedAt(),
        ));

        Mage::dispatchEvent(
            'trello_order_card_generate_description',
            array(
                'order'            => $order,
                'variables_object' => $variables,
                'template'         => $cardDescriptionTemplate
            )
        );

        $formatter = new Varien_Filter_Template();
        $formatter->setVariables($variables->getData());

        return $formatter->filter($cardDescriptionTemplate);
    }

    /**
     * Get general helper
     *
     * @return Emagedev_Trello_Helper_Data
     */
    protected function getDataHelper()
    {
        return Mage::helper('trello');
    }

    /**
     * Get API model
     *
     * @return Emagedev_Trello_Model_Api
     */
    protected function getApi()
    {
        return Mage::getSingleton('trello/api');
    }
}