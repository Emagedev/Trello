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
 * @category   Emagedev
 * @package    Emagedev_Trello
 * @subpackage Model
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Emagedev_Trello_Model_Action
 *
 * @method $this setIdMemberCreator(string $idMemberCreator)
 * @method string getIdMemberCreator()
 * @method $this setType(string $type)
 * @method string getType()
 * @method $this setAction(array $actionData)
 * @method array getAction()
 * @method $this setDate(string $date)
 * @method string getDate()
 * @method $this setMemberCreator(array $memberCreatorData)
 * @method array getMemberCreator()
 * @method $this setDisplay(array $displayData)
 * @method array getDisplay()
 */
class Emagedev_Trello_Model_Webhook_Action extends Varien_Object
{
    const TYPE_COMMENT_CARD = 'commentCard';
    const TYPE_UPDATE_COMMENT = 'updateComment';
    const TYPE_UPDATE_CARD = 'updateCard';
    const TYPE_ADD_ATTACHMENT = 'addAttachmentToCard';

    const REGISTRY_PROCESSING_WEBHOOK_ACTION = 'trello_processing_webhook_action';

    public function getPayload()
    {
        return $this->getData('data');
    }

    public function setPayload($data)
    {
        $this->setData('data', $data);
        return $this;
    }

    public function dispatch()
    {
        $this->getDataHelper()->log('Dispatching webhook action ' . $this->getType(), Zend_Log::DEBUG);

        if (
            in_array(
                $this->getType(), array(
                self::TYPE_COMMENT_CARD,
                self::TYPE_UPDATE_COMMENT,
                self::TYPE_UPDATE_CARD,
                self::TYPE_ADD_ATTACHMENT
            ))
        ) {
            $this->getDataHelper()->log('Found action for webhook ' . $this->getType(), Zend_Log::DEBUG);
            Mage::registry(self::REGISTRY_PROCESSING_WEBHOOK_ACTION);
            return $this->{$this->getType()}();
        }

        $this->getDataHelper()->log('No actions for webhook ' . $this->getType(), Zend_Log::DEBUG);
    }

    /**
     * Run action on comment creation (except already registered comments)
     *
     * @return bool
     */
    public function commentCard()
    {
        $action = Mage::getModel('trello/action')->load($this->getId(), 'action_id');

        if ($action && $action->getId()) {
            $this->getDataHelper()->log('Action ' . $this->getId() . ' already registered', Zend_Log::DEBUG);
            return false;
        }

        $comment = $this->getPayload()['text'];
        $comment = trim(strip_tags($comment));

        /** @var Emagedev_Trello_Model_Action $action */
        $action = Mage::getModel('trello/action');
        $action
            ->setText($comment)
            ->parseTextHeader();

        if ($action->getHistoryCommentId()) {
            /** @var Mage_Sales_Model_Order_Status_History $commentModel */
            $commentModel = Mage::getModel('sales/order_status_history')->load($action->getHistoryCommentId());

            if ($commentModel && $commentModel->getId()) {
                $this->getDataHelper()->log('Comment for action ' . $this->getId() . ' already existing (by header id)', Zend_Log::DEBUG);
                return false;
            } else {
                $this->getDataHelper()->log('Cannot load comment #' . $action->getHistoryCommentId() . ' fetched from header of action', Zend_Log::ERR);
            }
        } else {
            $this->getDataHelper()->log('Not found id in header for comment ' . $this->getId(), Zend_Log::DEBUG);
        }

        $member = $this->getActingMember();

        /** @var Emagedev_Trello_Model_Card $card */
        $card = Mage::getModel('trello/card');

        $card->loadFromTrello($this->getPayload()['card']['id']);

        if (!$card || !$card->getId()) {
            $this->getDataHelper()->log('Cannot find card to add comment ' . $this->getPayload()['card']['id'], Zend_Log::ERR);
            return false;
        }

        $order = $card->getOrder();

        if (!$order || !$order->getId()) {
            $this->getDataHelper()->log('Cannot find order to update comment ' . $this->getPayload()['card']['id'], Zend_Log::ERR);
            return false;
        }

        $this->getDataHelper()->log('Add comment to order #' . $order->getIncrementId(), Zend_Log::DEBUG);

        /** @var Mage_Sales_Model_Order_Status_History $commentModel */
        $commentModel = $order->addStatusHistoryComment($action->getText(), $order->getStatus());

        $commentModel
            ->setIsVisibleOnFront(false)
            ->setIsCustomerNotified(false);

        Mage::dispatchEvent(
            'trello_webhook_comment_order_card_before', array(
            'member'         => $member,
            'card'           => $card,
            'order'          => $order,
            'status_history' => $commentModel
        ));

        $commentModel->save();

        Mage::dispatchEvent(
            'trello_webhook_comment_order_card_after', array(
            'member'         => $member,
            'card'           => $card,
            'order'          => $order,
            'status_history' => $commentModel
        ));

        $action
            ->setStatusHistory($commentModel)
            ->setActionId($this->getId())
            ->setOrder($order)
            ->setOriginExternal(true)
            ->disableSync()
            ->save();

        return true;
    }

