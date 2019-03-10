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
 * Class Emagedev_Trello_Model_List
 *
 * Order - Trello Card connection
 *
 * @method $this setOrderId(int $orderId)
 * @method int getOrderId()
 * @method $this setCardId(string $cardId)
 * @method string getCardId()
 * @method $this setName(string $name)
 * @method string getName()
 * @method $this setDescription(string $name)
 * @method string getDescription()
 * @method $this setListId(string $listId)
 * @method string getListId()
 * @method $this setLabelIds(string $labelIds)
 * @method string getLabelIds()
 * @method $this setArchived(bool $archived)
 * @method bool getArchived()
 * @method $this setDue(string $due)
 * @method bool getDue()
 * @method $this setDueComplete(bool $complete)
 * @method bool getDueComplete()
 */
class Emagedev_Trello_Model_Card extends Emagedev_Trello_Model_Trello_Entity_Abstract
{
    protected $_eventPrefix = 'trello_card';

    /**
     * @var Mage_Sales_Model_Order
     */
    protected $order;

    /**
     * @var array
     */
    protected $labels;

    /**
     * @var array
     */
    protected $apiDataMap = array(
        'name'        => 'name',
        'desc'        => 'description',
        'due'         => 'due',
        'dueComplete' => 'due_complete',
        'idLabels'    => 'label_ids',
        'idList'      => 'list_id',
        'archived'    => 'archived'
    );

    /**
     * Init the resource
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('trello/card');
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

        $this->prepareLabelIds();

        if ($this->doSync) {
            if ($this->getCardId()) {
                $this->sync();
            } else {
                $this->export();
            }
        }

        return $this;
    }

    /**
     * @var Emagedev_Trello_Model_Api_Adapter
     */
    protected $adapter;

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
                array('cards'),
                Zend_Http_Client::POST,
                $this->prepareParams()
            );

        $response = $this->getAdapter()->decodeResponse($cardResponse);
        return $this->processResponse($response);
    }

    /**
     * Get Trello card params
     *
     * @param $cardId
     *
     * @return $this
     */
    public function get($cardId)
    {
        $cardResponse = $this->getAdapter()
            ->run(
                array('cards' => $cardId),
                Zend_Http_Client::GET
            );

        $response = $this->getAdapter()->decodeResponse($cardResponse);
        return $this->processResponse($response);
    }

    /**
     * Update card by id, set new parameters
     *
     * @return array|string
     */
    public function sync()
    {
        $cardResponse = $this->getAdapter()
            ->run(
                array('cards' => $this->getCardId()),
                Zend_Http_Client::PUT,
                $this->prepareParams()
            );

        $response = $this->getAdapter()->decodeResponse($cardResponse);
        return $this->processResponse($response);
    }

    /**
     * Fast method to archive card: update with closed param
     *
     * @return array|string
     */
    public function archive()
    {
        $this
            ->setArchived(true)
            ->save();

        return $this;
    }

    /**
     * Delete some Trello card
     */
    public function delete()
    {
        $this->getAdapter()
            ->run(
                array('cards' => $this->getCardId()),
                Zend_Http_Client::DELETE
            );

        return parent::delete();
    }

    /**
     * Load card by trello card id
     *
     * @param $trelloId
     *
     * @return Mage_Core_Model_Abstract
     */
    public function loadFromTrello($trelloId)
    {
        return $this->load($trelloId, 'card_id');
    }

    /**
     * Get order connected to this card
     *
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

    public function setLabels($labels)
    {
        if ($labels instanceof Emagedev_Trello_Model_Resource_Label_Collection) {
            $labels = $labels->load()->toArray();
        }

        $this->labels = $labels;
        return $this;
    }

    /**
     * @param Emagedev_Trello_Model_Label|string $label
     *
     * @return $this
     */
    public function addLabel($label)
    {
        if (is_string($label)) {
            $label = $this->getLabelById($label);
        }

        if ($label instanceof Emagedev_Trello_Model_Label) {
            $this->labels[$label->getTrelloLabelId()] = $label;
        }

        return $this;
    }

    /**
     * @param Emagedev_Trello_Model_Label|string $label
     *
     * @return $this
     */
    public function removeLabel($label)
    {

        if (is_string($label)) {
            $label = $this->getLabelById($label);
        }

        if ($label instanceof Emagedev_Trello_Model_Label) {
            /** @var Emagedev_Trello_Model_Label $label */
            foreach ($this->getLabels() as $key => $labelToDelete) {
                if ($labelToDelete->getTrelloLabelId() == $label->getTrelloLabelId()) {
                    unset($this->labels[$key]);
                }
            }
        }

        return $this;
    }

    /**
     * @param string $labelId
     *
     * @return Emagedev_Trello_Model_Label
     */
    protected function getLabelById($labelId)
    {
        return Mage::getModel('trello/label')->loadFromTrello($labelId);
    }

    protected function prepareLabelIds()
    {
        if (!empty($this->labels)) {
            $trelloIds = array();

            /** @var Emagedev_Trello_Model_Label $label */
            foreach ($this->labels as $label) {
                $trelloIds[] = $label->getTrelloLabelId();
            }

            $this->setLabelIds(implode(',', $trelloIds));
        }

        return $this;
    }

    public function getLabels()
    {
        if (is_null($this->labels)) {
            $labelIds = explode(',', $this->getLabelIds());

            /** @var Emagedev_Trello_Model_Resource_Label_Collection $labelCollection */
            $labelCollection = Mage::getModel('trello/label')->getCollection();
            $labelCollection->addFieldToFilter('trello_label_id', array('in' => $labelIds));

            $this->labels = $labelCollection->load()->toArray();
        }

        return $this->labels;
    }

    protected function processResponse($response)
    {
        $this->setCardId($response['id']);

        return $this;
    }

    /**
     * Import comments from Trello
     */
    public function importComments()
    {
        /** @var Emagedev_Trello_Model_Resource_Action_Collection $actions */
        $actions = Mage::getModel('trello/action')->getCollection();
    }

    /**
     * Export comments to Trello
     */
    public function exportComments()
    {

    }
}
