<?php

use app\components\ConditionDictionary;
use dosamigos\multiselect\MultiSelect;
use yii\bootstrap\Html;
use yii\bootstrap\ToggleButtonGroup;
use yii\bootstrap\ActiveForm;
use yii\jui\DatePicker;
use yii\widgets\MaskedInput;

/* @var $this yii\web\View */
/* @var $model app\models\ConditionForm */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="condition-form">
    <?php $form = ActiveForm::begin(['layout' => 'horizontal']); ?>
    <?php DatePicker::widget(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'services')->widget(MultiSelect::className(), [
        //'id' => "condition-sevices",
        "options" => ['multiple' => "multiple"], // for the actual multiselect
        'data' => $model::getServicesData(), // data as array
        'value' => $model->getServicesArray(), // if preselected
        //'name' => 'Condition[services]', // name for the form
        "clientOptions" =>
            [
                "includeSelectAllOption" => true,
                'numberDisplayed' => 2
            ],
    ]); ?>
    <?=
    $form->field($model, 'birth')->widget(ToggleButtonGroup::className(),
        [
            'type' => 'radio',
            'labelOptions' => [
                'class' => 'btn-primary',
            ],
            'items' => ConditionDictionary::getBirthValues(),
        ]
    );
    ?>

    <?= $form->field($model, 'phone')->widget(ToggleButtonGroup::className(),
        [
            'type' => 'radio',
            'labelOptions' => [
                'class' => 'btn-primary',
            ],
            'items' => ConditionDictionary::getPhoneValues(),
        ]
    ); ?>

    <?= $form->field($model, 'phone_end')->widget(MaskedInput::className(), ['mask' => '9999', 'clientOptions' => ['placeholder' => ''],])
        ->textInput(['disabled' => !$model->phone, 'style' => 'width: 139px;']) ?>

    <?= $form->field($model, 'gender')->widget(ToggleButtonGroup::className(),
        [
            'type' => 'radio',
            'labelOptions' => [
                'class' => 'btn-primary',
            ],
            'items' => ConditionDictionary::getGenderValues(),
        ]
    ); ?>
    <?php $datePickerOptions = [/*'language' => 'ru',*/
        'dateFormat' => 'yyyy-MM-dd']; ?>
    <?php $dateMaskOptions = ['mask' => '9999-99-99', 'clientOptions' => ['placeholder' => '']]; ?>

    <?= $form->field($model, 'date_begin')->widget(DatePicker::className(), $datePickerOptions)->widget(MaskedInput::className(), $dateMaskOptions) ?>

    <?= $form->field($model, 'date_end')->widget(DatePicker::className(), $datePickerOptions)->widget(MaskedInput::className(), $dateMaskOptions) ?>

    <?= $form->field($model, 'discount')->textInput()->widget(MaskedInput::className(), ['mask' => '9{1,2}', 'clientOptions' => ['placeholder' => '']]) ?>


    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
