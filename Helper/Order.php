<?php


class Omedrec_Trello_Helper_Order extends Mage_Core_Helper_Abstract
{
    public function createOrderCard(Mage_Sales_Model_Order $order)
    {
        $statusList = $this->getDataHelper()->getStatusListId($order->getStatus());

        if (!$statusList) {
            $statusList = $this->createStatusList($order->getStatus());
        }

        $dateTime = new DateTime($order->getCreatedAt(), new DateTimeZone('UTC'));

        /** @var Omedrec_Trello_Model_Api $api */
        $api = Mage::getModel('trello/api');
        $trelloCard = $api->createCard(
            array(
                'idList'      => $statusList->getListId(),
                'name'        => $this->__('Order #%d', $order->getIncrementId()),
                'desc'        => $this->getOrderCardDescription($order),
                'due'         => $dateTime->format(DateTime::W3C),
                'dueComplete' => 'true'
            )
        );

        $trelloCard = new Varien_Object($trelloCard);

        /** @var Omedrec_Trello_Model_Order $orderCardLink */
        $orderCardLink = Mage::getModel('trello/order');

        $orderCardLink
            ->setOrderId($order->getId())
            ->setCardId($trelloCard->getId());

        $orderCardLink
            ->save();

        return $orderCardLink;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param bool                   $create
     *
     * @return bool|Omedrec_Trello_Model_Order
     */
    public function getOrderCard(Mage_Sales_Model_Order $order, $create = false)
    {
        /** @var Omedrec_Trello_Model_Order $orderCardLink */
        $orderCardLink = Mage::getModel('trello/order')->load($order->getId(), 'order_id');

        if (!$orderCardLink || !$orderCardLink->getId()) {
            if (!$create) {
                return false;
            }

            $orderCardLink = $this->createOrderCard($order);
        }

        return $orderCardLink;
    }

    public function createStatusList($status)
    {
        if (!($status instanceof Mage_Sales_Model_Order_Status)) {
            /** @var Mage_Sales_Model_Order_Status $status */
            $status = Mage::getModel('sales/order_status')->load($status, 'status');
        }

        /** @var Omedrec_Trello_Model_Api $api */
        $api = Mage::getModel('trello/api');

        $trelloList = $api->createList(array(
            'name' => $status->getStoreLabel(Mage::app()->getStore()),
            'idBoard' => '5a0f02b9b88a403a70c53c59' // @todo: from config!
        ));

        var_dump($trelloList);

        $trelloList = new Varien_Object($trelloList);

        /** @var Omedrec_Trello_Model_List $statusListLink */
        $statusListLink = Mage::getModel('trello/list');

        $statusListLink
            ->setStatus($status->getStatus())
            ->setListId($trelloList->getId())
            ->save();

        return $statusListLink;
    }

    protected function getOrderCardDescription(Mage_Sales_Model_Order $order)
    {
        $name = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();

        return $this->__('Created by %s', $name);
    }

    /**
     * @return Omedrec_Trello_Helper_Data
     */
    protected function getDataHelper()
    {
        return Mage::helper('trello');
    }
}