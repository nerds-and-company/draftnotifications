<?php

namespace Craft;

use \PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests for the draft notifications events service.
 *
 * @coversDefaultClass Craft\DraftNotifications_EventCallbackService
 * @covers ::<!public>
 */
class DraftNotifications_EventCallbackServiceTest extends BaseTest
{

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        require_once __DIR__ . '/../../services/DraftNotifications_EventCallbackService.php';
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::saveDraftCallback
     * @dataProvider provideRecordStatus
     *
     * @param bool $newRecord
     * @param string $status
     */
    public function testSaveDraftCallbackShouldSendNotificationWithDraftAndStatus($newRecord, $status)
    {
        $mockDraft = $this->getMockDraft();
        $mockEvent = $this->getMockEvent(['draft' => $mockDraft, 'isNewDraft' => $newRecord]);
        $mockNotificationsService = $this->getMockDraftNotificationsService();

        $mockNotificationsService->expects($this->exactly(1))->method('sendNotifications')->with($mockDraft, $status);

        $draftNotificationsEventCallbacksService = new DraftNotifications_EventCallbackService();
        $draftNotificationsEventCallbacksService->saveDraftCallback($mockEvent);
    }

    /**
     * @covers ::deleteDraftCallback
     */
    public function testDeleteDraftCallbackShouldCallSendNotificationsWithDraftAndDeletedStatus()
    {
        $mockDraft = $this->getMockDraft();
        $mockEvent = $this->getMockEvent(['draft' => $mockDraft]);
        $mockNotificationsService = $this->getMockDraftNotificationsService();

        $mockNotificationsService->expects($this->exactly(1))
            ->method('sendNotifications')
            ->with($mockDraft, DraftNotifications_EventCallbackService::STATUS_DELETED_DRAFT);

        $draftNotificationsEventCallbacksService = new DraftNotifications_EventCallbackService();
        $draftNotificationsEventCallbacksService->deleteDraftCallback($mockEvent);
    }

    /**
     * @covers ::saveEntryCallback
     * @dataProvider provideRecordStatus
     *
     * @param bool $newRecord
     * @param string $status
     */
    public function testSaveEntryCallbackShouldSendNotificationWithEntryAndStatus($newRecord, $status)
    {
        $mockEntry = $this->getMockEntry();
        $mockEvent = $this->getMockEvent(['entry' => $mockEntry, 'isNewEntry' => $newRecord]);
        $mockNotificationsService = $this->getMockDraftNotificationsService();

        $mockNotificationsService->expects($this->exactly(1))
            ->method('sendNotifications')
            ->with($mockEntry, $status);

        $draftNotificationsEventCallbacksService = new DraftNotifications_EventCallbackService();
        $draftNotificationsEventCallbacksService->saveEntryCallback($mockEvent);
    }

    /**
     * @covers ::deleteEntryCallback
     */
    public function testDeleteEntryCallbackShouldCallSendNotificationsWithEntryAndDeletedStatus()
    {
        $mockEntry = $this->getMockEntry();
        $mockEvent = $this->getMockEvent(['entry' => $mockEntry]);
        $mockNotificationsService = $this->getMockDraftNotificationsService();

        $mockNotificationsService->expects($this->exactly(1))
            ->method('sendNotifications')
            ->with($mockEntry, DraftNotifications_EventCallbackService::STATUS_DELETED_DRAFT);

        $draftNotificationsEventCallbacksService = new DraftNotifications_EventCallbackService();
        $draftNotificationsEventCallbacksService->deleteEntryCallback($mockEvent);
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideRecordStatus()
    {
        require_once __DIR__ . '/../../services/DraftNotifications_EventCallbackService.php';

        return array(
            'newRecord' => [true, DraftNotifications_EventCallbackService::STATUS_NEW_DRAFT],
            'existingRecord' => [false, DraftNotifications_EventCallbackService::STATUS_EXISTING_DRAFT],
        );
    }

    //==============================================================================================================
    //=================================================  MOCKS  ====================================================
    //==============================================================================================================

    /**
     * @param array $params
     * @return Event|MockObject
     */
    private function getMockEvent(array $params)
    {
        $mock = $this->getMockBuilder('Craft\Event')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->params = $params;

        return $mock;
    }

    /**
     * @return EntryDraftModel|MockObject
     */
    private function getMockDraft()
    {
        $mock = $this->getMockBuilder('Craft\EntryDraftModel')
            ->disableOriginalConstructor()
            ->getMock();
        return $mock;
    }

    /**
     * @return EntryModel|MockObject
     */
    private function getMockEntry()
    {
        $mock = $this->getMockBuilder('Craft\EntryModel')
            ->disableOriginalConstructor()
            ->getMock();
        return $mock;
    }

    /**
     * @return DraftNotificationsService|MockObject
     */
    private function getMockDraftNotificationsService()
    {
        $mock = $this->getMockBuilder('Craft\DraftNotificationsService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->setComponent(craft(), 'draftNotifications', $mock);

        return $mock;
    }
}