<?php
/**
 * Emagedev extension for Magento
 *
 * NOTICE OF LICENSE
 *
 * Copyright (C) Effdocs, LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 *
 * This source file is proprietary and confidential
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Omedrec Startpage module to newer versions in the future.
 *
 * @copyright  Copyright (C) Effdocs, LLC
 * @license    http://www.binpress.com/license/view/l/45d152a594cd48488fda1a62931432e7
 */

/**
 * @category   Omedrec
 * @package    Omedrec_Trello
 * @subpackage Test
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Omedrec_Trello_Test_Model_Api
 */
class Omedrec_Trello_Test_Model_Api extends EcomDev_PHPUnit_Test_Case
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