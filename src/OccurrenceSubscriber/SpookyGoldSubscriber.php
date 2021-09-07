<?php

/**
 * This file is part of "LoTGD Bundle Spooky Gold".
 *
 * @see https://github.com/lotgd-core/spooky-gold-bundle
 *
 * @license https://github.com/lotgd-core/spooky-gold-bundle/blob/master/LICENSE.txt
 * @author IDMarinas
 *
 * @since 0.1.0
 */

namespace Lotgd\Bundle\SpookyGoldBundle\OccurrenceSubscriber;

use Lotgd\Bundle\SpookyGoldBundle\LotgdSpookyGoldBundle;
use Lotgd\Bundle\SpookyGoldBundle\Pattern\ModuleUrlTrait;
use Lotgd\Core\Http\Response;
use Lotgd\Core\Log;
use Lotgd\Core\Navigation\Navigation;
use Lotgd\CoreBundle\OccurrenceBundle\OccurrenceSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Twig\Environment;

class SpookyGoldSubscriber implements OccurrenceSubscriberInterface
{
    use ModuleUrlTrait;

    public const TRANSLATION_DOMAIN = LotgdSpookyGoldBundle::TRANSLATION_DOMAIN;

    private $response;
    private $twig;
    private $navigation;

    public function __construct(
        Response $response,
        Environment $twig,
        Navigation $navigation
    ) {
        $this->response   = $response;
        $this->twig       = $twig;
        $this->navigation = $navigation;
    }

    public function village(GenericEvent $event)
    {
        $query = sprintf(
            '&translation_domain=%s&translation_domain_navigation=%s&navigation_method=%s',
            $event->getArgument('translation_domain'),
            $event->getArgument('translation_domain_navigation'),
            $event->hasArgument('navigation_method') ? $event->getArgument('navigation_method') : '',
        );

        $this->navigation->setTextDomain(self::TRANSLATION_DOMAIN);

        $this->navigation->addNav('navigation.nav.default.go', $this->getModuleUrl('alley', $query));
        $this->navigation->addNav('navigation.nav.default.ignore', $this->getModuleUrl('ignore', $query));

        $this->navigation->setTextDomain();

        $this->response->pageAddContent($this->twig->render('@LotgdSpookyGold/activation.html.twig', [
            'translation_domain' => self::TRANSLATION_DOMAIN
        ]));

        $event->stopPropagation();
    }

    public static function getSubscribedOccurrences()
    {
        return [
            'village' => ['village', 10000, OccurrenceSubscriberInterface::PRIORITY_ANSWER],
            // 'village' => ['village', 2500, OccurrenceSubscriberInterface::PRIORITY_ANSWER],
        ];
    }
}
