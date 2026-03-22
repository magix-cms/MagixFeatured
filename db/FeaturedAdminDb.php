<?php
declare(strict_types=1);

namespace Plugins\MagixFeatured\db;

use App\Backend\Db\BaseDb;
use Magepattern\Component\Database\QueryBuilder;

class FeaturedAdminDb extends BaseDb
{
    /**
     * Recherche AJAX : Ne ramène que 10 résultats max correspondant à la frappe
     */
    public function searchActiveProducts(string $term, int $idLang): array
    {
        $qb = new QueryBuilder();
        $qb->select([
            'p.id_product', 'p.reference_p', 'c.name_p', 'cat_c.name_cat'
        ])
            ->from('mc_catalog_product', 'p')
            ->join('mc_catalog_product_content', 'c', 'p.id_product = c.id_product')
            ->join('mc_catalog', 'cat_rel', 'p.id_product = cat_rel.id_product AND cat_rel.default_c = 1')
            ->join('mc_catalog_cat_content', 'cat_c', 'cat_rel.id_cat = cat_c.id_cat AND cat_c.id_lang = c.id_lang')
            ->where('c.id_lang = :lang', ['lang' => $idLang])
            ->where('c.published_p = 1')
            ->where('(c.name_p LIKE :term OR p.reference_p LIKE :term)', ['term' => '%' . $term . '%'])
            ->limit(10); // Sécurité anti-surcharge

        return $this->executeAll($qb) ?: [];
    }

    /**
     * Récupère TOUTES les infos des produits DEJA sélectionnés, triés par position
     */
    public function getSelectedProductsFull(int $idLang): array
    {
        $qb = new QueryBuilder();
        $qb->select([
            'p.id_product', 'p.reference_p', 'c.name_p', 'cat_c.name_cat', 'feat.position'
        ])
            ->from('mc_plug_featured', 'feat')
            ->join('mc_catalog_product', 'p', 'feat.id_product = p.id_product')
            ->join('mc_catalog_product_content', 'c', 'p.id_product = c.id_product AND c.id_lang = ' . $idLang)
            ->leftJoin('mc_catalog', 'cat_rel', 'p.id_product = cat_rel.id_product AND cat_rel.default_c = 1')
            ->leftJoin('mc_catalog_cat_content', 'cat_c', 'cat_rel.id_cat = cat_c.id_cat AND cat_c.id_lang = ' . $idLang)
            ->orderBy('feat.position', 'ASC');

        return $this->executeAll($qb) ?: [];
    }

    public function saveFeaturedProducts(array $productIds): bool
    {
        $this->executeRawSql('TRUNCATE TABLE mc_plug_featured');
        if (empty($productIds)) return true;

        foreach ($productIds as $index => $id) {
            $qb = new QueryBuilder();
            $qb->insert('mc_plug_featured', [
                'id_product' => (int)$id,
                'position'   => $index // L'ordre du tableau $_POST détermine la position !
            ]);
            $this->executeInsert($qb);
        }
        return true;
    }
}