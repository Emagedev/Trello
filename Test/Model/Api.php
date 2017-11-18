<?php


class Omedrec_Trello_Test_Model_Api extends EcomDev_PHPUnit_Test_Case
{
    protected $alias = 'trello/api';

    //board 5a0f02b9b88a403a70c53c59
    //card 5a0f0d224e52cc35b024528c

    //list 1 5a0f0d1d5deb7a4bd3091d92
    //list 2 5a0f0d1fdca57ca4185c5066

    /**
     * @test
     */
    public function checkCardUpdate()
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

    /**
     * @test
     */
    public function checkCardCreate()
    {
        /** @var Omedrec_Trello_Model_Api $api */
        $api = Mage::getModel($this->alias);

        $timestamp = time();
        $dateTime = new DateTime('now', new DateTimeZone('UTC'));
        $dateTime->setTimestamp($timestamp);

        $result = $api->createCard(
            array(
                'idList'      => '5a0f0d1fdca57ca4185c5066',
                'name'        => 'Order #1524',
                'desc'        => 'Created by Somebody, vendor: SomeVendor',
                'due'         => $dateTime->format(DateTime::W3C),
                'dueComplete' => 'true'
            )
        );

        var_dump($result);
    }


    /**
     * @test
     */
    public function checkListCreate()
    {
        /** @var Omedrec_Trello_Model_Api $api */
        $api = Mage::getModel($this->alias);

        $timestamp = time();
        $dateTime = new DateTime('now', new DateTimeZone('UTC'));
        $dateTime->setTimestamp($timestamp);

        $result = $api->createList(
            array(
                'idBoard'     => '5a0f02b9b88a403a70c53c59',
                'name'        => 'In Progress',
                'pos'         => 'bottom',
            )
        );

        var_dump($result);
    }
}