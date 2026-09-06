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
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class EventVoter.
 */
class EventVoter extends Voter
{
    /**
     * Edit permission.
     *
     * @var string
     */
    public const EDIT = 'EVENT_EDIT';

    /**
     * View permission.
     *
     * @var string
     */
    public const VIEW = 'EVENT_VIEW';
    /**
     * Delete permission.
     *
     * @var string
     */
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
        if (!$user instanceof UserInterface) {
            return false;
        }
        if (!$subject instanceof Event) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            self::VIEW => $this->canView($subject, $user),
            default => false,
        };
    }

    /**
     * Checks if user can delete event.
     *
     * @param Event          $event Event entity
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canDelete(Event $event, UserInterface $user): bool
    {
        return $event->getOwner() === $user;
    }

    /**
     * Checks if user can edit an event.
     *
     * @param Event          $event Event entity
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canEdit(Event $event, UserInterface $user): bool
    {
        return $event->getOwner() === $user;
    }

    /**
     * Checks if a user can view an event.
     *
     * @param Event          $event Event entity
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canView(Event $event, UserInterface $user): bool
    {
        return $event->getOwner() === $user;
    }
}
