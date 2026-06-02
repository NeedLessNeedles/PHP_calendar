<?php

namespace App\Security\Voter;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class EventVoter extends Voter
{
    public const EDIT = 'EVENT_EDIT';
    public const VIEW = 'EVENT_VIEW';
    public const DELETE = 'EVENT_DELETE';

    public function __construct(
        private Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Event
            && in_array($attribute, [
                self::EDIT,
                self::VIEW,
                self::DELETE,
            ], true);
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
    ): bool {
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
