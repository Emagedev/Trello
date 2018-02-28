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
     * @return bool|Emagedev_Trello_Model_Card
     */
    public function getOrderCard(Mage_Sales_Model_Order $order, $create = false)
    {
        if (!$order || !$order->getId()) {
            return false;
        }

        /** @var Emagedev_Trello_Model_Card $card */
        $card = Mage::getModel('trello/card')->load($order->getId(), 'order_id');

        if (!$card || !$card->getId()) {
            if (!$create) {
                return false;
            }

            $card = $this->createOrderCard($order);
        }

        Mage::dispatchEvent(
            'trello_order_card_get_after',
            array(
                'order' => $order,
                'card'  => $card
            )
        );

        return $card;
    }

    /**
     * Create new Trello card and fill it with order info,
     * connect this card and order
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return Emagedev_Trello_Model_Card|false
     */
    public function createOrderCard(Mage_Sales_Model_Order $order)
    {
        Mage::dispatchEvent(
            'trello_order_card_create_before',
            array(
                'order' => $order
            )
        );

        // Fix cases, when order only created: use default status
        // (may vary based on order type, may be needs a fix)
        $statusCode = $order->getStatus();

        if (is_null($statusCode) || $statusCode == '') {
            $this->getDataHelper()->log('No status code provided order ' . $order->getId(), Zend_Log::DEBUG);
            $statusCode = 'pending';
        }

        $status = $this->getStatusByCode($statusCode);

        if (!$status) {
            $this->getDataHelper()->log('No status model found for code ' . $statusCode, Zend_Log::DEBUG);
            return false;
        }

        $statusList = $this->getStatusList($status, true);

        if (!$statusList) {
            $statusList = $this->createStatusList($status);
        }

        $dateTime = new DateTime($order->getCreatedAt(), new DateTimeZone('UTC'));

        /** @var Emagedev_Trello_Model_Card $card */
        $card = Mage::getModel('trello/card');

        $card
            ->setName($this->__('Order #%d', $order->getIncrementId()))
            ->setOrderId($order->getId())
            ->setDescription($this->getOrderCardDescription($order))
            ->setListId($statusList->getListId())
            ->setDue($dateTime->format(DateTime::W3C))
            ->setDueComplete(true);

        $card->save();

        Mage::dispatchEvent(
            'trello_order_card_create_after',
            array(
                'order' => $order,
                'card'  => $card
            )
        );

        return $card;
    }

    /**
     * Get trello card connected to order
     *
     * @param Mage_Sales_Model_Order_Status $status
     * @param bool                          $create
     *
     * @return bool|Emagedev_Trello_Model_List
     */
    public function getStatusList(Mage_Sales_Model_Order_Status $status, $create = false)
    {
        if (!$status || !$status->getStatus()) {
            return false;
        }

        /** @var Emagedev_Trello_Model_List $list */
        $list = Mage::getModel('trello/list')->load($status->getStatus(), 'status');

        if (!$list || !$list->getId()) {
            if (!$create) {
                return false;
            }

            $list = $this->createStatusList($status);
        }

        Mage::dispatchEvent(
            'trello_status_list_get_after',
            array(
                'status' => $status,
                'list'   => $list
            )
        );


        return $list;
    }

    /**
     * Create Trello list for status
     *
     * @param Mage_Sales_Model_Order_Status $status
     *
     * @return Emagedev_Trello_Model_List
     */
    public function createStatusList(Mage_Sales_Model_Order_Status $status)
    {
        Mage::dispatchEvent(
            'trello_status_list_create_before',
            array(
                'status' => $status
            )
        );

        if (!($status instanceof Mage_Sales_Model_Order_Status)) {
            /** @var Mage_Sales_Model_Order_Status $status */
            $status = Mage::getModel('sales/order_status')->load($status, 'status');
        }

        /** @var Emagedev_Trello_Model_List $list */
        $list = Mage::getModel('trello/list');

        $list
            ->setName($status->getLabel())
            ->setStatus($status->getStatus())
            ->save();

        $this->getDataHelper()->dropListCache();

        Mage::dispatchEvent(
            'trello_status_list_create_after',
            array(
                'status' => $status,
                'list'   => $list
            )
        );

        return $list;
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
        $card = $this->getOrderCard($order, $create);

        if (!$card) {
            $this->getDataHelper()->log('Failed to locate card for order ' . $order->getId(), Zend_Log::DEBUG);
            return false;
        }

        $statusCode = $order->getStatus();

        if (is_null($statusCode) || $statusCode == '') {
            $this->getDataHelper()->log('No status code provided order ' . $order->getId(), Zend_Log::DEBUG);
            $statusCode = 'pending';
        }

        $status = $this->getStatusByCode($statusCode);

        if (!$status) {
            $this->getDataHelper()->log('No status model found for code ' . $statusCode, Zend_Log::DEBUG);
            return false;
        }

        $list = $this->getStatusList($status, $create);

        if (!$list) {
            $this->getDataHelper()->log('No status list provided order ' . $order->getId(), Zend_Log::DEBUG);
            return false;
        }

        $card
            ->setListId($list->getListId())
            ->save();

        return new Varien_Object($card);
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
        $card = $this->getOrderCard($order);

        $card
            ->setDueComplete(false)
            ->save();

        return $card;
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

    protected function getStatusByCode($code)
    {
        /** @var Mage_Sales_Model_Order_Status $status */
        $status = Mage::getModel('sales/order_status');
        $status->load($code, 'status');

        Mage::dispatchEvent(
            'trello_status_get_by_code',
            array(
                'code'   => $code,
                'status' => $status
            )
        );

        return $status;
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
}