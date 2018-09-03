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
 * Class Emagedev_Trello_Helper_Data
 *
 * Usable methods
 */
class Emagedev_Trello_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * Array of lists sorted by status code
     *
     * @var [status => Emagedev_Trello_Model_List]
     */
    protected $statusLists;

    /**
     * Get link to trello list that representing given status
     *
     * @deprecated
     *
     * @param $statusCode
     *
     * @return bool|Emagedev_Trello_Model_List
     */
    public function getStatusListId($statusCode)
    {
        if ($statusCode instanceof Mage_Sales_Model_Order_Status) {
            $statusCode = $statusCode->getStatus();
        }

        if (array_key_exists($statusCode, $this->getStatusLists())) {
            $list = $this->getStatusLists()[$statusCode];
        } else {
            return false;
        }

        return $list instanceof Emagedev_Trello_Model_List ? $list : false;
    }

    public function isConnectionSet()
    {
        return $this->getBoardId() && $this->getApiKey() && $this->getApiToken();
    }

    public function isWebhookActive()
    {
        return $this->getWebhookStatus() && $this->getBoardId() && $this->getWebhookId();
    }

    public function isEnabled()
    {
        return Mage::getStoreConfig('trello_api/general/enabled');
    }

    public function getApiKey()
    {
        return Mage::getStoreConfig('trello_api/general/key');
    }

    public function getApiToken()
    {
        return Mage::getStoreConfig('trello_api/general/token');
    }

    /**
     * Get count of days after which cards will be marked as outdated or archived
     *
     * @return int
     */
    public function getDaysBeforeOutdated()
    {
        return (int)Mage::getStoreConfig('trello_api/order_status/outdated_days');
    }

    /**
     * Get status board id
     *
     * @return string
     */
    public function getBoardId()
    {
        return Mage::getStoreConfig('trello_api/order_status/board_id');
    }

    /**
     * Get status board id
     *
     * @return string
     */
    public function getWebhookId()
    {
        return Mage::getStoreConfig('trello_api/webhook/id');
    }

    /**
     * Get status board id
     *
     * @return bool
     */
    public function getWebhookStatus()
    {
        return (bool)Mage::getStoreConfigFlag('trello_api/webhook/status');
    }

    public function updateWebhookCheck()
    {
        $date = date('l, j \o\f F, Y');

        Mage::getConfig()->saveConfig('trello_api/webhook/check', $date, 'default', 0);
        Mage::getConfig()->saveConfig('trello_api/webhook/status', '1','default', 0);

        return true;
    }

    public function dropWebhookCheck()
    {
        Mage::getConfig()->saveConfig('trello_api/webhook/check', $this->__('Never'), 'default', 0);
        Mage::getConfig()->saveConfig('trello_api/webhook/status', '0','default', 0);

        return false;
    }

    /**
     * WARNING: This method will truncate all tables
     */
    public function dropTrelloIntegration()
    {
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');

        /** @var Varien_Db_Adapter_Interface $connection */
        $connection = $resource->getConnection('core_write');

        $connection->truncateTable($resource->getTableName('trello/action'));
        $connection->truncateTable($resource->getTableName('trello/card'));
        $connection->truncateTable($resource->getTableName('trello/member'));
        $connection->truncateTable($resource->getTableName('trello/list'));

        Mage::getConfig()->saveConfig('trello_api/webhook/id', '', 'default', 0);
        Mage::getConfig()->saveConfig('trello_api/order_status/board_id', '', 'default', 0);
        Mage::getConfig()->saveConfig('trello_api/general/key', '', 'default', 0);
        Mage::getConfig()->saveConfig('trello_api/general/token', '', 'default', 0);

        $this->dropWebhookCheck();

        Mage::app()->getCacheInstance()->cleanType('config');
    }

    public function escapeTrelloMarkdown($text)
    {
        $text = str_replace('*', '\*', $text);
        $text = str_replace('-', '\-', $text);
        $text = str_replace('[', '\[', $text);
        $text = str_replace(']', '\]', $text);

        return $text;
    }

    /**
     * Get all board lists related to status
     *
     * @return array
     */
    public function getStatusLists()
    {
        if (is_null($this->statusLists)) {
            /** @var Emagedev_Trello_Model_Resource_List_Collection $lists */
            $statusLists = Mage::getModel('trello/list')->getCollection();

            $this->statusLists = array();

            /** @var Emagedev_Trello_Model_List $list */
            foreach ($statusLists as $list) {
                $this->statusLists[$list->getStatus()] = $list;
            }
        }

        return $this->statusLists;
    }

    public function updateMembers()
    {
        /** @var Emagedev_Trello_Model_Resource_Member_Collection $existingCollection */
        $existingCollection = Mage::getModel('trello/member')->getCollection();
        $existingCollection->load();

        /** @var Emagedev_Trello_Model_Resource_Member_Collection $newCollection */
        $newCollection = Mage::getModel('trello/member')->getCollection();
        $newCollection->fetchTrelloMembers();

        $oldIds = $existingCollection->getItemsTrelloMemberIds();
        $newIds = $newCollection->getItemsTrelloMemberIds();

        $addedMemberIds = array_diff($newIds, $oldIds);
        $removedMemberIds = array_diff($oldIds, $newIds);

        foreach ($addedMemberIds as $addedMemberId) {
            /** @var Emagedev_Trello_Model_Member $addedMember */
            $addedMember = $newCollection->getItemByTrelloId($addedMemberId);
            if ($existingCollection->getItemByTrelloId($addedMember->getTrelloMemberId())) {
                continue;
            }

            $addedMember->save();
        }

        foreach ($removedMemberIds as $removedMemberId) {
            /** @var Emagedev_Trello_Model_Member $removedMember */
            $removedMember = $existingCollection->getItemByTrelloId($removedMemberId);

            $removedMember
                ->setActive(false)
                ->save();
        }
    }

    public function updateLabels()
    {
        /** @var Emagedev_Trello_Model_Resource_Label_Collection $existingCollection */
        $existingCollection = Mage::getModel('trello/label')->getCollection();
        $existingCollection->load();

        /** @var Emagedev_Trello_Model_Resource_Label_Collection $newCollection */
        $newCollection = Mage::getModel('trello/label')->getCollection();
        $newCollection->fetchTrelloLabels();

        $oldIds = $existingCollection->getItemsTrelloLabelIds();
        $newIds = $newCollection->getItemsTrelloLabelIds();

        $addedLabelIds = array_diff($newIds, $oldIds);
        $removedLabelIds = array_diff($oldIds, $newIds);

        foreach ($addedLabelIds as $addedLabelId) {
            /** @var Emagedev_Trello_Model_Label $addedLabel */
            $addedLabel = $newCollection->getItemByTrelloId($addedLabelId);
            if ($existingCollection->getItemByTrelloId($addedLabel->getTrelloLabelId())) {
                continue;
            }

            $addedLabel
                ->disableSync()
                ->save();
        }

        /** @var Emagedev_Trello_Model_Label $existedLabel */
        foreach ($existingCollection as $existedLabel) {
            if (in_array($existedLabel->getTrelloLabelId(), $removedLabelIds)) {
                $existedLabel
                    ->disableSync()
                    ->setActive(false)
                    ->save();
            } else {
                $fetchedLabel = $newCollection->getItemByTrelloId($existedLabel->getTrelloLabelId());

                if ($fetchedLabel) {
                    $hasUpdates = $existedLabel->getName() != $fetchedLabel->getName()
                        || $existedLabel->getColor() != $fetchedLabel->getColor();

                    if (!$hasUpdates) {
                        continue;
                    }

                    $existedLabel
                        ->disableSync()
                        ->setName($fetchedLabel->getName())
                        ->setColor($fetchedLabel->getColor())
                        ->save();
                }

            }
        }
    }

    /**
     * Drop cached status lists
     *
     * @return $this
     */
    public function dropListCache()
    {
        $this->statusLists = null;
        return $this;
    }

    public function log($message, $severity = Zend_Log::NOTICE)
    {
        if ($severity >= Zend_Log::NOTICE && !Mage::getStoreConfigFlag('trello_api/developer/debug_log')) {
            return $this;
        }

        Mage::log($message, $severity, 'trello.log');

        return $this;
    }
}