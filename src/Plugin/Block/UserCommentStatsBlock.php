<?php

namespace Drupal\user_comment_stats\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\comment\Entity\Comment;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'User Comment Stats' Block.
 *
 * @Block(
 *   id = "user_comment_stats_block",
 *   admin_label = @Translation("User Comment Stats Block"),
 * )
 */

class UserCommentStatsBlock extends BlockBase
{
    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $response = [];
        $user = $this->getContextualUser();

        if (!$user) {
            $response = [
                '#markup' => $this->t('No user available.'),
            ];
        } else {
            $totalComments = $this->getTotalComments($user);
            $lastComments = $this->getLastComments($user);
            //TODO: Get total number of words from all user comments
            exit;
        }

        return $response;
    }

    /**
     * 
     * Get the user based oon the context of the page
     */
    protected function getContextualUser()
    {
        $route_match = \Drupal::routeMatch();
        return $route_match->getParameter('user') ?? \Drupal::currentUser();
    }

    /**
     * 
     * Get the total number of user comments
     */
    protected function getTotalComments(AccountInterface $user)
    {
        $query = \Drupal::entityQuery('comment')
            ->condition('uid', $user->id())
            ->count()
            ->accessCheck(FALSE);
        return $query->execute();
    }


    /**
     * Get last 5 comments of the user and the associated node title
     */
    protected function getLastComments(AccountInterface $user)
    {
        $commentIDs = \Drupal::entityQuery('comment')
            ->condition('uid', $user->id())
            ->sort('created', 'DESC')
            ->range(0, 5)
            ->accessCheck(FALSE)
            ->execute();

        if ($commentIDs) {
            $comments = Comment::loadMultiple($commentIDs);
            $response = [];
            foreach ($comments as $comment) {
                $node = $comment->getCommentedEntity();
                $response[] = [
                    'comment' => $comment->get('comment_body')->value,
                    'node_title' => $node instanceof Node ? $node->getTitle() : ''
                ];
            }
        } else {
            $response = FALSE;
        }

        return $response;
    }
}
