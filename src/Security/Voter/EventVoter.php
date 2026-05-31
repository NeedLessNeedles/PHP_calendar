<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Event;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

final class EventVoter extends Voter
{

    public function __construct(
        private Security $security
    ) {
    }
    public const EDIT = 'EVENT_EDIT';
    public const VIEW = 'EVENT_VIEW';
    public const DELETE = 'EVENT_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
                self::VIEW,
                self::EDIT,
                self::DELETE,
            ]) && $subject instanceof Event;
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
        ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$this->security->getUser() instanceof User) {
            return false;
        }

        /** @var Event $event */
        $event = $subject;

        // Admin can do everything
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return $event->getOwner() === $user;
    }
}
