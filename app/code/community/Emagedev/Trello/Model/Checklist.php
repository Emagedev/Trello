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
 * Short description of the class
 *
 * Long description of the class (if any...)
 *
 * @category   Emagedev
 * @package    Emagedev_Trello
 * @subpackage Model
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Emagedev_Trello_Model_Checklist
 *
 * Checklist for Trello card
 *
 * @method $this setCardId(string $cardId)
 * @method string getCardId()
 * @method $this setName(string $name)
 * @method string getName()
 * @method $this setChecklistId(string $id)
 * @method string getChecklistId()
 * @method $this setPos(int $pos)
 * @method int getPos()
 */
class Emagedev_Trello_Model_Checklist extends Emagedev_Trello_Model_Trello_Entity_Abstract
{
    protected $_eventPrefix = 'trello_checklist';

    protected $items;

    protected $card;

    /**
     * @var array
     */
    protected $apiDataMap = array(
        'idCard'      => 'card_id',
        'name'        => 'name',
        'pos'         => 'pos'
    );

    /**
     * Init the resource
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('trello/checklist');

        $this->setPos('bottom');
    }

    public function loadFromTrello($trelloId)
    {
        return $this->load($trelloId, 'checklist_id');
    }

    public function addItem(Emagedev_Trello_Model_Checklist_Item $item)
    {
        $item->setChecklistId($this->getChecklistId());
        $this->getItems()->addItem($item);

        return $this;
    }

    public function getItems()
    {
        if (is_null($this->items)) {
            $this->items = new Varien_Data_Collection();
        }

        return $this->items;
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
            $this->export();
        }

        return $this;
    }

    /**
     * Create Trello list with provided data
     *
     * @return array|string
     */
    public function export()
    {
        $params = $this->prepareParams();

        $listResponse = $this->getAdapter()
            ->run(
                array('checklists'),
                Zend_Http_Client::POST,
                $params
            );

        $response = $this->getAdapter()->decodeResponse($listResponse);
        $this->processResponse($response);

        return $this->exportItems();
    }

    protected function exportItems()
    {
        /** @var Emagedev_Trello_Model_Checklist_Item $item */
        foreach ($this->getItems() as $item) {
            $params = $item->prepareParams();

            $itemResponse = $this->getAdapter()
                ->run(
                    array('checklists' => $this->getChecklistId(), 'checkItems'),
                    Zend_Http_Client::POST,
                    $params
                );

            $this->getAdapter()->decodeResponse($itemResponse);
        }

        return $this;
    }

    /**
     * Update Trello list with provided data
     *
     * @deprecated
     *
     * @return array|string
     */
    public function sync()
    {
        $params = $this->prepareParams();

        $listResponse = $this->getAdapter()
            ->run(
                array('checklists' => $this->getListId()),
                Zend_Http_Client::PUT,
                $params
            );

        $response = $this->getAdapter()->decodeResponse($listResponse);
        return $this->processResponse($response);
    }

    protected function processResponse($response)
    {
        $this->setChecklistId($response['id']);

        return $this;
    }
}
