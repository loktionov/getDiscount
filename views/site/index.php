<?php

/* @var $this yii\web\View */
use app\components\ConditionDictionary;
use dosamigos\multiselect\MultiSelect;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Alert;
use yii\bootstrap\Html;
use yii\bootstrap\ToggleButtonGroup;
use yii\jui\DatePicker;
use yii\widgets\MaskedInput;
use yii\widgets\Pjax;

/* @var $model \app\models\ClientForm */

$this->title = 'My Yii Application'; ?>
<div class="discount-calculate">
    <div class="discount-form">
        <?php
        DatePicker::widget();
        Pjax::begin();

        if (is_array($model->condition)) {
            echo Alert::widget([
                'options' => [
                    'class' => 'alert-success'
                ],
                'body' => "Скидка по акции <strong>{$model->condition['name']}</strong> составляет <strong>{$model->condition['discount']} %</strong>",
            ]);

        }

        $form = ActiveForm::begin(['layout' => 'horizontal']);

        ?>

        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'services')->widget(MultiSelect::className(), [
            "options" => ['multiple' => "multiple"],
            'data' => \app\models\Service::find()->select(['service', 'id'])->indexBy('id')->orderBy('service')->column(),
            'value' => $model->services,
            "clientOptions" =>
                [
                    "includeSelectAllOption" => true,
                    'numberDisplayed' => 2
                ],
        ]); ?>
        <?= $form->field($model, 'birth')->widget(DatePicker::className(), ['dateFormat' => 'yyyy-MM-dd'])
            ->widget(MaskedInput::className(), ['mask' => '9999-99-99', 'clientOptions' => ['placeholder' => ' ']]) ?>

        <?= $form->field($model, 'phone')
            ->widget(MaskedInput::className(), ['mask' => '+7 (999) 999-99-99', 'clientOptions' => ['placeholder' => ' ']])
            ->textInput(['style' => 'width: 262px;'])
        ?>

        <?= $form->field($model, 'gender')->widget(ToggleButtonGroup::className(),
            [
                'type' => 'radio',
                'labelOptions' => [
                    'class' => 'btn-primary',
                ],
                'items' => ConditionDictionary::getGenderValues(),
            ]
        ); ?>

        <div class="form-group">

            <?= Html::submitButton('Расчитать', ['class' => 'btn btn-success']); ?>
            <?= Html::resetButton('Сброс', ['class' => 'btn btn-danger']) ?>

        </div>

        <?php
        ActiveForm::end();
        Pjax::end();
        ?>
    </div>
</div>

