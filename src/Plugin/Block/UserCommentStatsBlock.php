<?php

namespace Drupal\user_comment_stats\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\comment\Entity\Comment;
use Drupal\node\Entity\Node;

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
        $service = \Drupal::service('user_comment_stats.user_comment_stats_service');
        if ($service) {
            $user = $service->getContextualUser();

            if (!$user || $user->isAnonymous()) {
                $response = [
                    '#markup' => $this->t('No user available.'),
                ];
            } else {
                $response = [
                    '#theme' => 'user_comment_stats',
                    '#total_comments' => $service->getTotalComments($user),
                    '#last_comments' => $service->getLastComments($user),
                    '#total_comments_words' => $service->getTotalCommentsWords($user),
                    '#attached' => [
                        'library' => [
                            'user_comment_stats/user_comment_stats_style'
                        ]
                    ],
                    '#cache' => [
                        'contexts' => ['user', 'url'],
                    ],
                ];
            }
        } else {
            $response = [
                '#markup' => $this->t('Block error'),
            ];
        }

        return $response;
    }
}
