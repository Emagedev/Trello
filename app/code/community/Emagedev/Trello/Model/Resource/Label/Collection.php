<?php
/**
 * J.R. Dunn Jewelers. extension for Magento
 *
 * Long description of this file (if any...)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Emagedev Trello module to newer versions in the future.
 * If you wish to customize the Emagedev Trello module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Emagedev
 * @package    Emagedev_Trello
 * @copyright  Copyright (C) 2018 J.R. Dunn Jewelers.
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Short description of the class
 *
 * Long description of the class (if any...)
 *
 * @category   Emagedev
 * @package    Emagedev_Trello
 * @subpackage Model
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */
class Emagedev_Trello_Model_Resource_Label_Collection extends Emagedev_Trello_Model_Trello_Entity_Resource_Collection_Abstract
{
    /**
     * Init model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('trello/label');
    }

    public function fetchTrelloLabels()
    {
        if ($this->isLoaded()) {
            Mage::throwException('Cannot fetch items from Trello: items already loaded.');
        }

        /** @var Emagedev_Trello_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('trello');

        $boardId = $dataHelper->getBoardId();

        $membersResponse = $this->getAdapter()
            ->run(
                array('boards' => $boardId, 'labels'),
                Zend_Http_Client::GET
            );

        $labels = $this->getAdapter()->decodeResponse($membersResponse);

        foreach ($labels as $label) {
            /** @var Emagedev_Trello_Model_Label $newLabel */
            $newLabel = Mage::getModel('trello/label');

            $newLabel
                ->setTrelloLabelId($label['id'])
                ->setName($label['name'])
                ->setColor($label['color']);

            $this->addItem($newLabel);
        }

        $this->_setIsLoaded(true);

        return $this;
    }

    public function getItemByTrelloId($trelloId)
    {
        /** @var Emagedev_Trello_Model_Label $label */
        foreach ($this as $label) {
            if ($label->getTrelloLabelId() == $trelloId) {
                return $label;
            }
        }

        return false;
    }

    public function getItemsTrelloLabelIds()
    {
        $itemIds = array();

        /** @var Emagedev_Trello_Model_Label $member */
        foreach ($this as $member) {
            if (!$member->getTrelloLabelId()) {
                continue;
            }

            $itemIds[] = $member->getTrelloLabelId();
        }

        return $itemIds;
    }
}
