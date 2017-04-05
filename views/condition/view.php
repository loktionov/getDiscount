<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Condition */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Conditions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="condition-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            [
                'attribute' => 'birth',
                'value' => \app\components\ConditionDictionary::getBirthValues()[$model->birth],
            ],
            [
                'attribute' => 'phone',
                'value' => \app\components\ConditionDictionary::getPhoneValues()[$model->phone],
            ],
            'phone_end',
            [
                'attribute' => 'gender',
                'value' => \app\components\ConditionDictionary::getPhoneValues()[$model->gender],
            ],
            'date_begin',
            'date_end',
            'discount',
        ],
    ]) ?>

    <?= GridView::widget([
        'dataProvider' => new \yii\data\ActiveDataProvider([
                'query' => $model->getService(),
        ]),

    ]); ?>

</div>
