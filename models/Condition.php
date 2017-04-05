<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "condition".
 *
 * @property integer $id
 * @property string $name
 * @property integer $birth
 * @property integer $phone
 * @property integer $phone_end
 * @property integer $gender
 * @property string $date_begin
 * @property string $date_end
 * @property integer $discount
 */
class Condition extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'condition';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'date_begin', 'discount'], 'required'],
            [['birth', 'phone', 'phone_end', 'gender', 'discount'], 'integer'],
            [['birth', 'phone', 'gender', 'discount'], 'default', 'value' => 0],
            [['date_begin', 'date_end'], 'safe'],
            [['date_begin', 'date_end'], 'date', 'format' => 'php:Y-m-d'],
            [['date_begin',], 'safe'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'birth' => 'Birth',
            'phone' => 'Phone',
            'phone_end' => 'Phone End',
            'gender' => 'Gender',
            'date_begin' => 'Date Begin',
            'date_end' => 'Date End',
            'discount' => 'Discount',
        ];
    }

    public function getConditionService()
    {
        return $this->hasMany(ConditionService::className(), ['condition_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getService()
    {
        return $this->hasMany(Service::className(), ['id' => 'service_id'])
            ->viaTable('condition_service', ['condition_id' => 'id']);
    }
}
