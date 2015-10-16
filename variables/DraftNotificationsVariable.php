<?php

namespace Craft;

/**
 * Draft notifications variable.
 */
class DraftNotificationsVariable
{

    /**
     * @return array
     */
    public function getUserGroupOptions()
    {
        $userGroups = craft()->userGroups->getAllGroups();
        return array_map(array($this, 'getUserGroupOption'), $userGroups);
    }

    /**
     * @param UserGroupModel $group
     * @return array
     */
    private function getUserGroupOption(UserGroupModel $group) {
        return array(
            'label' => $group->name,
            'value' => $group->handle,
        );
    }
}