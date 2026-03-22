{extends file="layout.tpl"}

{block name='head:title'}Configuration Produits Phares{/block}

{block name='article'}
    <div class="row">
        {* COLONNE DE GAUCHE : LA RECHERCHE *}
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-search"></i> Ajouter un produit</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 position-relative">
                        <label class="form-label text-muted small">Rechercher (Nom ou Référence)</label>
                        <input type="text" id="ajaxSearchInput" class="form-control" placeholder="Taper au moins 2 caractères..." autocomplete="off">

                        {* Container des résultats AJAX *}
                        <div id="ajaxSearchResults" class="list-group position-absolute w-100 shadow mt-1" style="z-index: 1050; display: none; max-height: 250px; overflow-y: auto;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {* COLONNE DE DROITE : LES SÉLECTIONNÉS *}
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-star-fill text-warning"></i> Produits en page d'accueil</h5>
                    <span class="badge bg-primary" id="countSelected">{$selected_products|count}</span>
                </div>
                <div class="card-body">
                    <form method="post" action="index.php?controller=MagixFeatured" class="validate_form">
                        <input type="hidden" name="hashtoken" value="{$hashtoken}">

                        <div class="alert alert-info py-2 small">
                            <i class="bi bi-info-circle me-1"></i> Utilisez les flèches pour modifier l'ordre d'affichage.
                        </div>

                        {* La liste des produits choisis *}
                        {* La liste des produits choisis (Draggable) *}
                        <ul class="list-group mb-4" id="selectedProductsList">
                            {foreach $selected_products as $p}
                                <li class="list-group-item d-flex justify-content-between align-items-center bg-light border-bottom cursor-move" draggable="true" data-id="{$p.id_product}">
                                    <input type="hidden" name="featured_products[]" value="{$p.id_product}">
                                    <div class="d-flex align-items-center w-100">
                                        <i class="bi bi-grip-vertical text-muted me-3 fs-5"></i>
                                        <div>
                                            <strong class="d-block text-dark">{$p.name_p}</strong>
                                            <small class="text-muted">Réf: {$p.reference_p|default:'N/A'} - {$p.name_cat}</small>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove ms-2" title="Retirer"><i class="bi bi-x-lg"></i></button>
                                </li>
                            {/foreach}
                        </ul>

                        {* On garde le bouton de soumission au cas où, ou comme indicateur visuel, mais il n'est plus strictement nécessaire *}
                        <div id="saveIndicator" class="text-success text-center fw-bold" style="display: none;">
                            <i class="bi bi-check-circle"></i> Sauvegardé automatiquement
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
{/block}

{block name="javascripts" append}
    <script>
        {literal}
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('ajaxSearchInput');
            const searchResults = document.getElementById('ajaxSearchResults');
            const selectedList = document.getElementById('selectedProductsList');
            const countBadge = document.getElementById('countSelected');
            const saveIndicator = document.getElementById('saveIndicator');
            const token = document.querySelector('input[name="hashtoken"]').value;

            let searchTimeout = null;
            let draggedItem = null;

            // ==========================================
            // 1. RECHERCHE AJAX (Reste identique)
            // ==========================================
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const term = this.value.trim();
                if (term.length < 2) { searchResults.style.display = 'none'; return; }

                searchTimeout = setTimeout(() => {
                    fetch('index.php?controller=MagixFeatured&action=search&q=' + encodeURIComponent(term))
                        .then(response => response.json())
                        .then(data => {
                            searchResults.innerHTML = '';
                            if (data.length === 0) {
                                searchResults.innerHTML = '<div class="list-group-item text-muted small">Aucun produit trouvé.</div>';
                            } else {
                                data.forEach(product => {
                                    const isAdded = document.querySelector(`li[data-id="${product.id_product}"]`);
                                    const a = document.createElement('a');
                                    a.href = 'javascript:void(0)';
                                    a.className = `list-group-item list-group-item-action ${isAdded ? 'disabled bg-light' : ''}`;
                                    a.innerHTML = `<strong>${product.name_p}</strong><br><small class="text-muted">Réf: ${product.reference_p || 'N/A'}</small>`;
                                    if (!isAdded) a.addEventListener('click', () => addProductToList(product));
                                    searchResults.appendChild(a);
                                });
                            }
                            searchResults.style.display = 'block';
                        });
                }, 300);
            });

            document.addEventListener('click', e => {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) searchResults.style.display = 'none';
            });

            // ==========================================
            // 2. AJOUTER / SUPPRIMER
            // ==========================================
            function addProductToList(product) {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center bg-light border-bottom cursor-move';
                li.setAttribute('draggable', 'true');
                li.setAttribute('data-id', product.id_product);
                li.innerHTML = `
            <input type="hidden" name="featured_products[]" value="${product.id_product}">
            <div class="d-flex align-items-center w-100">
                <i class="bi bi-grip-vertical text-muted me-3 fs-5"></i>
                <div>
                    <strong class="d-block text-dark">${product.name_p}</strong>
                    <small class="text-muted">Réf: ${product.reference_p || 'N/A'} - ${product.name_cat}</small>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove ms-2"><i class="bi bi-x-lg"></i></button>
        `;
                selectedList.appendChild(li);
                searchInput.value = '';
                searchResults.style.display = 'none';
                updateCountAndSave(); // 🟢 Sauvegarde auto à l'ajout
            }

            selectedList.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-remove');
                if (btn) {
                    btn.closest('li').remove();
                    updateCountAndSave(); // 🟢 Sauvegarde auto à la suppression
                }
            });

            // ==========================================
            // 3. DRAG & DROP HTML5 NATIVE
            // ==========================================
            selectedList.addEventListener('dragstart', function(e) {
                draggedItem = e.target.closest('li');
                e.dataTransfer.effectAllowed = 'move';
                // Hack pour Firefox
                e.dataTransfer.setData('text/html', draggedItem.innerHTML);
                setTimeout(() => draggedItem.style.opacity = '0.5', 0);
            });

            selectedList.addEventListener('dragover', function(e) {
                e.preventDefault(); // Autorise le drop
                const targetItem = e.target.closest('li');
                if (targetItem && targetItem !== draggedItem) {
                    const bounding = targetItem.getBoundingClientRect();
                    const offset = bounding.y + (bounding.height / 2);
                    // Insère avant ou après selon la position de la souris
                    if (e.clientY - offset > 0) {
                        targetItem.after(draggedItem);
                    } else {
                        targetItem.before(draggedItem);
                    }
                }
            });

            selectedList.addEventListener('dragend', function(e) {
                draggedItem.style.opacity = '1';
                draggedItem = null;
                updateCountAndSave(); // 🟢 Sauvegarde auto après le déplacement
            });

            // ==========================================
            // 4. SAUVEGARDE AUTOMATIQUE (AJAX)
            // ==========================================
            function updateCountAndSave() {
                countBadge.textContent = selectedList.children.length;

                // On construit les données du formulaire
                const formData = new FormData();
                formData.append('hashtoken', token);

                const inputs = selectedList.querySelectorAll('input[name="featured_products[]"]');
                if (inputs.length === 0) {
                    formData.append('featured_products[]', ''); // Sécurité si on vide tout
                } else {
                    inputs.forEach(input => formData.append('featured_products[]', input.value));
                }

                // On envoie au contrôleur qui gère déjà le POST !
                fetch('index.php?controller=MagixFeatured', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status || data.success) { // Selon ce que retourne votre jsonResponse()
                            // Petit effet visuel pour confirmer la sauvegarde
                            saveIndicator.style.display = 'block';
                            setTimeout(() => saveIndicator.style.display = 'none', 2000);
                        }
                    });
            }
        });
        {/literal}
    </script>
{/block}