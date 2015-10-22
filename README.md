# yii2-editable-server
Server-side for kartik-v/yii2-editable extension

## Example of usage
### Controller
```php
use spartaksun\Yii2\Editable\AjaxEditableBehavior;
use yii\base\Action;
use yii\web\Controller;
```

```php
class SiteController  extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'ajaxEditable' => [
                'class' => AjaxEditableBehavior::className(),
                'modelsConfig' => [ // List of models you want to be editable in grid
                    // Simple saving of model
                    'User' => [
                        'class' => User::className(),
                    ],
                    // Saving with additional validation before
                    'Order' => [
                        'class' => Order::className(),
                        'validator' => function (Action $action, Order $model) {
                            // You can check you model and add error message if necessary.
                            // Model wont be saved if any error added
                            $model->addError('status', 'Some error message');
                        }
                    ]
                ],
            ],
        ];
    }
}
```

### View
```php
use kartik\grid\EditableColumn;
use kartik\editable\Editable;
use kartik\grid\GridView;
```

```php
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
```