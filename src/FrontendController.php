<?php
declare(strict_types=1);

namespace Plugins\MagixFeatured\src;

use Plugins\MagixFeatured\db\FeaturedFrontDb;
use App\Frontend\Db\ProductDb; // 🟢 Import du moteur centralisé des produits
use App\Frontend\Model\ProductPresenter;
use Magepattern\Component\Tool\SmartyTool;

class FrontendController
{
    public static function renderWidget(array $params = []): string
    {
        $currentLang = $params['current_lang'] ?? ['id_lang' => 1, 'iso_lang' => 'fr'];
        $idLang = (int)$currentLang['id_lang'];
        $siteUrl = $params['site_url'] ?? 'http://localhost';

        // 1. Le plugin récupère sa propre liste d'IDs dans l'ordre voulu
        $featuredDb = new FeaturedFrontDb();
        $productIds = $featuredDb->getFeaturedProductIds();

        if (empty($productIds)) {
            return ''; // Aucun produit phare configuré
        }

        // 2. On délègue toute la logique complexe au cœur du CMS !
        // (C'est ici que getProductsByIds va déclencher le hook 'extendProductList'
        // et récupérer les éventuelles colonnes bonus des autres plugins)
        $productDb = new ProductDb();
        $rawProducts = $productDb->getProductsByIds($productIds, $idLang);

        if (empty($rawProducts)) {
            return '';
        }

        // 3. Formatage via le Presenter (qui attrape automatiquement les champs bonus)
        $formattedProducts = [];
        foreach ($rawProducts as $row) {
            $formatted = ProductPresenter::format($row, $currentLang, $siteUrl);
            if ($formatted) {
                $formattedProducts[] = $formatted;
            }
        }

        // 4. Envoi à Smarty
        $view = SmartyTool::getInstance('front');
        $view->assign('featured_products', $formattedProducts);

        return $view->fetch(ROOT_DIR . 'plugins/MagixFeatured/views/front/widget.tpl');
    }
}