<?php

/**
 * Class Emagedev_Trello_Model_Checklist_Item
 *
 * @method $this setName(string $name)
 * @method string getName()
 * @method $this setChecklistId(string $id)
 * @method string getChecklistId()
 * @method $this setChecked(bool $checked)
 * @method bool getChecked()
 */
class Emagedev_Trello_Model_Checklist_Item extends Varien_Object
{
    /**
     * @var array
     */
    protected $apiDataMap = array(
        'id'      => 'checklist_id',
        'name'    => 'name',
        'pos'     => 'pos',
        'checked' => 'checked'
    );

    public function _construct()
    {
        $this->setChecked(false);

        parent::_construct();
    }

    public function prepareParams()
    {
        $params = array();

        foreach ($this->apiDataMap as $apiParam => $dataKey) {
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
}