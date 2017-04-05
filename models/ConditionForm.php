<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 04.04.2017
 * Time: 16:25
 */

namespace app\models;


use Yii;

class ConditionForm extends Condition
{

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['services'], 'safe'],
        ]);
    }

    public $services = [];

    public function getServicesArray()
    {
        if ($this->isNewRecord)
            return [];
        $this->services = $this->getConditionService()->select(['service_id'])->column();
        return empty($this->services) ? [] : $this->services;
    }

    public static function getServicesData()
    {
        $services = Service::find()->select(['service', 'id'])->indexBy('id')->orderBy('service')->column();
        return empty($services) ? [] : $services;
    }


    public static function filterValidServices($services)
    {
        $services_all = array_keys(self::getServicesData());
        $services = !is_array($services) ? [] : $services;
        return array_intersect($services, $services_all);
    }

    public function beforeSave($insert)
    {
        $this->services = self::filterValidServices($this->services);
        return parent::beforeSave($insert);
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if ($runValidation AND !$this->validate($attributeNames))
            return false;
        $transaction = Yii::$app->db->beginTransaction();
        if (!parent::save($runValidation, $attributeNames)) {
            $transaction->rollBack();
            return false;
        }
        if (!$this->saveServices()) {
            $transaction->rollBack();
            return false;
        }
        $transaction->commit();
        return true;
    }

    public function saveServices()
    {
        $keep = [];
        foreach ($this->services as $service) {
            $condition_service = $this->getConditionService()->where(['service_id' => $service])->one();
            $condition_service = empty($condition_service) ? new ConditionService() : $condition_service;
            $condition_service->condition_id = $this->id;
            $condition_service->service_id = $service;
            if (!$condition_service->save()) {
                return false;
            }
            $keep[] = $condition_service->id;
        }
        $query = ConditionService::find()->andWhere(['condition_id' => $this->id]);
        if ($keep) {
            $query->andWhere(['not in', 'id', $keep]);
        }
        foreach ($query->all() as $cs) {
            $cs->delete();
        }
        return true;
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), ['services']);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), ['services' => 'Services']);
    }
}