<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 04.04.2017
 * Time: 12:13
 */

namespace app\components;

class ConditionDictionary
{
    const DEFAULT_VALUE = 0;
    const DEFAULT_KEY = 'NOT SET';
    const SET_VALUE = 1;
    const SET_KEY = 'SET';

    /**
     * value for strtotime() as "a week" or "10 days"
     */
    const BIRTH_INTERVAL = '1 WEEK';

    private static function MergeWithDefault($arr = [])
    {
        $arr[self::DEFAULT_VALUE] = self::DEFAULT_KEY;
        ksort($arr, SORT_NUMERIC);
        return $arr;
    }

    private static function getSetValues()
    {
        $values[self::SET_VALUE] = self::SET_KEY;
        return self::MergeWithDefault($values);
    }

    const DATE_BEFORE = 1;
    const DATE_AFTER = 2;
    const DATE_BOTH = 3;

    public static function getBirthValues()
    {
        $values = [
            self::DATE_BEFORE => self::BIRTH_INTERVAL . ' BEFORE',
            self::DATE_AFTER => self::BIRTH_INTERVAL . ' AFTER',
            self::DATE_BOTH => 'BOTH',
        ];
        return self::MergeWithDefault($values);
    }

    public static function getPhoneValues()
    {
        return self::getSetValues();
    }

    public static function getGenderValues()
    {
        $values = [
            2 => 'FEMALE',
            1 => 'MALE',
        ];
        return self::MergeWithDefault($values);
    }

    public static function getDaysFromRelativeFormats($relative)
    {
        $newdate = new \DateTime();
        $newdate->setTimestamp(strtotime('now +' . $relative));
        return $newdate->diff(new \DateTime())->days;
    }

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

    public static function getPhone($phone)
    {
        if (empty($phone))
            return [self::DEFAULT_VALUE];
        return [self::DEFAULT_VALUE, self::SET_VALUE];
    }

    public static function getPhoneEnd($phone)
    {
        $default = [self::DEFAULT_VALUE];
        if (empty($phone))
            return $default;

        if (preg_match_all('/(\d)/', $phone, $m)) {
            $default[] = implode('', array_slice($m[1], -4));
        }
        return $default;
    }

    public static function getGender($gender)
    {
        $default = [self::DEFAULT_VALUE];
        if (!empty($gender)) {
            $default[] = $gender;
        }
        return $default;
    }
}