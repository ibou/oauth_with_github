<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use League\OAuth2\Client\Provider\GithubResourceOwner;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }


    public function findOrCreateFromOauth(GithubResourceOwner $owner)
    {
        /** @var User|null $user */
        $user = $this->createQueryBuilder('u')
            ->where('u.githubId = :githubId')
            ->orWhere('u.email = :email')
            ->setParameters(
                [
                    'githubId' => $owner->getId(),
                    'email' => $owner->getEmail(),
                ]
            )
            ->getQuery()
            ->getOneOrNullResult();

        if ($user) {
            if (null === $user->getEmail()) {
                $user->setEmail($owner->getEmail());
                $this->_em->persist($user);
                $this->_em->flush();
            }
            if (null === $user->getGithubId()) {
                $user->setGithubId($owner->getId());
                $this->_em->persist($user);
                $this->_em->flush();
            }
            return $user;
        }

        $user = (new User())
            ->setEmail($owner->getEmail())
            ->setGithubId($owner->getId());

        $this->_em->persist($user);
        $this->_em->flush();

        return $user;
    }
}
