<?php
declare(strict_types=1);

namespace Plugins\MagixFeatured\src;

use App\Backend\Controller\BaseController;
use Plugins\MagixFeatured\db\FeaturedAdminDb;
use Magepattern\Component\HTTP\Request;

class BackendController extends BaseController
{
    public function run(): void
    {
        $db = new FeaturedAdminDb();
        $idLang = (int)($this->defaultLang['id_lang'] ?? 1);

        // 🟢 INTERCEPTION DE LA RECHERCHE AJAX
        if (isset($_GET['action']) && $_GET['action'] === 'search') {
            $term = $_GET['q'] ?? '';
            if (strlen($term) > 1) {
                $results = $db->searchActiveProducts($term, $idLang);
                echo json_encode($results);
            } else {
                echo json_encode([]);
            }
            exit; // On stoppe le script pour ne renvoyer que le JSON
        }

        // 🟢 SAUVEGARDE DU FORMULAIRE
        if (Request::isMethod('POST')) {
            $token = $_POST['hashtoken'] ?? '';
            if (!$this->session->validateToken($token)) {
                $this->jsonResponse(false, 'Session expirée.');
            }

            // Les IDs arriveront dans l'ordre exact où ils sont dans le DOM HTML
            $selectedIds = $_POST['featured_products'] ?? [];
            if ($db->saveFeaturedProducts($selectedIds)) {
                $this->jsonResponse(true, 'Produits phares mis à jour avec succès.');
            } else {
                $this->jsonResponse(false, 'Erreur de sauvegarde.');
            }
            return;
        }

        // 🟢 AFFICHAGE DE LA PAGE (On n'envoie QUE les produits sélectionnés)
        $this->view->assign([
            'selected_products' => $db->getSelectedProductsFull($idLang),
            'hashtoken'         => $this->session->getToken()
        ]);

        $this->view->display('index.tpl');
    }
}