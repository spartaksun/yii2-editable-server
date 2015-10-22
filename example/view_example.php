<?php
/**
 * Created by A.Belyakovskiy.
 * Date: 10/22/15
 * Time: 6:05 PM
 * @var $dataProvider \yii\data\DataProviderInterface
 */

use kartik\grid\EditableColumn;
use kartik\editable\Editable;
use kartik\grid\GridView;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [

        /* Example of text column */

        [
            'class' => EditableColumn::className(),
            'attribute' => 'first_name',
        ],

        /* Example of dropdown  list column */

        [
            'attribute' => 'sex',
            'format' => 'raw',
            'value' => function (\yii\db\ActiveRecord $model) {
                $dropDownData = ['1' => 'male', '0' => 'female'];

                return Editable::widget([
                    'model' => $model,
                    'attribute' => 'sex',
                    'inputType' => Editable::INPUT_DROPDOWN_LIST,
                    'data' => $dropDownData,
                    'displayValueConfig' => $dropDownData,
                    'beforeInput' => function () use ($model) {
                        echo yii\helpers\Html::hiddenInput('editableKey', $model->id);
                    },
                    'options' => ['id' => uniqid()], // it necessary if you render more than one grid on page
                ]);
            },
        ],


    ]
]);
