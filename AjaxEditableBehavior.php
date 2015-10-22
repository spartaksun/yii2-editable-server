<?php
/**
 * Created by A.Belyakovskiy.
 * Date: 10/22/15
 * Time: 4:43 PM
 */

namespace spartaksun\Yii2\Editable;


use Yii;
use yii\base\ActionEvent;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\BaseActiveRecord;
use yii\web\Controller;
use yii\web\Response;

class AjaxEditableBehavior extends Behavior
{

    public $charset = 'UTF-8';
    public $showAllErrors = false;
    public $modelsConfig = [];
    public $errorMessages = [];

    const KARTIK_EDITABLE_INDEX = 'editableIndex',
        KARTIK_EDITABLE_KEY = 'editableKey',
        KARTIK_HAS_EDITABLE = 'hasEditable';


    /**
     *  {@inheritdoc}
     */
    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'checkAndSaveFirst'
        ];
    }

    /**
     * Checks if model attribute tried to save by ajax
     */
    public function checkAndSaveFirst(ActionEvent $event)
    {
        $post = Yii::$app->request->post();
        if (!$this->validatePostData($post)) {
            return;
        }

        $response = Yii::$app->response;
        $response->charset = $this->charset;
        $response->format = Response::FORMAT_JSON;

        $output = '';
        $message = '';

        $config = $this->modelsConfig;
        $modelConfig = $this->modelConfig($post, $config);

        try {
            $this->validateConfig($modelConfig);
            $modelClass = $modelConfig['class'];
            $model = $modelClass::findOne($post[self::KARTIK_EDITABLE_KEY]);

            if ($model instanceof BaseActiveRecord) {
                if (isset($post[self::KARTIK_EDITABLE_INDEX])) {
                    $models[$post[self::KARTIK_EDITABLE_INDEX]] = $model;
                    $load = Model::loadMultiple($models, $post);
                } else {
                    $load = $model->load($post);
                }

                if ($load) {
                    $dirtyAttributes = $model->dirtyAttributes;
                    if (is_array($dirtyAttributes) && !empty($dirtyAttributes)) {
                        $keys = array_keys($dirtyAttributes);

                        $attribute = $keys[0]; // we expect only one element

                        if ($model->validate()) {
                            if (!empty($modelConfig['validator'])) {
                                call_user_func_array($modelConfig['validator'], [$event->action, $model]);
                            }
                            if (!$model->hasErrors()) {
                                if ($model->save(false)) {
                                    $output = $model->{$attribute};
                                }
                            }
                        }
                        if ($model->hasErrors($attribute)) {
                            foreach ($model->errors as $error) {
                                if($this->showAllErrors) {
                                    $message .= implode("\n" , $error);
                                } else {
                                    $message .= reset($error) . "\n";
                                }
                            }
                        }
                    }
                } else {
                    $message = $this->getErrorMessage('cant.load.data');
                }
            } else {
                $message = $this->getErrorMessage('invalid.incoming.params');
            }
        } catch (InvalidConfigException $e) {
            $message = $e->getMessage();
        }

        $event->isValid = false;

        $response->data = ['output' => $output, 'message' => $message];
        $response->send();
    }

    /**
     * @param array $post
     * @return bool
     */
    private function validatePostData($post)
    {
        return is_array($post)
        && !empty($post)
        && !empty($post[self::KARTIK_HAS_EDITABLE])
        && !empty($post[self::KARTIK_EDITABLE_KEY]);
    }

    private function getErrorMessage($key)
    {
        $defaultMessages = [
            'cant.load.data' => 'Can`t load model data',
            'invalid.incoming.params' => 'Invalid incoming parameters',
            'action.not.configured' => 'Action was not configured!',
            'invalid.config.class' => 'Invalid configuration  class!',
            'invalid.config.validator' => 'Invalid configuration  validator!',
        ];

        return isset($this->errorMessages[$key])
            ? $this->errorMessages[$key]
            : $defaultMessages[$key];
    }

    /**
     * @param array $params
     * @param $config
     * @return BaseActiveRecord|null
     */
    private function modelConfig(array $params, $config)
    {
        if (empty($params[self::KARTIK_EDITABLE_KEY])) {
            return null;
        }

        foreach ($params as $modelName => $param) {
            if (isset($config[$modelName])) {
                return $config[$modelName];
            }
        }

        return null;
    }

    /**
     * @param $element
     * @throws InvalidConfigException
     */
    private function validateConfig($element)
    {
        if (empty($element) || !is_array($element)) {
            throw new InvalidConfigException(
                $this->getErrorMessage('action.not.configured')
            );
        }

        if (empty($element['class'])) {
            throw new InvalidConfigException(
                $this->getErrorMessage('invalid.config.class')
            );
        }

        if (isset($element['validator']) && !is_callable($element['validator'])) {
            throw new InvalidConfigException(
                $this->getErrorMessage('invalid.config.validator')
            );
        }
    }
}
