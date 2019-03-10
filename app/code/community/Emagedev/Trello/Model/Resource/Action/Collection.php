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
class Emagedev_Trello_Model_Resource_Action_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Init model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('trello/action');
    }

    public function getActionByTrelloId($actionId)
    {
        /** @var Emagedev_Trello_Model_Action $action */
        foreach ($this as $action) {
            if ($action->getActionId() == $actionId) {
                return $action;
            }
        }

        return false;
    }

    public function getActionByHistoryCommentId($historyCommentId)
    {
        /** @var Emagedev_Trello_Model_Action $action */
        foreach ($this as $action) {
            if ($action->getHistoryCommentId() == $historyCommentId) {
                return $action;
            }
        }

        return false;
    }

    public function fetchCardActions($card)
    {
        /** @var Emagedev_Trello_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('trello');

        $boardId = $dataHelper->getBoardId();

        $cardResponse = $this->getAdapter()
            ->run(
                array('boards' => $boardId, 'members'),
                Zend_Http_Client::GET
            );

        $members = $this->getAdapter()->decodeResponse($cardResponse);
        $memberIds = array();

        foreach ($members as $member) {
            $memberIds[] = $member['id'];
        }

        /** @var Emagedev_Trello_Model_Resource_Member_Collection $existingCollection */
        $existingCollection = Mage::getModel('trello/member')->getCollection();
        $existingCollection->addFieldToFilter('trello_member_id', array('in' => $memberIds));

        foreach ($members as $member) {
            $existingMember = $existingCollection->getItemByTrelloId($member['id']);

            if ($existingMember && $existingMember->getId()) {
                $this->addItem($existingMember);
            } else {
                /** @var Emagedev_Trello_Model_Member $newMember */
                $newMember = Mage::getModel('trello/member');

                $newMember
                    ->setTrelloMemberId($member['id'])
                    ->setFullName($member['fullName']);

                $this->addItem($newMember);
            }
        }

        $this->_setIsLoaded(true);

        return $this;
    }
}
