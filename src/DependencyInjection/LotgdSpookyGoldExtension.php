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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class LotgdSpookyGoldExtension extends ConfigurableExtension
{
    public function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));

        $loader->load('services.php');

        $container->setParameter('lotgd_bundle.spooky_gold.cowardice', $mergedConfig['cowardice']);
        $container->setParameter('lotgd_bundle.spooky_gold.max_visits', $mergedConfig['max_visits']);
        $container->setParameter('lotgd_bundle.spooky_gold.beast', $mergedConfig['beast']);
        $container->setParameter('lotgd_bundle.spooky_gold.cache', $mergedConfig['cache']);
    }
}