    public function updateComment()
    {
        /** @var Emagedev_Trello_Model_Member $member */
        $member = Mage::getModel('trello/member');

        $member->loadFromTrello($this->getIdMemberCreator());

        /** @var Emagedev_Trello_Model_Card $card */
        $card = Mage::getModel('trello/card');

        $card->loadFromTrello($this->getPayload()['card']['id']);

        if (!$card || !$card->getId()) {
            $this->getDataHelper()->log('Cannot find card to update comment ' . $this->getPayload()['card']['id'], Zend_Log::ERR);
            return false;
        }

        /** @var Emagedev_Trello_Model_Action $action */
        $action = Mage::getModel('trello/action');

        $action->loadFromTrello($this->getPayload()['action']['id']);

        if (!$action || !$action->getId()) {
            $this->getDataHelper()->log('Cannot find action to update ' . $this->getPayload()['action']['id'], Zend_Log::ERR);
            return false;
        }

        $order = $card->getOrder();

        if (!$order || !$order->getId()) {
            $this->getDataHelper()->log('Cannot find order to update comment ' . $this->getPayload()['action']['id'], Zend_Log::ERR);
            return false;
        }

        $comment = $this->getPayload()['action']['text'];
        $comment = trim(strip_tags($comment));

        /** @var Emagedev_Trello_Model_Action $action */
        $action = Mage::getModel('trello/action');
        $action
            ->setText($comment)
            ->parseTextHeader(false);

        /** @var Mage_Sales_Model_Order_Status_History $commentModel */
        $commentModel = Mage::getModel('sales/order_status_history')->load($action->getHistoryCommentId());
        $commentModel->setComment($action->getText());

        Mage::dispatchEvent(
            'trello_webhook_update_comment_order_card_before', array(
            'member'         => $member,
            'card'           => $card,
            'order'          => $order,
            'status_history' => $commentModel
        ));

        $commentModel->save();

        Mage::dispatchEvent(
            'trello_webhook_update_comment_order_card_after', array(
            'member'         => $member,
            'card'           => $card,
            'order'          => $order,
            'status_history' => $commentModel
        ));

        return true;
    }

    public function addAttachmentToCard()
    {
        $actionModel = Mage::getModel('trello/action')->load($this->getId(), 'action_id');

        if ($actionModel && $actionModel->getId()) {
            $this->getDataHelper()->log('Action ' . $this->getId() . ' already registered', Zend_Log::DEBUG);
            return;
        }

        /** @var Emagedev_Trello_Model_Member $member */
        $member = Mage::getModel('trello/member');

        $member->loadFromTrello($this->getIdMemberCreator());

        /** @var Emagedev_Trello_Model_Card $card */
        $card = Mage::getModel('trello/card');

        $card->loadFromTrello($this->getPayload()['card']['id']);

        if (!$card || !$card->getId()) {
            $this->getDataHelper()->log('Cannot find card to add comment ' . $this->getPayload()['card']['id'], Zend_Log::ERR);
            return false;
        }

        $order = $card->getOrder();

        if (!$order || !$order->getId()) {
            $this->getDataHelper()->log('Cannot find order to update comment ' . $this->getPayload()['card']['id'], Zend_Log::ERR);
            return false;
        }

        $attachmentData = $this->getPayload()['attachment'];

        $comment = 'Attachment:' . PHP_EOL .
            '<a target="_blank" href="' . $attachmentData['url'] . '" title="Attachment">' .
                $attachmentData['name']
            . '</a>';

        $this->getDataHelper()->log('Add attachment to order #' . $order->getIncrementId(), Zend_Log::DEBUG);

        /** @var Mage_Sales_Model_Order_Status_History $commentModel */
        $commentModel = $order->addStatusHistoryComment($comment, $order->getStatus());

        $commentModel
            ->setIsVisibleOnFront(true)
            ->setIsCustomerNotified(false);

        Mage::dispatchEvent(
            'trello_webhook_attachment_order_card_before', array(
            'member'         => $member,
            'card'           => $card,
            'order'          => $order,
            'status_history' => $commentModel
        ));

        $commentModel->save();

        Mage::dispatchEvent(
            'trello_webhook_attachment_order_card_after', array(
            'member'         => $member,
            'card'           => $card,
            'order'          => $order,
            'status_history' => $commentModel
        ));

        /** @var Emagedev_Trello_Model_Action $action */
        $action = Mage::getModel('trello/action');
        $action
            ->setStatusHistory($commentModel)
            ->setActionId($this->getId())
            ->setOrder($order)
            ->disableSync()
            ->save();

        return true;
    }

