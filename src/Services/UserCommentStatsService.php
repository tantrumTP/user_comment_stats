<?php

namespace Drupal\user_comment_stats\Services;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Service to handle user comment statistics.
 */
class UserCommentStatsService
{

    /**
     *
     * @var \Drupal\Core\Session\AccountInterface
     */
    protected $currentUser;

    /**
     *
     * @var \Drupal\Core\Entity\EntityTypeManagerInterface
     */
    protected $entityTypeManager;

    /**
     *
     * @var \Drupal\Core\Routing\RouteMatchInterface
     */
    protected $routeMatch;

    /**
     *
     * @param \Drupal\Core\Session\AccountInterface $current_user
     *   The current user service.
     * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
     *   The entity type manager service.
     * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
     *   The route match service.
     */
    public function __construct(AccountInterface $current_user, EntityTypeManagerInterface $entity_type_manager, RouteMatchInterface $route_match)
    {
        $this->currentUser = $current_user;
        $this->entityTypeManager = $entity_type_manager;
        $this->routeMatch = $route_match;
    }

    /**
     * Get current user on current page
     *
     * @return \Drupal\Core\Session\AccountInterface
     *   The user present in the route or the logged user
     */
    public function getContextualUser()
    {
        $user = $this->routeMatch->getParameter('user');
        return $user instanceof AccountInterface ? $user : $this->currentUser;
    }

    /**
     * Get the total number of user comments.
     *
     * @param \Drupal\Core\Session\AccountInterface $user
     *   The user for whom to get the total number of comments.
     *
     * @return int
     *   The total number of comments.
     */
    public function getTotalComments(AccountInterface $user)
    {
        $query = $this->entityTypeManager->getStorage('comment')
            ->getQuery()
            ->condition('uid', $user->id())
            ->count()
            ->accessCheck(FALSE);
        return $query->execute();
    }

    /**
     * Gets the last 5 comments of a user and the associated node title.
     *
     * @param \Drupal\Core\Session\AccountInterface $user
     *   The user from whom to retrieve the comments.
     *
     * @return array|false
     *   An array with comments and node titles, or FALSE if no comments are found.
     */
    public function getLastComments(AccountInterface $user)
    {
        $commentIDs = $this->entityTypeManager->getStorage('comment')
            ->getQuery()
            ->condition('uid', $user->id())
            ->sort('created', 'DESC')
            ->range(0, 5)
            ->accessCheck(FALSE)
            ->execute();

        if ($commentIDs) {
            $comments = $this->entityTypeManager->getStorage('comment')->loadMultiple($commentIDs);
            $response = [];
            foreach ($comments as $comment) {
                /** @var \Drupal\comment\Entity\Comment $comment */
                $node = $comment->getCommentedEntity();
                $response[] = [
                    'comment' => $comment->get('comment_body')->value,
                    'node_title' => $node instanceof \Drupal\node\Entity\Node ? $node->getTitle() : ''
                ];
            }
            return $response;
        }

        return FALSE;
    }

    /**
     * Gets the total number of words in a user's comments.
     *
     * @param \Drupal\Core\Session\AccountInterface $user
     *   The user whose comment words will be counted.
     *
     * @return int
     *   The total number of words in the comments.
     */
    public function getTotalCommentsWords(AccountInterface $user)
    {
        $commentIDs = $this->entityTypeManager->getStorage('comment')
            ->getQuery()
            ->condition('uid', $user->id())
            ->accessCheck(FALSE)
            ->execute();

        $word_count = 0;
        if ($commentIDs) {
            $comments = $this->entityTypeManager->getStorage('comment')->loadMultiple($commentIDs);
            foreach ($comments as $comment) {
                /** @var \Drupal\comment\Entity\Comment $comment */
                $word_count += str_word_count($comment->get('comment_body')->value);
            }
        }
        return $word_count;
    }
}
