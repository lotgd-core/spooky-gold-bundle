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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Lotgd\Bundle\SpookyGoldBundle\Controller\SpookyGoldController;
use Lotgd\Bundle\SpookyGoldBundle\EventSubscriber\SpookyGoldSubscriber;
use Lotgd\Bundle\SpookyGoldBundle\OccurrenceSubscriber\SpookyGoldSubscriber as OccurrenceSubscriberSpookyGoldSubscriber;
use Lotgd\Core\Http\Response;
use Lotgd\Core\Lib\Settings;
use Lotgd\Core\Navigation\Navigation;

return static function (ContainerConfigurator $container)
{
    $container->services()
        //-- Controller
        ->set(SpookyGoldController::class)
            ->args([
                new ReferenceConfigurator(Navigation::class),
                new ReferenceConfigurator(Response::class),
                new ReferenceConfigurator(Settings::class),
                new ReferenceConfigurator('lotgd.core.tools'),
                new ReferenceConfigurator('lotgd.core.log'),
                new ReferenceConfigurator('parameter_bag'),
                new ReferenceConfigurator('translator')
            ])
            ->call('setContainer', [
                new ReferenceConfigurator('service_container'),
            ])
            ->call('setServiceBattle', [
                new ReferenceConfigurator('lotgd_core.combat.battle'),
            ])
            ->call('setServiceCreatureFunction', [
                new ReferenceConfigurator('lotgd_core.tool.creature_functions'),
            ])
            ->tag('controller.service_arguments')

        //-- Event Subscribers
        ->set(SpookyGoldSubscriber::class)
            ->tag('kernel.event_subscriber')

        //-- Occurrence Subscribers
        ->set(OccurrenceSubscriberSpookyGoldSubscriber::class)
            ->args([
                new ReferenceConfigurator(Response::class),
                new ReferenceConfigurator('twig'),
                new ReferenceConfigurator(Navigation::class),
            ])
            ->tag('lotgd_core.occurrence_subscriber')
    ;
};