    /**
     * Card processing
     */

    const CARD_MOVE_ACTION_TRANSLATION_KEY = 'action_move_card_from_list_to_list';
    const CARD_UPDATE_DESCRIPTION_ACTION_TRANSLATION_KEY = 'action_changed_description_of_card';

    /**
     * Update card action - on any action from position move to list update
     *
     * @return bool
     */
    public function updateCard()
    {
        /**
         * Because we can only specify exact action by its translation code
         */
        switch ($this->getDisplay()['translationKey']) {
            case self::CARD_MOVE_ACTION_TRANSLATION_KEY:
                $this->getDataHelper()->log('Status update by translation key ' . $this->getDisplay()['translationKey'], Zend_Log::DEBUG);
                return $this->moveCardUpdateStatus();
            case self::CARD_UPDATE_DESCRIPTION_ACTION_TRANSLATION_KEY:
                $this->getDataHelper()->log('Description update by translation key ' . $this->getDisplay()['translationKey'], Zend_Log::DEBUG);
                return $this->cardDescriptionUpdate();
            default:
                $this->getDataHelper()->log('Cannot process card update by translation key ' . $this->getDisplay()['translationKey'], Zend_Log::DEBUG);
                break;
        }

        return false;
    }

    /**
     * When list was changed
     *
     * @return bool
     */
    protected function moveCardUpdateStatus()
    {
        $member = $this->getActingMember();

        /** @var Emagedev_Trello_Model_Card $card */
        $card = Mage::getModel('trello/card');

        $card->loadFromTrello($this->getPayload()['card']['id']);

        if (!$card || !$card->getId()) {
            $this->getDataHelper()->log('Cannot find card to update ' . $this->getPayload()['card']['id'], Zend_Log::ERR);
            return false;
        }

        $order = $card->getOrder();

        /** @var Emagedev_Trello_Model_List $newList */
        $newList = Mage::getModel('trello/list');

        $newList->loadFromTrello($this->getPayload()['listAfter']['id']);

        if (!$newList || !$newList->getId()) {
            $this->getDataHelper()->log('Cannot find list ' . $this->getPayload()['listAfter']['id'] .
                ' to to move card ' . $this->getPayload()['card']['id'], Zend_Log::ERR);
            return false;
        }

        $this->getDataHelper()->log('Update order #' . $order->getIncrementId() . ' status to ' . $newList->getStatus(), Zend_Log::DEBUG);

        /** @var Mage_Sales_Model_Order_Status_History $commentModel */
        $commentModel = $order->addStatusHistoryComment('', $newList->getStatus());

        $commentModel
            ->setIsVisibleOnFront(true)
            ->setIsCustomerNotified(false);

        Mage::dispatchEvent(
            'trello_webhook_comment_order_card_before', array(
            'member'         => $member,
            'card'           => $card,
            'order'          => $order,
            'status_history' => $commentModel
        ));

        $commentModel->save();

        Mage::dispatchEvent(
            'trello_webhook_comment_order_card_after', array(
            'member'         => $member,
            'card'           => $card,
            'order'          => $order,
            'status_history' => $commentModel
        ));

        $order->save();

        return true;
    }

    /**
     * Run event when card description updated
     *
     * @return bool
     */
    protected function cardDescriptionUpdate()
    {
        $member = $this->getActingMember();

        /** @var Emagedev_Trello_Model_Card $card */
        $card = Mage::getModel('trello/card');

        $card->loadFromTrello($this->getPayload()['card']['id']);

        if (!$card || !$card->getId()) {
            $this->getDataHelper()->log('Cannot find card to update ' . $this->getPayload()['card']['id'], Zend_Log::ERR);
            return false;
        }

        $order = $card->getOrder();

        Mage::dispatchEvent(
            'trello_webhook_edit_order_card_description', array(
            'action'         => $this,
            'member'         => $member,
            'card'           => $card,
            'order'          => $order
        ));

        return true;
    }

    /**
     * Map camelCased JSON data to underscored Varien_Object data
     *
     * @param $data
     *
     * @return $this
     */
    public function setJsonData($data)
    {
        foreach ($data as $key => $datum) {
            $this->setData($this->_underscore($key), $datum);
        }

        return $this;
    }

    /**
     * Get member that processing this action
     *
     * @return Emagedev_Trello_Model_Member
     */
    public function getActingMember()
    {
        /** @var Emagedev_Trello_Model_Member $member */
        $member = Mage::getModel('trello/member');
        $member->loadFromTrello($this->getIdMemberCreator());

        return $member;
    }

    /**
     * @return Emagedev_Trello_Helper_Data
     */
    protected function getDataHelper()
    {
        return Mage::helper('trello');
    }
}
