<?php

namespace Craft;

use \PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests for the draft notifications variable.
 *
 * @coversDefaultClass Craft\DraftNotificationsVariable
 * @covers ::<!public>
 */
class DraftNotificationsVariableTest extends BaseTest
{

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        require_once __DIR__ . '/../../variables/DraftNotificationsVariable.php';
    }

    //==============================================================================================================
    //=================================================  TESTS  ====================================================
    //==============================================================================================================

    /**
     * @covers ::getUserGroupOptions
     * @dataProvider provideMockUserGroups
     *
     * @param UserGroupModel[] $userGroups
     * @return array
     */
    public function testGetUserGroupOptionsReturnsAllUserGroups(array $userGroups)
    {
        $this->setMockUserGroupService($userGroups);

        $variable = new DraftNotificationsVariable();
        $userGroupOptions = $variable->getUserGroupOptions();

        $this->assertCount(count($userGroups), $userGroupOptions);

        return [$userGroups, $userGroupOptions];
    }


    /**
     * @covers ::getUserGroupOptions
     * @dataProvider provideMockUserGroups
     *
     * @param UserGroupModel[] $userGroups
     */
    public function testGetUserGroupOptionsSetsNameAsLabelAndHandleAsValue(array $userGroups)
    {
        $this->setMockUserGroupService($userGroups);

        $variable = new DraftNotificationsVariable();
        $userGroupOptions = $variable->getUserGroupOptions();

        foreach ($userGroups as $userGroup) {
            $expected = array(
                'label' => $userGroup->name,
                'value' => $userGroup->handle,
            );
            $this->assertSame($expected, array_shift($userGroupOptions));
        }
    }

    //==============================================================================================================
    //===============================================  PROVIDERS  ==================================================
    //==============================================================================================================

    /**
     * @return UserGroupModel[]
     */
    public function provideMockUserGroups()
    {
        $group1 = $this->getMockUserGroup('Group name 1', 'groupHandle1');
        $group2 = $this->getMockUserGroup('Group name 2', 'groupHandle2');
        $group3 = $this->getMockUserGroup('Group name 3', 'groupHandle3');

        return array(
            '0 groups' => [[]],
            '1 group' => [['group1' => $group1]],
            '2 groups' => [['group1' => $group1, 'group2' => $group2]],
            '3 groups' => [['group1' => $group1, 'group2' => $group2, 'group3' => $group3]],
        );
    }

    //==============================================================================================================
    //=================================================  MOCKS  ====================================================
    //==============================================================================================================

    /**
     * @param string $name
     * @param string $handle
     * @return UserGroupModel
     */
    private function getMockUserGroup($name, $handle)
    {
        $mock = $this->getMockBuilder('Craft\UserGroupModel')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('__get')
            ->will($this->returnValueMap(
                array(
                    ['name', $name],
                    ['handle', $handle],
                )
            ));
        return $mock;
    }

    /**
     * @param $userGroups
     */
    private function setMockUserGroupService(array $userGroups)
    {
        $mock = $this->getMockBuilder('Craft\UserGroupsService')
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('getAllGroups')
            ->willReturn($userGroups);

        $this->setComponent(craft(), 'userGroups', $mock);
    }
}