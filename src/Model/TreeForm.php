<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\References\Model;

use Gm\Panel\Data\Model\FormModel;

/**
 * Модель данных формы справочника (дерево).
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\References\Model
 * @since 1.0
 */
class TreeForm extends FormModel
{
    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $this
            ->on(self::EVENT_AFTER_SAVE, function ($isInsert, $columns, $result, $message) {
                /** @var \Gm\Data\DataManager $manager */
                $manager   = $this->getDataManager();
                $parentKey = $manager->getAlias($manager->parentKey);
                // обновить узлы дерева
                $this->updateNodes();
                $this->response()
                    ->meta
                        // всплывающие сообщение
                        ->cmdPopupMsg($message['message'], $message['title'], $message['type'])
                        // обновить дерево
                        ->cmdReloadTreeGrid($this->module->viewId('grid'), $this->{$parentKey});
            })
            ->on(self::EVENT_AFTER_DELETE, function ($result, $message) {
                // обновить узлы дерева
                $this->updateChildNodes();
                $this->updateNodes();
                $this->response()
                    ->meta
                        // всплывающие сообщение
                        ->cmdPopupMsg($message['message'], $message['title'], $message['type'])
                        // обновить дерево
                        ->cmdReloadTreeGrid($this->module->viewId('grid'));
                
            });
    }

    /**
     * {@inheritdoc}
     */
    public function beforeUpdate(array &$columns): void
    {
        /** @var \Gm\Data\DataManager $manager */
        $manager = $this->getDataManager();
        $parentId = $columns[$manager->parentKey] ?? null;
        if ($parentId === 'null')
            $columns[$manager->parentKey] = null;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeInsert(array &$columns): void
    {
        /** @var \Gm\Data\DataManager $manager */
        $manager = $this->getDataManager();
        $parentId = $columns[$manager->parentKey] ?? null;
        if ($parentId === 'null')
            $columns[$manager->parentKey] = null;
    }

    /**
     * Обновляет дочернии узлы дерева.
     * 
     * @return void
     */
    protected function updateChildNodes(): void
    {
        /** @var \Gm\Data\DataManager $manager */
        $manager = $this->getDataManager();
        /** @var \Gm\Db\Adapter\Driver\AbstractCommand $command */
        $command = $this->getDb()->createCommand();
        // если предок удалён, всего его потомки не удаляются, а остаются без предка
        $command->update(
            $manager->tableName,
            [$manager->parentKey => null],
            [$manager->parentKey => $this->getIdentifier()]
        );
        $command->execute();
    }

    /**
     * Обновляет все узлы дерева, указывая количества потомков у предков. 
     * 
     * @return void
     */
    protected function updateNodes(): void
    {
        /** @var \Gm\Data\DataManager $manager */
        $manager = $this->getDataManager();
        /** @var \Gm\Db\Adapter\Driver\AbstractCommand $command */
        $command = $this->getDb()->createCommand();
        // обновление количества потомков у предков
        $command->setSql(
            'UPDATE `:@table` `rows`, (SELECT COUNT(*) `total`, `:@parentKey` FROM `:@table` GROUP BY `:@parentKey`) `nodes` '
          . 'SET `rows`.`count`=`nodes`.`total` WHERE `rows`.`:@primaryKey`=`nodes`.`:@parentKey` '
        );
        $command->bindValues([
            ':@table'      => $manager->tableName,
            ':@primaryKey' => $manager->primaryKey,
            ':@parentKey'  => $manager->parentKey
        ]);
        $command->execute();
    }

    /**
     * Возвращает значение для выпадающего списка разделов.
     * 
     * @return array
     */
    protected function getParentValue(): array
    {
        /** @var \Gm\Data\DataManager $manager */
        $manager = $this->getDataManager();
        $parentKey = $manager->getAlias($manager->parentKey);
        $parentId  = $this->{$parentKey};
        if ($parentId > 0) {
            $db = $this->getDb();
            $select = $db
                ->select($manager->tableName)
                ->columns(['*'])
                ->where([$manager->primaryKey => $parentId]);
            $item = $db->createCommand($select)->queryOne();
            if ($item) {
                return [
                    'type'  => 'combobox',
                    'value' => $parentId,
                    'text'  => $item['name']
                ];
            }
        }
        return [
            'type'  => 'combobox',
            'value' => 'null',
            'text'  => $this->t('is a parent item')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function processing(): void
    {
        parent::processing();

        /** @var \Gm\Data\DataManager $manager */
        $manager   = $this->getDataManager();
        $parentKey = $manager->getAlias($manager->parentKey);

        /** @var array $parentId идент. раздела (его предок) */
        $this->{$parentKey} = $this->getParentValue();
    }
}
