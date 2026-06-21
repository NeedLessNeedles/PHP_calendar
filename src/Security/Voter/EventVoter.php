<?php

/**
 * Event voter.
 */

namespace App\Security\Voter;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class EventVoter.
 */
class EventVoter extends Voter
{
    public const EDIT = 'EVENT_EDIT';
    public const VIEW = 'EVENT_VIEW';
    public const DELETE = 'EVENT_DELETE';

    /**
     * Constructor.
     *
     * @param Security $security Admin Login security
     */
    public function __construct(private readonly Security $security)
    {
    }

    /**
     * Supports action.
     *
     * @param string $attribute Attribute
     * @param mixed  $subject   Subject
     *
     * @return bool Action
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Event
            && in_array($attribute, [
                self::EDIT,
                self::VIEW,
                self::DELETE,
            ], true);
    }

    /**
     * Vote on attribute action.
     *
     * @param string         $attribute Attribute
     * @param mixed          $subject   Subject
     * @param TokenInterface $token     Token
     *
     * @return bool Vote
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        /** @var Event $event */
        $event = $subject;

        if (!$user instanceof User) {
            if (self::VIEW === $attribute) {
                return true;
            }

            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if (self::VIEW === $attribute) {
            return true;
        }

        if (self::DELETE === $attribute) {
            return $event->getOwner() === $user
                && 'approved' === $event->getStatus();
        }

        if (self::EDIT === $attribute) {
            return $event->getOwner() === $user;
        }

        return false;
    }
}
