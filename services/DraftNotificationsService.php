<?php

namespace Craft;

/**
 * Class DraftNotificationsService
 */
class DraftNotificationsService extends BaseApplicationComponent
{

    /**
     * @param EntryModel $draft
     * @param $status
     * @return bool
     */
    public function sendNotifications(EntryModel $draft, $status)
    {
        $recipients = $this->getNotificationRecipients();

        if (!$this->isDraft($draft) || empty($recipients)) {
            return true;
        }

        $mailSubject = $this->getNotificationSubject($status);
        $mailBody = $this->getNotificationBody($draft, $status, $mailSubject);
        $mailSubject .= ' (' . $draft->getSection()->name . ' - ' . $draft->title . ')';

        $email = new EmailModel();
        $email->replyTo = craft()->userSession->getUser()->email;
        $email->subject = $mailSubject;
        $email->body = $mailBody;
        $email->htmlBody = nl2br($mailBody);

        foreach ($recipients as $recipient) {
            $email->toEmail = $recipient->email;
            craft()->email->sendEmail($email);
        }

        return $email;
    }

    /**
     * @param EntryModel $draft
     * @param string $status
     * @param string $subject
     * @return string
     */
    private function getNotificationBody(EntryModel $draft, $status, $subject)
    {
        return craft()->templates->render('draftNotifications/_email.text.twig', array(
                'draft' => $draft,
                'status' => $status,
                'subject' => $subject,
            ));
    }

    /**
     * @param string $status
     * @return string
     */
    private function getNotificationSubject($status)
    {
        switch ($status) {
            case DraftNotifications_EventCallbackService::STATUS_NEW_DRAFT:
                $message = Craft::t('A new draft has been saved');
                break;
            case DraftNotifications_EventCallbackService::STATUS_EXISTING_DRAFT:
                $message = Craft::t('An existing draft has been saved');
                break;
            case DraftNotifications_EventCallbackService::STATUS_DELETED_DRAFT:
                $message = Craft::t('A draft has been deleted');
                break;
            default:
                $message = craft::t('A draft has been saved');
        }

        return $message;
    }

    /**
     * When users are not allowed to publish live changes any new entries they create will be disabled entries
     * which are not technically drafts
     *
     * @param EntryModel $entry
     * @return bool
     */
    private function isDraft(EntryModel $entry)
    {
        return $entry instanceof EntryDraftModel || $entry->getStatus() == 'disabled';
    }

    /**
     * Get all users from chiefEditor group except current user
     * @return UserModel[]
     */
    private function getNotificationRecipients()
    {
        $userGroupHandles = craft()->plugins->getPlugin('draftNotifications')->getSettings()->userGroups;
        if (empty($userGroupHandles)) {
            return [];
        }

        /** @var ElementCriteriaModel $userCriteria */
        $userCriteria = craft()->elements->getCriteria('User');
        $userCriteria->group = $userGroupHandles;
        $userCriteria->id = 'not ' . craft()->userSession->getUser()->id;
        return $userCriteria->find();
    }
}
