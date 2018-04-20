<?php

/*
 * This file has been created by developers from BitBag. 
 * Feel free to contact us once you face any issues or want to start
 * another great project. 
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl. 
 */

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Context;

use BitBag\SyliusWishlistPlugin\Factory\WishlistFactoryInterface;
use BitBag\SyliusWishlistPlugin\Model\WishlistInterface;
use BitBag\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class WishlistContext implements WishlistContextInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var WishlistRepositoryInterface */
    private $wishlistRepository;

    /** @var WishlistFactoryInterface */
    private $wishlistFactory;

    /** @var string */
    private $wishlistCookieId;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        WishlistRepositoryInterface $wishlistRepository,
        WishlistFactoryInterface $wishlistFactory,
        string $wishlistCookieId
    )
    {
        $this->tokenStorage = $tokenStorage;
        $this->wishlistRepository = $wishlistRepository;
        $this->wishlistFactory = $wishlistFactory;
        $this->wishlistCookieId = $wishlistCookieId;
    }

    public function getWishlist(Request $request): WishlistInterface
    {
        $cookieWishlistId = $request->cookies->get($this->wishlistCookieId);
        $user = $this->tokenStorage->getToken()->getUser();

        if (null === $cookieWishlistId && null === $user) {
            return $this->wishlistFactory->createNew();
        }

        if (null !== $cookieWishlistId && null === $user) {
            return $this->wishlistRepository->find($cookieWishlistId);
        }

        if ($user instanceof ShopUserInterface) {
            return $this->wishlistRepository->findByShopUser($user) ?
                $this->wishlistRepository->findByShopUser($user) :
                $this->wishlistFactory->createForUser($user)
            ;
        }
    }
}
