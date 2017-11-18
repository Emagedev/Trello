<?php


class Omedrec_Trello_Model_Observer
{
    /**
     * Add card to Trello for new order
     *
     * @param Varien_Event_Observer $observer
     */
    public function addOrderCard(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $order = $event->getOrder();

        if (!$order || !$order->getId()) {
            return;
        }

        Mage::helper('trello/order')->createOrderCard($order);
    }

    /**
     * Update order card on Trello when order updated (saved)
     *
     * @param Varien_Event_Observer $observer
     */
    public function updateOrderCard(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $order = $event->getOrder();

        if (!$order || !$order->getId()) {
            return;
        }

        Mage::helper('trello/order')->updateOrderStatusList($order, true);
    }

    public function markOrArchiveOutdatedOrders()
    {
        /** @var Mage_Sales_Model_Resource_Order_Collection $orderCollection */
        $orderCollection = Mage::getModel('sales/order')->getCollection();

        $orderCollection
            ->getSelect()
            ->joinInner(
                array('order_card' => Mage::getSingleton('core/resource')->getTableName('trello/order')),
                'order_card.order_id = main_table.entity_id'
            );

        $nowDate = new DateTime('now');

        /** @var Mage_Sales_Model_Order $order */
        foreach ($orderCollection as $order) {
            $orderDate = new DateTime($order->getUpdatedAt());

            $days = $orderDate->diff($nowDate)->days;

            if ($days > 3) {
                Mage::helper('trello/order')->markOrderOutdated($order);
            }
        }
    }
}