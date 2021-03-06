<?php

namespace App\EventSubscriber;

use App\Repository\UserRepository;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;

class SitemapSubscriber implements EventSubscriberInterface
{
    private $urlGenerator;
    private $userRepository;
    private $locales;

    public function __construct(UrlGeneratorInterface $urlGenerator, UserRepository $userRepository, array $locales)
    {
        $this->urlGenerator = $urlGenerator;
        $this->userRepository = $userRepository;
        $this->locales = $locales;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SitemapPopulateEvent::ON_SITEMAP_POPULATE => 'populate',
        ];
    }

    public function populate(SitemapPopulateEvent $event): void
    {
        $this->registerUsersUrls($event->getUrlContainer());
        $this->registerStaticUrls($event->getUrlContainer());
    }

    public function registerUsersUrls(UrlContainerInterface $urls): void
    {
        $users = $this->userRepository->findByEnabled(true);

        foreach ($users as $user) {
            foreach ($this->locales as $locale) {
                $urls->addUrl(
                    new UrlConcrete(
                        $this->urlGenerator->generate(
                            'app_profile',
                            ['slug' => $user->getSlug(), '_locale' => $locale],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        )
                    ),
                    "profile_$locale"
                );
            }
        }
    }

    public function registerStaticUrls(UrlContainerInterface $urls): void
    {
        $static_routers = [
            'app_homepage',
            'fos_user_registration_register',
            'fos_user_security_login',
        ];

        foreach ($static_routers as $static_router) {
            foreach ($this->locales as $locale) {
                $urls->addUrl(
                    new UrlConcrete(
                        $this->urlGenerator->generate(
                            $static_router,
                            ['_locale' => $locale],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        )
                    ),
                    "static_$locale"
                );
            }
        }

    }
}