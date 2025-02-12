<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\References\Controller;

use Gm;
use Gm\Panel\Http\Response;
use Gm\Mvc\Module\BaseModule;
use Gm\Panel\Widget\EditWindow;
use Gm\Panel\Controller\FormController;

/**
 * Контроллер формы выбора элементов.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\References\Controller
 * @since 1.0
 */
class SelectionForm extends FormController
{
    /**
     * {@inheritdoc}
     * 
     * @var BaseModule|\Gm\Backend\References\Elements\Extension
     */
    public BaseModule $module;

    /**
     * {@inheritdoc}
     */
    protected string $defaultModel = 'SelectionForm';

    /**
     * {@inheritdoc}
     */
    public function createWidget(): EditWindow
    {

        /** @var EditWindow $window */
        $window = parent::createWidget();

        // окно компонента (Ext.window.Window Sencha ExtJS)
        $window->width = 500;
        $window->autoHeight = true;
        $window->layout = 'fit';
        $window->resizable = false;

        // панель формы (Gm.view.form.Panel GmJS)
        $window->form->autoScroll = true;
        $window->form->bodyPadding = 10;
        $window->form->router->route = $this->module->route('/selection');
        $window->form->loadJSONFile('selection-form', 'items');
        return $window;
    }

    /**
     * Действие "add" добавляет записи.
     * 
     * @return Response
     */
    public function addAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();
        /** @var \Gm\Http\Request $request */
        $request = Gm::$app->request;

        /** @var \Gm\Panel\Data\Model\FormModel $model модель данных */
        $model = $this->getModel($this->defaultModel);
        if ($model === false) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel]));
            return $response;
        }
        // т.к. атрибутов для запроса 
        $model->load($request->getPost());
        // валидация атрибутов модели
        if (!$model->validate()) {
            $response
                ->meta->error(Gm::t(BACKEND, 'Error filling out form fields: {0}', [$model->getError()]));
            return $response;
        }
        // сохранение атрибутов модели
        if (!$model->save()) {
            $response
                ->meta->error(
                    $model->hasErrors() ? $model->getError() : Gm::t(BACKEND, 'Could not add data')
                );
            return $response;
        }
        return $response;
    }
}
