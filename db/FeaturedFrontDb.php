<?php
declare(strict_types=1);

namespace Plugins\MagixFeatured\db;

use App\Frontend\Db\BaseDb;
use Magepattern\Component\Database\QueryBuilder;

class FeaturedFrontDb extends BaseDb
{
    /**
     * Récupère UNIQUEMENT la liste des IDs des produits phares,
     * triés par la position définie dans l'administration du plugin.
     */
    public function getFeaturedProductIds(): array
    {
        $qb = new QueryBuilder();
        $qb->select(['id_product'])
            ->from('mc_plug_featured')
            ->orderBy('position', 'ASC');

        $results = $this->executeAll($qb) ?: [];

        // Transforme le résultat [['id_product' => 5], ['id_product' => 12]]
        // en un tableau simple [5, 12]
        return array_column($results, 'id_product');
    }
}