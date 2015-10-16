<?php

namespace Craft;

/**
 * Class DraftNotifications_EventsService
 */
class DraftNotifications_EventCallbackService extends BaseApplicationComponent
{
    const STATUS_NEW_DRAFT = 'savedNewDraft';
    const STATUS_EXISTING_DRAFT = 'savedExistingDraft';
    const STATUS_DELETED_DRAFT = 'deletedDraft';


    /**
     * @param Event $event
     */
    public static function saveDraftCallBack(Event $event)
    {
        $status = $event->params['isNewDraft'] ? self::STATUS_NEW_DRAFT : self::STATUS_EXISTING_DRAFT;
        craft()->draftNotifications->sendNotifications($event->params['draft'], $status);
    }

    /**
     * @param Event $event
     */
    public function deleteDraftCallback(Event $event)
    {
        craft()->draftNotifications->sendNotifications($event->params['draft'], self::STATUS_DELETED_DRAFT);
    }

    /**
     * @param Event $event
     */
    public function saveEntryCallback(Event $event)
    {
        $status = $event->params['isNewEntry'] ? self::STATUS_NEW_DRAFT : self::STATUS_EXISTING_DRAFT;
        craft()->draftNotifications->sendNotifications($event->params['entry'], $status);
    }

    /**
     * @param Event $event
     */
    public function deleteEntryCallback(Event $event)
    {
        craft()->draftNotifications->sendNotifications($event->params['entry'], self::STATUS_DELETED_DRAFT);
    }
}