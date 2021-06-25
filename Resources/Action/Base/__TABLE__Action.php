<?php
{include "../../includes/header.php"}


namespace {$moduleCode}\Action\Base;

use {$moduleCode}\Model\Map\{$table->getTableName()}TableMap;
use {$moduleCode}\Event\{$table->getTableName()}Event;
use {$moduleCode}\Event\{$table->getTableName()}Events;
use {$table->getFullQueryClass()};
use {$table->getFullModelClass()};
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Thelia\Action\BaseAction;
use Thelia\Core\Event\ToggleVisibilityEvent;
use Thelia\Core\Event\UpdatePositionEvent;
use Propel\Runtime\Propel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\TheliaEvents;
use \Thelia\Core\Event\TheliaFormEvent;
{if $table->hasSeo()}use Thelia\Core\Event\UpdateSeoEvent;{/if}


/**
 * Class {$table->getTableName()}Action
 * @package {$moduleCode}\Action
 * @author TheliaStudio
 */
class {$table->getTableName()}Action extends BaseAction implements EventSubscriberInterface
{
{if $table->hasSeo()}
    /**
     * Change Folder SEO
     *
     * @param UpdateSeoEvent $event
     * @param $eventName
     * @param EventDispatcherInterface $dispatcher
     * @return Object
     */
    public function updateSeo(UpdateSeoEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        return $this->genericUpdateSeo({$table->getQueryClass()}::create(), $event, $dispatcher);
    }
{/if}

    public function create({$table->getTableName()}Event $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $this->createOrUpdate($event, new {$table->getModelClass()}(), $dispatcher);
    }

    public function update({$table->getTableName()}Event $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $model = $this->get{$table->getTableName()}($event);

        $this->createOrUpdate($event, $model, $dispatcher);
    }

    public function delete({$table->getTableName()}Event $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $this->get{$table->getTableName()}($event)->setDispatcher($dispatcher)->delete();
    }

    protected function createOrUpdate({$table->getTableName()}Event $event, {$table->getModelClass()} $model, EventDispatcherInterface $dispatcher)
    {
        $con = Propel::getConnection({$table->getTableName()}TableMap::DATABASE_NAME);
        $con->beginTransaction();

        try {
{if $table->hasI18nBehavior()}
            $model->setLocale($event->getLocale());

{/if}
{foreach from=$table->getColumns() item=column}
{if ! $table->isExcludedColumn($column)}
            if (null !== ${$column->getCamelizedName()|lcfirst} = $event->get{$column->getCamelizedName()}()) {
                $model->set{$column->getCamelizedName()}(${$column->getCamelizedName()|lcfirst});
            }
{/if}

{/foreach}
            $model->setDispatcher($dispatcher)->save($con);

            $con->commit();
        } catch (\Exception $e) {
            $con->rollback();

            throw $e;
        }

        $event->set{$table->getTableName()}($model);
    }

    protected function get{$table->getTableName()}({$table->getTableName()}Event $event)
    {
        $model = {$table->getQueryClass()}::create()->findPk($event->getId());

        if (null === $model) {
            throw new \RuntimeException(sprintf(
                "The '{$table->getRawTableName()}' id '%d' doesn't exist",
                $event->getId()
            ));
        }

        return $model;
    }

{if $table->hasPosition()}
    public function updatePosition(UpdatePositionEvent $event)
    {
        $this->genericUpdatePosition(new {$table->getQueryClass()}(), $event);
    }

{/if}
{if $table->hasVisible()}
    public function toggleVisibility(ToggleVisibilityEvent $event)
    {
        $this->genericToggleVisibility(new {$table->getQueryClass()}(), $event);
    }

{/if}
    public function beforeCreateFormBuild(TheliaFormEvent $event)
    {
    }

    public function beforeUpdateFormBuild(TheliaFormEvent $event)
    {
    }

    public function afterCreateFormBuild(TheliaFormEvent $event)
    {
    }

    public function afterUpdateFormBuild(TheliaFormEvent $event)
    {
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            {$table->getTableName()}Events::CREATE => ["create", 128],
            {$table->getTableName()}Events::UPDATE => ["update", 128],
            {$table->getTableName()}Events::DELETE => ["delete", 128],
{if $table->hasPosition()}
            {$table->getTableName()}Events::UPDATE_POSITION => ["updatePosition", 128],
{/if}
{if $table->hasVisible()}
            {$table->getTableName()}Events::TOGGLE_VISIBILITY => ["toggleVisibility", 128],
{/if}
{if $table->hasSeo()}
            {$table->getTableName()}Events::UPDATE_SEO => ["updateSeo", 128],
{/if}
            TheliaEvents::FORM_BEFORE_BUILD . ".{$table->getRawTableName()}_create" => ["beforeCreateFormBuild", 128],
            TheliaEvents::FORM_BEFORE_BUILD . ".{$table->getRawTableName()}_update" => ["beforeUpdateFormBuild", 128],
            TheliaEvents::FORM_AFTER_BUILD . ".{$table->getRawTableName()}_create" => ["afterCreateFormBuild", 128],
            TheliaEvents::FORM_AFTER_BUILD . ".{$table->getRawTableName()}_update" => ["afterUpdateFormBuild", 128],
        );
    }
}
