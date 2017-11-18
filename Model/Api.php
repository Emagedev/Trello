<?php


class Omedrec_Trello_Model_Api
{
    protected $authorized = false;

    protected $scope = 'write';

    protected $cardParams = array(
        'name', 'desc', 'closed',
        'idMembers', 'idAttachmentCover',
        'idList', 'idLabels',
        'idBoard', 'pos', 'due',
        'dueComplete', 'subscribed'
    );

    public function createCard($params)
    {
        $cardResponse = $this->getAdapter()
            ->run(
                array('cards'),
                Zend_Http_Client::POST,
                $params
            );

        var_dump($cardResponse);

        return $this->decodeResponse($cardResponse);
    }

    /**
     * @param string $cardId
     * @param array $params
     */
    public function updateCard($cardId, $params)
    {
        $cardResponse = $this->getAdapter()
            ->run(
                array('cards' => $cardId),
                Zend_Http_Client::PUT,
                $params
            );

        return $this->decodeResponse($cardResponse);
    }

    public function deleteCard($cardId)
    {

    }

    public function createList($params)
    {
        // required: name, idBoard

        $listResponse = $this->getAdapter()
            ->run(
                array('lists'),
                Zend_Http_Client::POST,
                $params
            );

        var_dump($listResponse);

        return $this->decodeResponse($listResponse);
    }

    public function updateList()
    {

    }

    public function deleteList()
    {

    }

    /**
     * @return Omedrec_Trello_Model_Api_Adapter
     */
    public function getAdapter()
    {
        return Mage::getModel('trello/api_adapter');
    }

    protected function decodeResponse($response)
    {
        return Mage::helper('core')->jsonDecode($response['body']);
    }
}