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
 * @subpackage Test
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Emagedev_Trello_Test_Model_Api
 */
class Emagedev_Trello_Test_Model_Api extends EcomDev_PHPUnit_Test_Case
{
    protected $alias = 'trello/api';

    /**
     * Check that different API calls running with correct
     * HTTP code and parameters
     *
     * @test
     */
    public function massApiCheck()
    {
        $actions = array(
            'create_card'  => array(
                array('cards'),
                Zend_Http_Client::POST,
                array('name' => 'Test')
            ),
            'get_card'     => array(
                array('cards' => ':id'),
                Zend_Http_Client::GET
            ),
            'update_card'  => array(
                array('cards' => ':id'),
                Zend_Http_Client::PUT,
                array('name' => 'Test')
            ),
            'archive_card' => array(
                array('cards' => ':id'),
                Zend_Http_Client::PUT
            ),
            'delete_card'  => array(
                array('cards' => ':id'),
                Zend_Http_Client::DELETE
            ),
            'create_list'  => array(
                array('lists'),
                Zend_Http_Client::POST,
                array('name' => 'Test', 'idBoard' => 'test')
            ),
            'update_list'  => array(
                array('lists' => ':id'),
                Zend_Http_Client::PUT,
                array('name' => 'Test')
            ),
            'archive_list' => array(
                array('lists' => ':id'),
                Zend_Http_Client::PUT
            )
        );

        $params = array(
            'create_card'  => array(
                array('name' => 'Test')
            ),
            'get_card'     => array(
                ':id'
            ),
            'update_card'  => array(
                ':id',
                array('name' => 'Test')
            ),
            'archive_card' => array(
                ':id',
                array('closed' => true)
            ),
            'delete_card'  => array(
                ':id'
            ),
            'create_list'  => array(
                array(
                    'name'    => 'Test',
                    'idBoard' => 'test'
                )
            ),
            'update_list'  => array(
                ':id',
                array('name' => 'Test')
            ),
            'archive_list' => array(
                ':id',
                array('closed' => true)
            )
        );

        $adapterMock = $this->mockModel('trello/api_adapter', array('run'));
        $adapterMock->replaceByMock('model');

        $adapterMockMethod = $adapterMock
            ->expects($this->any())
            ->method('run');

        call_user_func_array(array($adapterMockMethod, 'withConsecutive'), array_values($actions));

        $adapterMockMethod
            ->willReturn(
                array(
                    'code'   => 200,
                    'header' => 'Header',
                    'body'   => '{"status": "ok"}'
                )
            );

        $api = Mage::getModel($this->alias);

        foreach ($actions as $method => $callParams) {
            $methodCamelCase = $this->toCamelCase($method);

            call_user_func_array(array($api, $methodCamelCase), $params[$method]);
        }
    }

    protected function toCamelCase($string, $capitalizeFirstCharacter = false)
    {
        $str = str_replace('_', '', ucwords($string, '_'));

        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }

        return $str;
    }
}