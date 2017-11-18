<?php


class Omedrec_Trello_Test_Helper_Order extends EcomDev_PHPUnit_Test_Case
{
    protected $alias = 'trello/order';

    /**
     * @test
     */
    public function checkOrderCreating()
    {
        /** @var Omedrec_Trello_Model_Api $api */
        $api = Mage::getModel($this->alias);

        $result = $api->updateCard(
            '5a0f0d224e52cc35b024528c', array(
                'idList' => '5a0f0d1fdca57ca4185c5066'
            )
        );

        var_dump($result);
    }
}