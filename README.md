# Подсчет скидки

###### Запрос скидки
![Запрос скидки](https://github.com/loktionov/getDiscount/blob/master/web/img/demo1.png?raw=true)

###### Создание условий для скидки
![Создание условий для скидки](https://github.com/loktionov/getDiscount/blob/master/web/img/demo2.png?raw=true)

#### Составление SQL-запроса 
[app\models\ClientForm](https://github.com/loktionov/getDiscount/blob/master/models/ClientForm.php#L69)
```php
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
        
        //Выбираем только те условия, которые содержат все услуги выбранные пользователем
        //или только условия без услуг, если пользователь ничего не выбрал
        $in = \Yii::$app->db->getQueryBuilder()
            ->buildInCondition('IN', ['cs.service_id', $this->services], $in_params);
        $query->having(new Expression("COUNT(cs.id) = SUM(IF($in, 1, 0))", $in_params));
        
        $query->orderBy('c.discount desc');
        $this->condition = $query->one();
    }
```
### Полученный SQL-запрос
```sql
SELECT
  `c`.`name`,
  `c`.`discount`
FROM `condition` `c`
  LEFT JOIN `condition_service` `cs`
    ON `c`.`id` = 'cs.condition_id'
WHERE (`birth` IN (1, 3, 0))
AND (`phone` IN (0, 1))
AND (`phone_end` IN (0, '5555'))
AND (`gender` IN (0, 1))
AND (NOW() BETWEEN date_begin AND IFNULL(date_end, NOW()))
GROUP BY `c`.`id`
HAVING COUNT(cs.id) = SUM(IF(`cs`.`service_id` IN (3, 0), 1, 0))
ORDER BY `c`.`discount` DESC LIMIT 1
```
#### Сохранение связанных моделей 
[app\models\ConditionForm](https://github.com/loktionov/getDiscount/blob/master/models/ConditionForm.php#L54)
```php
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
```

#### Определение "категории" именника (неделя до дня рождения или после)
[app\components\ConditionDictionary](https://github.com/loktionov/getDiscount/blob/master/components/ConditionDictionary.php#L78)
```php
    /**
     * Собирает группу условий, в которую попадает переданная дата
     *
     * @param $birth string Дата рождения
     * @param string $curdate
     * @return array
     */
    public static function getBirth($birth, $curdate = 'now')
    {
        //Избавляемся от времени, чтобы оперировать целыми днями
        $curdate = new \DateTime(date('Y-m-d', strtotime($curdate)));

        $birth = new \DateTime($birth);

        if($birth > $curdate){
            return [self::DEFAULT_VALUE];
        }

        $diff = $birth->diff($curdate);

        //Прибавляем полное количество лет к дате рождения, чтобы получить дату последнего дня рождения
        $birth->add(new \DateInterval('P' . $diff->y . 'Y'));
        $diff = $birth->diff($curdate);

        //Количество дней прошедших с последнего дня рождения
        $days = $diff->days;

        //Если ДР сегодня, возвращаем весь набор условий
        if ($days == 0) {
            return array_keys(self::getBirthValues());
        }

        $birth_interval_in_days = self::getDaysFromRelativeFormats(self::BIRTH_INTERVAL);

        //Если количество прошедших дней меньше заданного интервала,
        //значит ДР попадает в условие ДО
        if ($days <= $birth_interval_in_days) {
            return [self::DATE_BEFORE, self::DATE_BOTH, self::DEFAULT_VALUE];
        }

        //Иначе проводим ту же процедуру со следующим ДР
        $birth->add(new \DateInterval('P1Y'));
        $days = $birth->diff($curdate)->days;
        if ($days < $birth_interval_in_days) {
            return [self::DATE_AFTER, self::DATE_BOTH, self::DEFAULT_VALUE];
        }

        return [self::DEFAULT_VALUE];
    }
```