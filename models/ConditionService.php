<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "condition_service".
 *
 * @property integer $id
 * @property integer $condition_id
 * @property integer $service_id
 */
class ConditionService extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'condition_service';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['condition_id', 'service_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'condition_id' => 'Condition ID',
            'service_id' => 'Service ID',
        ];
    }
}
