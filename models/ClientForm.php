<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 04.04.2017
 * Time: 18:04
 */

namespace app\models;


use app\components\ConditionDictionary;
use yii\base\Model;
use yii\db\Expression;
use yii\db\Query;
use yii\db\QueryBuilder;

class ClientForm extends Model
{
    public $name;
    public $services;
    public $birth;
    public $phone;
    public $gender = 0;
    public $condition = 0;

    public function rules()
    {
        return [
            [['name', 'birth'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['birth'], 'date', 'format' => 'php:Y-m-d'],
            [['services', 'phone', 'gender',], 'safe'],
        ];
    }

    public function attributes()
    {
        return [
            'name',
            'services',
            'birth',
            'phone',
            'gender',
            'condition',
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'ФИО',
            'services' => 'Услуги',
            'birth' => 'Дата рождения',
            'phone' => 'Телефон',
            'gender' => 'Пол',
            'condition' => 'Скидка',
        ];
    }

    public function getBirthGroup()
    {

    }

    /**
     * Подсчитывает максимальную скидку по заданным параметрам
     */
    public function calculateDiscount()
    {
        $query = (new Query())
            ->select(['c.name', 'c.discount'])
            ->limit(1)
            ->from('condition as c')
            ->leftJoin('condition_service as cs', ['c.id' => 'cs.condition_id'])
            ->groupBy('c.id');

        $birth = ConditionDictionary::getBirth($this->birth);
        $query->andWhere(['birth' => $birth]);

        $phone = ConditionDictionary::getPhone($this->phone);
        $query->andWhere(['phone' => $phone]);

        $phone_end = ConditionDictionary::getPhoneEnd($this->phone);
        $query->andWhere(['phone_end' => $phone_end]);

        $gender = ConditionDictionary::getGender($this->gender);
        $query->andWhere(['gender' => $gender]);

        //Проверяем что условия действующие
        $query->andWhere('now() between date_begin AND IFNULL(date_end, now())');

        $this->services = ConditionForm::filterValidServices($this->services);
        $this->services[] = 0;

        $in = \Yii::$app->db->getQueryBuilder()->buildInCondition('IN', ['cs.service_id', $this->services], $in_params);


        //Выбираем только те условия, которые содержат все услуги выбранные пользователем
        //или только условия без услуг, если пользователь ничего не выбрал
        $query->having(new Expression("COUNT(cs.id) = SUM(IF($in, 1, 0))", $in_params));

        $query->orderBy('c.discount desc');


        //$sql = $query->createCommand()->getRawSql();
        //$res = $query->one();
        $this->condition = $query->one();
    }
}