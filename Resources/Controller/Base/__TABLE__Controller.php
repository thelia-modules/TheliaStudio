<?php
{include "../../includes/header.php"}

namespace {$moduleCode}\Controller\Base;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Thelia\Controller\Admin\{if $table->hasSeo()}AbstractSeoCrudController{else}AbstractCrudController{/if};
use Thelia\Tools\URL;
use Thelia\Core\Security\Resource\AdminResources;
use {$moduleCode}\Event\{$table->getTableName()}Event;
use {$moduleCode}\Event\{$table->getTableName()}Events;
use {$table->getFullQueryClass()};
{if $table->hasVisible()}
use Thelia\Core\Event\ToggleVisibilityEvent;
{/if}
{if $table->hasPosition()}
use Thelia\Core\Event\UpdatePositionEvent;
{/if}

/**
 * Class {$table->getTableName()}Controller
 * @package {$moduleCode}\Controller\Base
 * @author TheliaStudio
 */
class {$table->getTableName()}Controller extends {if $table->hasSeo()}AbstractSeoCrudController{else}AbstractCrudController{/if}

{
    public function __construct()
    {
        parent::__construct(
            "{$table->getRawTableName()}",
            "{if $table->hasPosition()}manual{else}id{/if}",
            "order",
            AdminResources::MODULE,
            {$table->getTableName()}Events::CREATE,
            {$table->getTableName()}Events::UPDATE,
            {$table->getTableName()}Events::DELETE,
            {if $table->hasVisible()}{$table->getTableName()}Events::TOGGLE_VISIBILITY{else}null{/if},
            {if $table->hasPosition()}{$table->getTableName()}Events::UPDATE_POSITION{else}null{/if},
            {if $table->hasSeo()}{$table->getTableName()}Events::UPDATE_SEO{else}null{/if},
            "{$moduleCode}"
        );
    }

    /**
     * Return the creation form for this object
     */
    protected function getCreationForm()
    {
        return $this->createForm("{$table->getRawTableName()}.create");
    }

    /**
     * Return the update form for this object
     */
    protected function getUpdateForm($data = array())
    {
        if (!is_array($data)) {
            $data = array();
        }

        return $this->createForm("{$table->getRawTableName()}.update", "form", $data);
    }

    /**
     * Hydrate the update form for this object, before passing it to the update template
     *
     * @param mixed $object
     */
    protected function hydrateObjectForm($object)
    {
{if $table->hasSeo()}
        // Hydrate the "SEO" tab form
        $this->hydrateSeoForm($object);
{/if}

        $data = array(
{foreach from=$table->getColumns() item=column}
    {if ! $table->isExcludedColumn($column) && $column->getName() !== 'position'}
        "{$column->getName()}" => {if $column->getFormType() == 'checkbox'}(bool) {/if}$object->get{$column->getCamelizedName()}(),
    {/if}
{/foreach}
        );

        return $this->getUpdateForm($data);
    }

    /**
     * Creates the creation event with the provided form data
     *
     * @param mixed $formData
     * @return \Thelia\Core\Event\ActionEvent
     */
    protected function getCreationEvent($formData)
    {
        $event = new {$table->getTableName()}Event();

{foreach from=$table->getColumns() item=column}
{if $column->getName() != 'id' && $column->getName() != 'position' && ! $table->isExcludedColumn($column)}
        $event->set{$column->getCamelizedName()}($formData["{$column->getName()}"]);
{/if}
{/foreach}

        return $event;
    }

    /**
     * Creates the update event with the provided form data
     *
     * @param mixed $formData
     * @return \Thelia\Core\Event\ActionEvent
     */
    protected function getUpdateEvent($formData)
    {
        $event = new {$table->getTableName()}Event();

{foreach from=$table->getColumns() item=column}
{if $column->getName() != 'position' && ! $table->isExcludedColumn($column)}
        $event->set{$column->getCamelizedName()}($formData["{$column->getName()}"]);
{/if}
{/foreach}

        return $event;
    }

