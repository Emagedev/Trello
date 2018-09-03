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
 * @subpackage Model
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Emagedev_Trello_Model_Action
 *
 * @method $this setHistoryCommentId(int $commentId)
 * @method int getHistoryCommentId()
 * @method $this setActionId(string $actionId)
 * @method string getActionId()
 * @method $this setOrderId(int $orderId)
 * @method int getOrderId()
 * @method $this setText(string $text)
 * @method string getText()
 * @method $this setOriginExternal(bool $external)
 * @method bool getOriginExternal()
 */
class Emagedev_Trello_Model_Action extends Emagedev_Trello_Model_Trello_Entity_Abstract
{
    protected $_eventPrefix = 'trello_action';

    /**
     * @var Mage_Sales_Model_Order
     */
    protected $order;

    /**
     * @var Mage_Sales_Model_Order_Status_History
     */
    protected $statusHistory;

    /**
     * @var array
     */
    protected $apiDataMap = array(
        'text' => 'text'
    );

    /**
     * @var Emagedev_Trello_Model_Card
     */
    protected $card;

    /**
     * Init the resource
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('trello/action');
    }

    public function loadFromTrello($trelloId)
    {
        return $this->load($trelloId, 'action_id');
    }

    /**
     * @param Mage_Sales_Model_Order_Status_History $statusHistory
     *
     * @return $this
     */
    public function importFromStatusHistory(Mage_Sales_Model_Order_Status_History $statusHistory)
    {
        $this->setOrder($statusHistory->getOrder());

        $this->setStatusHistory($statusHistory);
        $this->setOrderId($statusHistory->getOrder()->getId());
        $this->setText($statusHistory->getComment());

        Mage::dispatchEvent(
            'trello_comment_import_from_status_history', array(
            'action'         => $this,
            'status_history' => $statusHistory
        ));

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;
        $this->setOrderId($order->getId());

        return $this;
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (is_null($this->order)) {
            $orderId = $this->getOrderId();

            if ($orderId) {
                $order = Mage::getModel('sales/order')->load($orderId);

                if ($order && $order->getId())
                    $this->order = $order;
            }
        }

        return $this->order;
    }

    /**
     * @param Mage_Sales_Model_Order_Status_History $statusHistory
     *
     * @return $this
     */
    public function setStatusHistory($statusHistory)
    {
        $this->statusHistory = $statusHistory;
        $this->setHistoryCommentId($statusHistory->getId());

        return $this;
    }

    /**
     * @return Mage_Sales_Model_Order_Status_History
     */
    public function getStatusHistory()
    {
        if (is_null($this->statusHistory)) {
            $commentId = $this->getHistoryCommentId();

            if ($commentId) {
                $statusHistory = Mage::getModel('sales/order_status_history')->load($commentId);

                if ($statusHistory && $statusHistory->getId())
                    $this->statusHistory = $statusHistory;
            }
        }

        return $this->statusHistory;
    }

    /**
     * @param Emagedev_Trello_Model_Card $card
     *
     * @return $this
     */
    public function setCard($card)
    {
        $this->card = $card;
        return $this;
    }

    /**
     * @return Emagedev_Trello_Model_Card
     */
    public function getCard()
    {
        if (is_null($this->card)) {
            if (!is_null($this->getOrder())) {
                /** @var Emagedev_Trello_Helper_Order $helper */
                $helper = Mage::helper('trello/order');

                /** @var Emagedev_Trello_Model_Card $card */
                $card = $helper->getOrderCard($this->getOrder());

                if ($card) {
                    $this->setCard($card);
                }
            }
        }

        return $this->card;
    }

    /**
     * Save data to Trello
     *
     * @todo: Make universal
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if ($this->doSync) {
            $this->prepareExportText();

            if ($this->getActionId()) {
                $this->sync();
            } else {
                $this->export();
            }
        }

        return $this;
    }

    /**
     * Often webhook creates action faster than model because model waits for cURL and
     * Trello seems to make request for webhook before giving a response for action
     * creation, so we have a race condition here creating duplicating comments.
     * To prevent race condition, we adding comment id to text, that should be parsed
     * in webhook to prevent duplicates.
     *
     * @return $this
     */
    public function prepareExportText()
    {
        if ($this->getOriginExternal()) {
            return $this;
        }

        $text = $this->getText();
        $headerObject = new Varien_Object(array(
            'text' => '\#' . $this->getHistoryCommentId()
        ));

        Mage::dispatchEvent('trello_comment_add_header', array(
            'action' => $this,
            'header' => $headerObject
        ));

        $text = $headerObject->getText() . PHP_EOL . $text;

        $this->setText($text);

        return $this;
    }

    /**
     * Parse added header to prevent race condition
     *
     * @param bool $trustHeader
     *
     * @return $this
     */
    public function parseTextHeader($trustHeader = true)
    {
        if ($this->getOriginExternal()) {
            return $this;
        }

        $text = $this->getText();
        $lines = explode(PHP_EOL, $text);
        $header = array_shift($lines);
        $matches = array();

        $hasId = preg_match('/#(\d{1,9})(\s|$)/si', $header, $matches);

        if (!$hasId) {
            return $this;
        }

        Mage::dispatchEvent('trello_comment_parse_header', array(
            'action' => $this,
            'header' => $header
        ));

        $this
            ->setText(implode(PHP_EOL, $lines));

        if ($trustHeader || !$this->getHistoryCommentId()) {
            $this->setHistoryCommentId($matches[1]);
        }

        return $this;
    }

    /**
     * Create trello card
     *
     * @see https://developers.trello.com/v1.0/reference#cards-2
     *
     * @return array|string
     */
    public function export()
    {
        $cardResponse = $this->getAdapter()
            ->run(
                array('cards' => $this->getCard()->getCardId(), 'actions' => 'comments'),
                Zend_Http_Client::POST,
                $this->prepareParams()
            );

        $response = $this->getAdapter()->decodeResponse($cardResponse);
        $this->processResponse($response);

        return $this;
    }

    /**
     * Update some card by id, set new parameters
     *
     * @return array|string
     */
    public function sync()
    {
        $cardResponse = $this->getAdapter()
            ->run(
                array('cards' => $this->getCard()->getCardId(), 'actions' => $this->getActionId(), 'comments'),
                Zend_Http_Client::PUT,
                $this->prepareParams()
            );

        $response = $this->getAdapter()->decodeResponse($cardResponse);
        $this->processResponse($response);

        return $this;
    }

    /**
     * Get Trello card params
     *
     * @param $cardId
     *
     * @return array|string
     */
    public function get($cardId)
    {
        $cardResponse = $this->getAdapter()
            ->run(
                array('cards' => $cardId, 'actions' => 'comments'),
                Zend_Http_Client::GET
            );

        return $this->getAdapter()->decodeResponse($cardResponse);
    }

    protected function processResponse($response)
    {
        $this->setActionId($response['id']);

        return $this;
    }

    /**
     * Delete some Trello card permanently
     *
     * @return $this
     */
    public function delete()
    {
        $this->getAdapter()
            ->run(
                array('cards' => $this->getCard()->getCardId(), 'actions' => $this->getActionId(), 'comments'),
                Zend_Http_Client::PUT,
                array(
                    'text' => $this->getText()
                )
            );

        return parent::delete();
    }
}
