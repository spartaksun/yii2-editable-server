<?php
use spartaksun\Yii2\Editable\AjaxEditableBehavior;
use yii\base\Action;
use yii\web\Controller;


class SiteController  extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            /* ... , */

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