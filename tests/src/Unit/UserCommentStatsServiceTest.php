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
}
