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

namespace Lotgd\Bundle\SpookyGoldBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('lotgd_spooky_gold');

        $treeBuilder->getRootNode()
            ->addDefaultsIfNotSet()
            ->children()
                ->integerNode('cowardice')
                    ->defaultValue(10)
                    ->min(0)
                    ->max(100)
                    ->info('Percentage of times running away for something bad to happen')
                ->end()
                ->integerNode('max_visits')
                    ->defaultValue(3)
                    ->min(1)
                    ->info('Number of times allowed to visit the alley per day')
                ->end()
                ->integerNode('beast')
                    ->defaultValue(10)
                    ->min(0)
                    ->max(100)
                    ->info('Percentage of times that the beast will attack')
                ->end()
                ->integerNode('cache')
                    ->defaultValue(10)
                    ->min(0)
                    ->max(100)
                    ->info('Chance of finding a cache of gems or gold')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
