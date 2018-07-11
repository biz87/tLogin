<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;

    $dev = MODX_BASE_PATH . 'Extras/tLogin/';
    /** @var xPDOCacheManager $cache */
    $cache = $modx->getCacheManager();
    if (file_exists($dev) && $cache) {
        if (!is_link($dev . 'assets/components/tlogin')) {
            $cache->deleteTree(
                $dev . 'assets/components/tlogin/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_ASSETS_PATH . 'components/tlogin/', $dev . 'assets/components/tlogin');
        }
        if (!is_link($dev . 'core/components/tlogin')) {
            $cache->deleteTree(
                $dev . 'core/components/tlogin/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_CORE_PATH . 'components/tlogin/', $dev . 'core/components/tlogin');
        }
    }
}

return true;