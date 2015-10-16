<?php

namespace Craft;

class DraftNotificationsPlugin extends BasePlugin
{

    /**
     * @return string
     */
    public function getName()
    {
        return Craft::t('Draft Notifications');
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0.0';
    }

    /**
     * @return string
     */
    public function getDeveloper()
    {
        return 'Bart van Gennep';
    }

    /**
     * @return string
     */
    public function getDeveloperUrl()
    {
        return 'http://www.itmundi.nl';
    }

    /**
     * @return array
     */
    public function defineSettings()
    {
        return array(
            'userGroups' => AttributeType::Mixed,
        );
    }

    /**
     * @return string
     */
    public function getSettingsHtml()
    {
        return craft()->templates->render('draftNotifications/_settings.html.twig', array(
            'settings' => $this->getSettings(),
        ));
    }

    /**
     * Initialize draft listeners
     */
    public function init()
    {
        craft()->on('entryRevisions.saveDraft', array(craft()->draftNotifications_eventCallback, 'saveDraftCallBack'));
        craft()->on('entryRevisions.deleteDraft', array(craft()->draftNotifications_eventCallback, 'deleteDraftCallBack'));
        craft()->on('entries.saveEntry', array(craft()->draftNotifications_eventCallback, 'saveEntryCallBack'));
        craft()->on('entries.deleteEntry', array(craft()->draftNotifications_eventCallback, 'deleteEntryCallBack'));
    }
}