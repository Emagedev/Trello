<?php


class Omedrec_Trello_Model_Observer
{
    /**
     * Add card to Trello for new order
     *
     * @param Varien_Event_Observer $observer
     */
    public function addOrderCart(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $order = $event->getOrder();

        if (!$order || !$order->getId()) {
            return;
        }

        Mage::helper('trello/order')->createOrderCard($order);
    }
}