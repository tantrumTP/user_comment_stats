<?php

use Drupal\Tests\UnitTestCase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\user_comment_stats\Services\UserCommentStatsService;

class UserCommentStatsServiceTest extends UnitTestCase
{

    protected $currentUser;
    protected $entityTypeManager;
    protected $routeMatch;
    protected $userCommentStatsService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->currentUser = $this->createMock(AccountInterface::class);
        $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
        $this->routeMatch = $this->createMock(RouteMatchInterface::class);

        $this->userCommentStatsService = new UserCommentStatsService(
            $this->currentUser,
            $this->entityTypeManager,
            $this->routeMatch
        );
    }

    /**
     * Check if the user is on the route and the service returns the user of the route
     */
    public function testGetContextualUserFromRoute()
    {
        $mockedUserFromRoute = $this->createMock(AccountInterface::class);
        $this->routeMatch->method('getParameter')
            ->with('user')
            ->willReturn($mockedUserFromRoute);

        $result = $this->userCommentStatsService->getContextualUser();
        $this->assertSame($mockedUserFromRoute, $result);
    }

    /**
     * Check if the user is not on the route and the service returns the current logued user
     */
    public function testGetContextualUserFromCurrentUser()
    {
        $this->routeMatch->method('getParameter')
            ->with('user')
            ->willReturn(NULL);

        $result = $this->userCommentStatsService->getContextualUser();
        $this->assertSame($this->currentUser, $result);
    }


    public function testGetContextualUserWhenAnonymous()
    {
        $this->routeMatch->method('getParameter')
            ->with('user')
            ->willReturn(NULL);

        $anonymousUser = $this->createMock(AccountInterface::class);
        $anonymousUser->method('id')->willReturn(0);

        $this->currentUser = $anonymousUser;

        $result = $this->userCommentStatsService->getContextualUser();
        $this->assertEquals($this->currentUser, $result);
        $this->assertEquals(0, $result->id(), 'Expected an anonymous user (UID 0).');
    }

    /**
     * Check if method returns the correct number of comments
     */
    public function testGetTotalComments()
    {
        //User mock
        $mockUser = $this->createMock(AccountInterface::class);
        $mockUser->method('id')->willReturn(1);

        //EntityQuery mock
        $mockQuery = $this->createMock(\Drupal\Core\Entity\Query\QueryInterface::class);
        $mockQuery->method('condition')->willReturn($mockQuery);
        $mockQuery->method('count')->willReturn($mockQuery);
        $mockQuery->method('accessCheck')->willReturn($mockQuery);
        $mockQuery->method('execute')->willReturn(3);

        //Configure the EntityTypeManager to return the comment storage.
        $mockStorage = $this->createMock(\Drupal\Core\Entity\EntityStorageInterface::class);
        $mockStorage->method('getQuery')->willReturn($mockQuery);
        $this->entityTypeManager->method('getStorage')->with('comment')->willReturn($mockStorage);

        $result = $this->userCommentStatsService->getTotalComments($mockUser);

        $this->assertEquals(3, $result);
    }

    public function testGetLastComments()
    {
        // Mock del usuario
        $mockUser = $this->createMock(AccountInterface::class);
        $mockUser->method('id')->willReturn(1);

        //EntityQuery mock
        $mockQuery = $this->createMock(\Drupal\Core\Entity\Query\QueryInterface::class);
        $mockQuery->method('condition')->willReturn($mockQuery);
        $mockQuery->method('sort')->willReturn($mockQuery);
        $mockQuery->method('range')->willReturn($mockQuery);
        $mockQuery->method('accessCheck')->willReturn($mockQuery);
        $mockQuery->method('execute')->willReturn([10, 20]);

        // Comment storage Mock
        $mockStorage = $this->createMock(\Drupal\Core\Entity\EntityStorageInterface::class);
        $mockStorage->method('getQuery')->willReturn($mockQuery);
        $mockStorage->method('loadMultiple')->willReturn($this->getMockComments());

        //Configure the EntityTypeManager to return the comment storage.
        $this->entityTypeManager->method('getStorage')->with('comment')->willReturn($mockStorage);

        $result = $this->userCommentStatsService->getLastComments($mockUser);

        
        $expected = [
            ['comment' => 'Comment body 1', 'node_title' => 'Node Title 1'],
            ['comment' => 'Comment body 2', 'node_title' => 'Node Title 2'],
        ];

        $this->assertEquals($expected, $result);
    }

    protected function getMockComments()
    {
        $mockComment1 = $this->createMock(\Drupal\comment\Entity\Comment::class);
        $mockComment1->method('get')->willReturn($this->createMockValue('Comment body 1'));
        $mockComment1->method('getCommentedEntity')->willReturn($this->getMockNode('Node Title 1'));

        $mockComment2 = $this->createMock(\Drupal\comment\Entity\Comment::class);
        $mockComment2->method('get')->willReturn($this->createMockValue('Comment body 2'));
        $mockComment2->method('getCommentedEntity')->willReturn($this->getMockNode('Node Title 2'));

        return [$mockComment1, $mockComment2];
    }

    protected function getMockNode($title)
    {
        $mockNode = $this->createMock(\Drupal\node\Entity\Node::class);
        $mockNode->method('getTitle')->willReturn($title);
        return $mockNode;
    }

    protected function createMockValue($value)
    {
        $mockValue = new \stdClass();
        $mockValue->value = $value;
        return $mockValue;
    }
}