    /**
     * Creates the delete event with the provided form data
     */
    protected function getDeleteEvent()
    {
        $event = new {$table->getTableName()}Event();

        $event->setId($this->getRequest()->request->get("{$table->getRawTableName()}_id"));

        return $event;
    }

    /**
     * Return true if the event contains the object, e.g. the action has updated the object in the event.
     *
     * @param mixed $event
     */
    protected function eventContainsObject($event)
    {
        return null !== $this->getObjectFromEvent($event);
    }

    /**
     * Get the created object from an event.
     *
     * @param mixed $event
     */
    protected function getObjectFromEvent($event)
    {
        return $event->get{$table->getTableName()}();
    }

    /**
     * Load an existing object from the database
     */
    protected function getExistingObject()
    {
        $object = {$table->getQueryClass()}::create()
        ->findPk($this->getRequest()->query->get("{$table->getRawTableName()}_id"))
    ;

        if (null !== $object && method_exists($object, 'setLocale')) {
            $object->setLocale($this->getCurrentEditionLocale());
        }

        return $object;
    }

    /**
     * Returns the object label form the object event (name, title, etc.)
     *
     * @param mixed $object
     */
    protected function getObjectLabel($object)
    {
{if $table->has("title")}
        return $object->getTitle();
{elseif $table->has("name")}
        return $object->getName();
{elseif $table->has("code")}
        return $object->getCode();
{else}
        return '';
{/if}
    }

    /**
     * Returns the object ID from the object
     *
     * @param mixed $object
     */
    protected function getObjectId($object)
    {
        return $object->getId();
    }

    /**
     * Render the main list template
     *
     * @param mixed $currentOrder , if any, null otherwise.
     */
    protected function renderListTemplate($currentOrder)
    {
        $this->getParser()
            ->assign("order", $currentOrder)
        ;

        return $this->render("{$table->getListTemplateName()}");
    }

    /**
     * Render the edition template
     */
    protected function renderEditionTemplate()
    {
        $this->getParserContext()
            ->set(
                "{$table->getRawTableName()}_id",
                $this->getRequest()->query->get("{$table->getRawTableName()}_id")
            )
        ;

        return $this->render("{$table->getEditionTemplateName()}");
    }

    /**
     * Must return a RedirectResponse instance
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToEditionTemplate()
    {
        $currentTab = '';
{if $table->hasSeo()}
        // If we come from the SEO tab, the object ID is in the 'current_id' variable
        if (null !== $id = $this->getRequest()->get("current_id")) {
            $currentTab = 'seo';
        }

        if (null === $id) {
            $id = $this->getRequest()->query->get("{$table->getRawTableName()}_id");
        }
{else}
        $id = $this->getRequest()->query->get("{$table->getRawTableName()}_id");
{/if}

        $args = [ "{$table->getRawTableName()}_id" => $id ];

        if (! empty($currentTab)) {
            $args['current_tab'] = $currentTab;
        }

        return new RedirectResponse(
            URL::getInstance()->absoluteUrl("{$table->getEditionPathInfo()}", $args)
        );
    }

    /**
     * Must return a RedirectResponse instance
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToListTemplate()
    {
        return new RedirectResponse(
            URL::getInstance()->absoluteUrl("{$table->getListPathInfo()}")
        );
    }
{if $table->hasVisible()}

    protected function createToggleVisibilityEvent()
    {
        return new ToggleVisibilityEvent($this->getRequest()->query->get("{$table->getRawTableName()}_id"));
    }
{/if}
{if $table->hasPosition()}

    protected function createUpdatePositionEvent($positionChangeMode, $positionValue)
    {
        return new UpdatePositionEvent(
            $this->getRequest()->query->get("{$table->getRawTableName()}_id"),
            $positionChangeMode,
            $positionValue
        );
    }
{/if}
}
