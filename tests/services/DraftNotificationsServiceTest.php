<?php

namespace Craft;

use \PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests for the draft notifications service.
 *
 * @coversDefaultClass Craft\DraftNotificationsService
 * @covers ::<!public>
 */
class DraftNotificationsServiceTest extends BaseTest
{

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        require_once __DIR__.'/../../services/DraftNotificationsService.php';
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::sendNotifications
     */
    public function testSendNotificationsShouldReturnTrueWhenNoRecipientsFound()
    {
        $currentUser = $this->getMockUser();
        $this->setMockUserSession($currentUser);

        $this->setMockElementsService([]);

        $mockDraft = $this->getMockDraft();

        $draftNotificationsService = new DraftNotificationsService();
        $result = $draftNotificationsService->sendNotifications($mockDraft, '');

        $this->assertTrue($result);
    }

    /**
     * @covers ::sendNotifications
     */
    public function testSendNotificationShouldReturnTrueWhenEntryIsEnabled()
    {
        $currentUser = $this->getMockUser();
        $this->setMockUserSession($currentUser);

        $user1 = $this->getMockUser();
        $this->setMockElementsService([$user1]);

        $mockEntry = $this->getMockEntry(true);

        $draftNotificationsService = new DraftNotificationsService();
        $result = $draftNotificationsService->sendNotifications($mockEntry, '');

        $this->assertTrue($result);
    }

    /**
     * @covers ::sendNotifications
     * @dataProvider provideDraftStatus
     */
    public function testSendNotificationsShouldSendEmailToRecipients($status)
    {
        $currentUser = $this->getMockUser();
        $this->setMockUserSession($currentUser);

        $user1 = $this->getMockUser();
        $this->setMockElementsService([$user1]);
        $this->setMockTemplatesService();
        $this->setMockEmailService();


        $newPath = craft()->path->getTemplatesPath();
        $mockDraft = $this->getMockDraft();

        $draftNotificationsService = new DraftNotificationsService();
        $result = $draftNotificationsService->sendNotifications($mockDraft, $status);

        $this->assertInstanceOf(EmailModel::class, $result);
    }

    //==============================================================================================================
    //==============================================  PROVIDERS  ===================================================
    //==============================================================================================================

    /**
     * @return array
     */
    public function provideDraftStatus()
    {
        require_once __DIR__ . '/../../services/DraftNotifications_EventCallbackService.php';

        return array(
            'newDraft' => [DraftNotifications_EventCallbackService::STATUS_NEW_DRAFT],
            'existingDraft' => [DraftNotifications_EventCallbackService::STATUS_EXISTING_DRAFT],
            'deletedDraft' => [DraftNotifications_EventCallbackService::STATUS_DELETED_DRAFT],
            'noStatus' => [''],
        );
    }

    //==============================================================================================================
    //=================================================  MOCKS  ====================================================
    //==============================================================================================================

    /**
     * @return EntryDraftModel|MockObject
     */
    private function getMockDraft()
    {
        $mockDraft = $this->getMockBuilder('Craft\EntryDraftModel')
            ->disableOriginalConstructor()
            ->getMock();

        $mockSection = $this->getmockBuilder('Craft\SectionModel')
            ->disableOriginalConstructor()
            ->getMock();
        $mockSection->title = 'sectionTitle';

        $mockDraft->expects($this->any())
            ->method('getSection')
            ->willReturn($mockSection);

        return $mockDraft;
    }

    /**
     * @param bool $enabled
     * @return EntryModel|MockObject
     */
    private function getMockEntry($enabled = false)
    {
        $mock = $this->getMockBuilder('Craft\EntryModel')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->enabled = $enabled;
        return $mock;
    }

    /**
     * @return UserModel|MockObject
     */
    private function getMockUser()
    {
        $mock = $this->getMockBuilder('Craft\UserModel')
            ->disableOriginalConstructor()
            ->getMock();
        return $mock;
    }

    /**
     * @param UserModel $mockUser
     */
    private function setMockUserSession(UserModel $mockUser)
    {
        $mock = $this->getMockBuilder('Craft\UserSessionService')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('getUser')
            ->willReturn($mockUser);

        $this->setComponent(craft(), 'userSession', $mock);

        craft()->templates->getTwig()->addGlobal('currentUser', $mockUser);
    }

    /**
     * @param UserModel[] $users
     */
    private function setMockElementsService(array $users = [])
    {
        $this->setMockPluginsService(['editor']);

        $mockElementCriteria = $this->getMockBuilder('Craft\ElementCriteriaModel')
            ->disableOriginalConstructor()
            ->getMock();

        $mockElementCriteria->expects($this->any())
            ->method('find')
            ->willReturn($users);

        $mockElementsService = $this->getMockBuilder('Craft\ElementsService')
            ->disableOriginalConstructor()
            ->getMock();

        $mockElementsService->expects($this->any())
            ->method('getCriteria')
            ->with('User')
            ->willReturn($mockElementCriteria);

        $this->setComponent(craft(), 'elements', $mockElementsService);
    }

    /**
     * @param UserGroupModel[] $userGroupHandles
     */
    private function setMockPluginsService(array $userGroupHandles = [])
    {
        $mockPlugin = $this->getMockBuilder('Craft\BasePlugin')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPlugin->expects($this->any())
            ->method('getSettings')
            ->willReturn((object)['userGroups' => $userGroupHandles]);

        $mockPluginsService = $this->getMockBuilder('Craft\PluginsService')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPluginsService->expects($this->any())
            ->method('getPlugin')
            ->with('draftNotifications')
            ->willReturn($mockPlugin);

        $this->setComponent(craft(), 'plugins', $mockPluginsService);
    }

    /**
     * Set mock email service
     */
    private function setMockEmailService()
    {
        $mock = $this->getMockBuilder('Craft\EmailService')
            ->disableOriginalConstructor()
            ->getMock();

        $settings = array(
            'emailAddress' => '',
            'senderName' => '',
        );

        $mock->expects($this->any())
            ->method('getSettings')
            ->willReturn($settings);

        $mock->expects($this->any())
            ->method('sendEmail')
            ->willReturn(true);

        $this->setComponent(craft(), 'email', $mock);
    }

    /**
     * Set mock request service
     */
    private function setMockTemplatesService()
    {
        $mockTemplatesService = $this->getMockBuilder('Craft\TemplatesService')
            ->disableOriginalConstructor()
            ->getMock();

        $mockTemplatesService->expects($this->any())
            ->method('render')
            ->willReturn(true);

        $mockTwigEnvironment = $this->getMockBuilder('Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();

        $mockTemplatesService->expects($this->any())
            ->method('getTwig')
            ->willReturn($mockTwigEnvironment);

        $this->setComponent(craft(), 'templates', $mockTemplatesService);
    }
}