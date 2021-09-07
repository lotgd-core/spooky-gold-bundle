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

namespace Lotgd\Bundle\SpookyGoldBundle\Controller;

use Lotgd\Bundle\SpookyGoldBundle\LotgdSpookyGoldBundle;
use Lotgd\Bundle\SpookyGoldBundle\Pattern\ModuleUrlTrait;
use Lotgd\Core\Combat\Battle;
use Lotgd\Core\Http\Request;
use Lotgd\Core\Http\Response as HttpResponse;
use Lotgd\Core\Lib\Settings;
use Lotgd\Core\Log;
use Lotgd\Core\Navigation\Navigation;
use Lotgd\Core\Tool\CreatureFunction;
use Lotgd\Core\Tool\Tool;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class SpookyGoldController extends AbstractController
{
    use ModuleUrlTrait;

    public const TRANSLATION_DOMAIN = LotgdSpookyGoldBundle::TRANSLATION_DOMAIN;

    private $navigation;
    private $response;
    private $settings;
    private $tool;
    private $log;
    private $parameter;
    private $translator;
    private $serviceBattle;
    private $serviceCreatureFunction;

    public function __construct(
        Navigation $navigation,
        HttpResponse $response,
        Settings $settings,
        Tool $tool,
        Log $log,
        ParameterBagInterface $parameter,
        TranslatorInterface $translator
    ) {
        $this->navigation = $navigation;
        $this->response   = $response;
        $this->settings   = $settings;
        $this->tool       = $tool;
        $this->log        = $log;
        $this->parameter  = $parameter;
        $this->translator = $translator;
    }

    public function alley(Request $request): Response
    {
        $this->response->pageTitle('title.alley', [], self::TRANSLATION_DOMAIN);

        $params['award'] = mt_rand(0, 1); //-- 0 = gem, 1 = gold

        $query = sprintf(
            '&translation_domain=%s&translation_domain_navigation=%s&navigation_method=%s',
            $request->query->get('translation_domain', ''),
            $request->query->get('translation_domain_navigation', ''),
            $request->query->get('navigation_method', ''),
        );

        $this->navigation->setTextDomain(self::TRANSLATION_DOMAIN);

        if ( ! $params['award'])
        {
            $this->navigation->addNav('navigation.nav.alley.gem', $this->getModuleUrl('pickupGem', $query));
        }
        else
        {
            $this->navigation->addNav('navigation.nav.alley.gold', $this->getModuleUrl('pickupGold', $query));
        }

        $this->navigation->addNav('navigation.nav.alley.run', $this->getModuleUrl('dontPickup', $query));

        $this->navigation->setTextDomain();

        return $this->render('@LotgdSpookyGold/alley.html.twig', $this->addParameters($params));
    }

    public function pickupGem(Request $request): Response
    {
        global $session;

        $this->response->pageTitle('title.pickup.gem', [], self::TRANSLATION_DOMAIN);

        $nav = $request->query->get('navigation_method', '');

        $params['cache'] = false;
        $diceroll        = mt_rand(1, 100);
        $beastchance     = $this->parameter->get('lotgd_bundle.spooky_gold.beast');
        $cachechance     = $this->parameter->get('lotgd_bundle.spooky_gold.cache');
        $visits          = get_module_pref('visits', 'bundle_spooky_gold');

        if ($diceroll <= $beastchance)
        {
            $this->addFlash('warning', $this->translator->trans('flash.message.battle.fight', [], self::TRANSLATION_DOMAIN));

            return $this->fight($request, true);
        }
        elseif ($diceroll < (100 - $cachechance))
        {
            $params['cache'] = false;

            $this->log->debug('found a gem in the spooky alley');
            ++$session['user']['gems'];

            ++$visits;
            set_module_pref('visits', $visits, 'bundle_spooky_gold');

            if (method_exists($this->navigation, $nav))
            {
                $this->navigation->{$nav}($request->query->get('translation_domain_navigation', ''));
            }
        }
        else
        {
            $params['cache'] = true;

            $this->log->debug('found a cache of 5 gems in the spooky alley');
            $session['user']['gems'] += 5;

            ++$visits;
            set_module_pref('visits', $visits, 'bundle_spooky_gold');

            if (method_exists($this->navigation, $nav))
            {
                $this->navigation->{$nav}($request->query->get('translation_domain_navigation', ''));
            }
        }

        return $this->render('@LotgdSpookyGold/pickup_gem.html.twig', $this->addParameters($params));
    }

    public function pickupGold(Request $request): Response
    {
        global $session;

        $this->response->pageTitle('title.pickup.gold', [], self::TRANSLATION_DOMAIN);

        $nav = $request->query->get('navigation_method', '');

        if (method_exists($this->navigation, $nav))
        {
            $this->navigation->{$nav}($request->query->get('translation_domain_navigation', ''));
        }

        $diceroll    = mt_rand(1, 100);
        $cachechance = $this->parameter->get('lotgd_bundle.spooky_gold.cache');
        $visits      = get_module_pref('visits', 'bundle_spooky_gold');

        $params['cache'] = false;
        $params['gold']  = 1;

        if ($diceroll > $cachechance)
        {
            $this->log->debug('found a gold piece in the spooky alley');
            ++$session['user']['gold'];

            ++$visits;
            set_module_pref('visits', $visits, 'bundle_spooky_gold');
        }
        else
        {
            $params['cache'] = true;

            $params['gold'] = $session['user']['level'] * mt_rand(159, 211);

            $this->log->debug("found a cache of {$params['gold']} in the spooky alley");

            $session['user']['gold'] += $params['gold'];

            ++$visits;
            set_module_pref('visits', $visits, 'bundle_spooky_gold');
        }

        return $this->render('@LotgdSpookyGold/pickup_gold.html.twig', $this->addParameters($params));
    }

    public function dontPickup(Request $request): Response
    {
        global $session;

        $this->response->pageTitle('title.pickup.nope', [], self::TRANSLATION_DOMAIN);

        $nav = $request->query->get('navigation_method', '');

        if (method_exists($this->navigation, $nav))
        {
            $this->navigation->{$nav}($request->query->get('translation_domain_navigation', ''));
        }

        $cowardicechance = $this->parameter->get('lotgd_bundle.spooky_gold.cowardice');
        $wimpychance     = mt_rand(1, 100);
        $visits          = get_module_pref('visits', 'bundle_spooky_gold');

        $params['coward'] = false;

        if ($wimpychance <= $cowardicechance)
        {
            $params['coward'] = true;
            $session['user']['charm'] -= 2;
            $session['user']['charm'] = max($session['user']['charm'], 0);
        }

        ++$visits;
        set_module_pref('visits', $visits, 'bundle_spooky_gold');

        return $this->render('@LotgdSpookyGold/dont_pickup.html.twig', $this->addParameters($params));
    }

    public function ignore(Request $request): Response
    {
        $this->response->pageTitle('title.ignore', [], self::TRANSLATION_DOMAIN);

        $nav = $request->query->get('navigation_method', '');

        if (method_exists($this->navigation, $nav))
        {
            $this->navigation->{$nav}($request->query->get('translation_domain_navigation', ''));
        }

        return $this->render('@LotgdSpookyGold/ignore.html.twig', $this->addParameters([]));
    }

    public function fight(Request $request, bool $generateCreature = false)
    {
        global $session;

        $this->response->pageTitle('title.fight', [], self::TRANSLATION_DOMAIN);

        $query = sprintf(
            '&translation_domain=%s&translation_domain_navigation=%s&navigation_method=%s',
            $request->query->get('translation_domain', ''),
            $request->query->get('translation_domain_navigation', ''),
            $request->query->get('navigation_method', ''),
        );

        if ($generateCreature)
        {
            $badguy = [
                'creaturename'    => $this->translator->trans('badguy.name', [], self::TRANSLATION_DOMAIN),
                'creaturelevel'   => $session['user']['level'] + 2,
                'creatureweapon'  => $this->translator->trans('badguy.weapon', [], self::TRANSLATION_DOMAIN),
                'creatureattack'  => $session['user']['attack'],
                'creaturedefense' => $session['user']['defense'],
                'diddamage'       => 0,
                'type'            => 'bonemarrow',
            ];
            $badguy = $this->serviceCreatureFunction->lotgdTransformCreature($badguy, false);

            $session['user']['badguy'] = ['enemies' => [$badguy]];
            $request->query->set('op', 'fight');
        }

        if ('run' == $request->query->get('op', ''))
        {
            $this->addFlash('warning', $this->translator->trans('flash.message.battle.run', [], self::TRANSLATION_DOMAIN));
            $request->query->set('op', 'fight');
        }

        if ('fight' == $request->query->get('op', ''))
        {
            $battle = true;
        }

        if ($battle)
        {
            //-- Battle zone.
            $this->serviceBattle->initialize()
                ->setBattleZone('bonemarrow')
                ->battleStart()
                ->battleProcess()
                ->battleEnd()
            ;

            if ($this->serviceBattle->isVictory())
            {
                $battle = false;

                $badguy = $this->serviceBattle->getEnemies()[0];

                if ($session['user']['hitpoints'] <= 0)
                {
                    $this->serviceBattle->addContextToBattleEnd([
                        'battle.end.victory.hitpoints',
                        [
                            'creatureName' => $badguy['creaturename'],
                        ],
                        self::TRANSLATION_DOMAIN,
                    ]);

                    $session['user']['hitpoints'] = 1;
                }

                $this->serviceBattle->addContextToBattleEnd([
                    'battle.end.victory.paragraph',
                    [
                        'creatureName' => $badguy['creaturename'],
                    ],
                    self::TRANSLATION_DOMAIN,
                ]);

                $this->navigation->setTextDomain(self::TRANSLATION_DOMAIN);
                $this->navigation->addNav('navigation.nav.alley.gem', $this->getModuleUrl('pickupGem', $query));
                $this->navigation->addNav('navigation.nav.alley.run', $this->getModuleUrl('dontPickup', $query));
                $this->navigation->setTextDomain();
            }
            elseif ($this->serviceBattle->isDefeat())
            {
                $battle = false;

                $badguy = $this->serviceBattle->getEnemies()[0];

                $this->tool->addNews('news.battle.defeated', [
                    'playerName' => $session['user']['name'],
                ], self::TRANSLATION_DOMAIN);

                $this->log->debug('lost to Bonemarrow Beast');

                $session['user']['hitpoints'] = 1;

                $this->serviceBattle->addContextToBattleEnd([
                    'battle.end.defeated.paragraph',
                    [
                        'creatureName' => $badguy['creaturename'],
                    ],
                    self::TRANSLATION_DOMAIN,
                ]);

                if (is_module_active('staminasystem'))
                {
                    require_once 'modules/staminasystem/lib/lib.php';

                    removestamina(5 * 25000);

                    $this->serviceBattle->addContextToBattleEnd(['battle.end.defeated.stamina', [], self::TRANSLATION_DOMAIN]);
                }
                else
                {
                    $session['user']['turns'] -= 5;
                    $session['user']['turns'] = max($session['user']['turns'], 0);
                    $this->serviceBattle->addContextToBattleEnd(['battle.end.defeated.turns', [], self::TRANSLATION_DOMAIN]);
                }

                if ($session['user']['charm'] > 0)
                {
                    --$session['user']['charm'];
                    $this->serviceBattle->addContextToBattleEnd(['battle.end.defeated.charm.lost', [], self::TRANSLATION_DOMAIN]);
                }
                else
                {
                    $this->serviceBattle->addContextToBattleEnd(['battle.end.defeated.charm.equal', [], self::TRANSLATION_DOMAIN]);
                }

                $nav = $request->query->get('navigation_method', '');

                if (method_exists($this->navigation, $nav))
                {
                    $this->navigation->{$nav}($request->query->get('translation_domain_navigation', ''));
                }
            }
            elseif ( ! $this->serviceBattle->battleHasWinner())
            {
                $this->serviceBattle->fightNav(true, true, $this->getModuleUrl('fight', $query));
            }
        }

        $response = new Response($this->serviceBattle->battleResults(true));

        return $response;
    }

    public function setServiceCreatureFunction(CreatureFunction $service): self
    {
        $this->serviceCreatureFunction = $service;

        return $this;
    }

    public function setServiceBattle(Battle $battle): self
    {
        $this->serviceBattle = $battle;

        return $this;
    }

    private function addParameters(array $params): array
    {
        $params['translation_domain'] = self::TRANSLATION_DOMAIN;
        $params['partner']            = $this->tool->getPartner();

        return $params;
    }
}
