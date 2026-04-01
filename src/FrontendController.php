<?php
declare(strict_types=1);

namespace Plugins\MagixFeaturedProduct\src;

use Plugins\MagixFeaturedProduct\db\FeaturedFrontDb;
use App\Frontend\Db\ProductDb;
use App\Frontend\Model\ProductPresenter;
use Magepattern\Component\Tool\SmartyTool;

class FrontendController
{
    public static function renderWidget(array $params = []): string
    {
        // 🟢 1. SÉCURITÉ / AIGUILLAGE
        $hookName = $params['name'] ?? '';
        if (!str_starts_with($hookName, 'displayHome')) {
            return '';
        }

        // 🟢 2. TRAITEMENT NORMAL
        try {
            $view = SmartyTool::getInstance('front');

            $currentLang = $view->getTemplateVars('current_lang') ?: ['id_lang' => 1, 'iso_lang' => 'fr'];
            $idLang      = (int)$currentLang['id_lang'];
            $siteUrl     = $view->getTemplateVars('site_url') ?: 'http://localhost';

            $companyInfo = $view->getTemplateVars('companyData') ?: [];
            $mcSettings  = $view->getTemplateVars('mc_settings') ?: [];
            $skinFolder  = $mcSettings['theme']['value'] ?? 'default';

            $featuredDb = new FeaturedFrontDb();
            $productIds = $featuredDb->getFeaturedProductIds();

            if (empty($productIds)) return '';

            if (!method_exists(ProductDb::class, 'getProductsByIds')) {
                return '';
            }

            $productDb = new ProductDb();
            $rawProducts = $productDb->getProductsByIds($productIds, $idLang);

            if (empty($rawProducts)) return '';

            $formattedProducts = [];
            foreach ($rawProducts as $row) {
                $formatted = ProductPresenter::format($row, $currentLang, $siteUrl, $companyInfo, $skinFolder, $mcSettings);
                if ($formatted) {
                    $formattedProducts[] = $formatted;
                }
            }

            $view->assign('featured_products', $formattedProducts);

            $templatePath = ROOT_DIR . 'plugins/MagixFeaturedProduct/views/front/widget.tpl';
            if (!file_exists($templatePath)) return '';

            return $view->fetch($templatePath);

        } catch (\Throwable $e) {
            return '';
        }
    }
}