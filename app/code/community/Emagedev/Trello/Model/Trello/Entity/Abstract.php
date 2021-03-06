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
 * Class Emagedev_Trello_Model_Api
 *
 * API model - map different actions to requests
 */
abstract class Emagedev_Trello_Model_Trello_Entity_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * @var bool
     */
    protected $doSync = true;

    /**
     * @var Emagedev_Trello_Model_Api_Adapter
     */
    protected $adapter;

    /**
     * @var array
     */
    protected $apiDataMap = array();

    /**
     * Get API adapter model
     *
     * @return Emagedev_Trello_Model_Api_Adapter
     */
    protected function getAdapter()
    {
        if (is_null($this->adapter)) {
            $this->adapter = Mage::getModel('trello/api_adapter');
        }

        return $this->adapter;
    }

    protected function beforeExport()
    {
        return $this;
    }

    abstract public function export();

    abstract public function sync();

    protected function prepareParams()
    {
        $params = array();

        foreach ($this->apiDataMap as $apiParam => $dataKey)
        {
            if ($this->hasData($dataKey)) {
                $param = $this->getData($dataKey);

                if (is_bool($param)) {
                    $param = $param ? 'true' : 'false';
                }

                $params[$apiParam] = $param;
            }
        }

        return $params;
    }

    abstract protected function processResponse($response);

    public function enableSync()
    {
        $this->doSync = true;

        return $this;
    }

    public function disableSync()
    {
        $this->doSync = false;

        return $this;
    }

    /**
     * @return Emagedev_Trello_Helper_Data
     */
    protected function getDataHelper()
    {
        return Mage::helper('trello');
    }
}