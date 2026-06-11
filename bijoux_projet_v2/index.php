<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

$selectedCategory = isset($_GET['category']) && !empty($_GET['category']) ? $_GET['category'] : '';
$searchQuery      = isset($_GET['q']) && !empty($_GET['q']) ? trim($_GET['q']) : '';

if ($selectedCategory) {
    $products = getProductsByCategory($pdo, $selectedCategory);
} else {
    $products = getProducts($pdo);
}

// Filtre côté PHP si recherche présente
if ($searchQuery) {
    $q = mb_strtolower($searchQuery);
    $products = array_filter($products, function($p) use ($q) {
        return str_contains(mb_strtolower($p['name']), $q)
            || str_contains(mb_strtolower($p['categories']), $q)
            || str_contains((string)$p['price'], $q);
    });
    $products = array_values($products);
}

$totalProducts = count($products);

// KPI stats
$totalValue    = 0;
$lowStockCount = 0;
$outOfStock    = 0;
foreach ($products as $p) {
    $totalValue += $p['price'] * $p['quantity'];
    if ((int)$p['quantity'] === 0) $outOfStock++;
    elseif ((int)$p['quantity'] <= 5) $lowStockCount++;
}

// Flash messages (après add/edit/delete)
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novalya · Gestion de Stock</title>
    <meta name="description" content="Interface de gestion de stock de bijoux Novalya.">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800&display=swap" rel="stylesheet">
</head>
<body>

<!-- ════════════════════════════════════════════════════════
     SIDEBAR
════════════════════════════════════════════════════════ -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">💎</div>
        <div class="brand-text">
            <span class="brand-name">Novalya</span>
            <span class="brand-sub">Stock Manager</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-title">Menu Principal</div>
        <a href="index.php" class="nav-item active" id="nav-dashboard">
            <span class="nav-icon">🏠</span>
            <span class="nav-label">Tableau de bord</span>
            <span class="nav-badge"><?= $totalProducts ?></span>
        </a>
        <a href="add.php" class="nav-item" id="nav-add">
            <span class="nav-icon">➕</span>
            <span class="nav-label">Ajouter Produit</span>
        </a>

        <div class="nav-section-title">Catégories</div>
        <a href="index.php?category=1" class="nav-item <?= $selectedCategory=='1'?'active':'' ?>" id="nav-cat-1">
            <span class="nav-icon">📿</span>
            <span class="nav-label">Bracelets</span>
        </a>
        <a href="index.php?category=2" class="nav-item <?= $selectedCategory=='2'?'active':'' ?>" id="nav-cat-2">
            <span class="nav-icon">💍</span>
            <span class="nav-label">Colliers</span>
        </a>
        <a href="index.php?category=3" class="nav-item <?= $selectedCategory=='3'?'active':'' ?>" id="nav-cat-3">
            <span class="nav-icon">💛</span>
            <span class="nav-label">Bagues</span>
        </a>
        <a href="index.php?category=4" class="nav-item <?= $selectedCategory=='4'?'active':'' ?>" id="nav-cat-4">
            <span class="nav-icon">✨</span>
            <span class="nav-label">Boucles</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-footer-inner">
            <div class="sidebar-avatar">NS</div>
            <div class="sidebar-user-info">
                <span class="sidebar-user-name">Admin</span>
                <span class="sidebar-user-role">PFA · 2025</span>
            </div>
        </div>
    </div>
</aside>

<!-- Overlay mobile -->
<div class="sidebar-overlay" id="sidebar-overlay" onclick="closeSidebar()"></div>

<!-- ════════════════════════════════════════════════════════
     MAIN CONTENT
════════════════════════════════════════════════════════ -->
<div class="layout-wrapper">

    <!-- TOPBAR -->
    <header class="topbar" id="topbar">
        <div class="topbar-left">
            <button class="hamburger" id="hamburger" onclick="toggleSidebar()" aria-label="Menu" title="Ouvrir le menu">
                <span></span><span></span><span></span>
            </button>
            <nav class="breadcrumb" aria-label="Fil d'Ariane">
                <a href="index.php" class="bc-link">Novalya</a>
                <span class="bc-sep">›</span>
                <span class="bc-current">Tableau de bord</span>
                <?php if ($selectedCategory): ?>
                    <span class="bc-sep">›</span>
                    <span class="bc-current"><?= ['','Bracelets','Colliers','Bagues','Boucles'][$selectedCategory] ?></span>
                <?php endif; ?>
            </nav>
        </div>
        <div class="topbar-center">
            <form method="GET" class="topbar-search" id="topbar-search-form" role="search">
                <?php if ($selectedCategory): ?>
                    <input type="hidden" name="category" value="<?= htmlspecialchars($selectedCategory) ?>">
                <?php endif; ?>
                <span class="search-icon">🔍</span>
                <input
                    type="text"
                    name="q"
                    id="search-input"
                    class="search-field"
                    placeholder="Rechercher un produit…"
                    value="<?= htmlspecialchars($searchQuery) ?>"
                    autocomplete="off"
                >
                <?php if ($searchQuery): ?>
                    <a href="index.php<?= $selectedCategory ? '?category='.$selectedCategory : '' ?>" class="search-clear" title="Effacer">✕</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="topbar-right">
            <div class="topbar-date" id="topbar-clock"></div>
            <a href="add.php" class="btn-primary" id="btn-add-top">
                <span>＋</span> Nouveau Produit
            </a>
        </div>
    </header>

    <!-- MAIN -->
    <main class="main-content">

        <!-- Flash Toast -->
        <?php if ($flash): ?>
        <div class="toast toast-<?= $flash['type'] ?>" id="flash-toast" role="alert">
            <span class="toast-icon"><?= $flash['type'] === 'success' ? '✅' : '❌' ?></span>
            <span class="toast-msg"><?= htmlspecialchars($flash['msg']) ?></span>
            <button class="toast-close" onclick="this.parentElement.remove()" aria-label="Fermer">✕</button>
        </div>
        <?php endif; ?>

        <!-- Page title -->
        <div class="page-header">
            <div class="header-left">
                <h1 class="page-title">Gestion de Stock</h1>
                <p class="page-subtitle">
                    <?php if ($searchQuery): ?>
                        Résultats pour <strong>"<?= htmlspecialchars($searchQuery) ?>"</strong>
                    <?php elseif ($selectedCategory): ?>
                        Catégorie : <strong><?= ['','Bracelets','Colliers','Bagues','Boucles'][$selectedCategory] ?></strong>
                    <?php else: ?>
                        Vue d'ensemble de votre inventaire bijoux
                    <?php endif; ?>
                </p>
            </div>
            <div class="header-actions">
                <button class="btn-icon-only" onclick="window.print()" title="Imprimer le tableau" id="btn-print">🖨️</button>
                <a href="index.php" class="btn-secondary" id="btn-refresh">↺ Actualiser</a>
            </div>
        </div>

        <!-- KPI Grid -->
        <section class="kpi-grid" aria-label="Indicateurs clés">

            <div class="kpi-card kpi-total">
                <div class="kpi-icon-wrap kpi-icon-gold">📦</div>
                <div class="kpi-body">
                    <div class="kpi-value counter" data-target="<?= $totalProducts ?>"><?= $totalProducts ?></div>
                    <div class="kpi-label">Total Produits</div>
                </div>
                <div class="kpi-trend kpi-trend-up">+<?= $totalProducts ?> références</div>
                <div class="kpi-bg-deco"></div>
            </div>

            <div class="kpi-card kpi-value-card">
                <div class="kpi-icon-wrap kpi-icon-blue">💰</div>
                <div class="kpi-body">
                    <div class="kpi-value" style="font-size:20px"><?= number_format($totalValue, 2, ',', ' ') ?> <small>Dh</small></div>
                    <div class="kpi-label">Valeur du Stock</div>
                </div>
                <div class="kpi-trend">Inventaire total</div>
                <div class="kpi-bg-deco"></div>
            </div>

            <div class="kpi-card kpi-low">
                <div class="kpi-icon-wrap kpi-icon-amber">⚠️</div>
                <div class="kpi-body">
                    <div class="kpi-value counter" data-target="<?= $lowStockCount ?>"><?= $lowStockCount ?></div>
                    <div class="kpi-label">Stock Faible (≤5)</div>
                </div>
                <div class="kpi-trend <?= $lowStockCount > 0 ? 'kpi-trend-warn' : 'kpi-trend-ok' ?>">
                    <?= $lowStockCount > 0 ? 'Attention requise' : 'Tout est OK' ?>
                </div>
                <div class="kpi-bg-deco"></div>
            </div>

            <div class="kpi-card kpi-out">
                <div class="kpi-icon-wrap kpi-icon-red">🚫</div>
                <div class="kpi-body">
                    <div class="kpi-value counter" data-target="<?= $outOfStock ?>"><?= $outOfStock ?></div>
                    <div class="kpi-label">Rupture de Stock</div>
                </div>
                <div class="kpi-trend <?= $outOfStock > 0 ? 'kpi-trend-danger' : 'kpi-trend-ok' ?>">
                    <?= $outOfStock > 0 ? 'Réapprovisioner' : 'Aucune rupture' ?>
                </div>
                <div class="kpi-bg-deco"></div>
            </div>

        </section>

        <!-- Toolbar -->
        <div class="toolbar">
            <div class="toolbar-left">
                <span class="result-count">
                    <span class="count-num"><?= $totalProducts ?></span>
                    produit<?= $totalProducts > 1 ? 's' : '' ?>
                    <?= $searchQuery ? 'trouvé' . ($totalProducts > 1 ? 's' : '') : '' ?>
                </span>
                <?php if ($selectedCategory || $searchQuery): ?>
                    <a href="index.php" class="chip-clear">✕ Effacer les filtres</a>
                <?php endif; ?>
            </div>
            <form method="GET" class="filter-form" id="filter-form">
                <?php if ($searchQuery): ?>
                    <input type="hidden" name="q" value="<?= htmlspecialchars($searchQuery) ?>">
                <?php endif; ?>
                <label for="category-select" class="filter-label">Filtrer :</label>
                <div class="select-wrapper">
                    <select name="category" id="category-select" onchange="this.form.submit()">
                        <option value="">Toutes les catégories</option>
                        <option value="1" <?= $selectedCategory=='1'?'selected':'' ?>>📿 Bracelets</option>
                        <option value="2" <?= $selectedCategory=='2'?'selected':'' ?>>💍 Colliers</option>
                        <option value="3" <?= $selectedCategory=='3'?'selected':'' ?>>💛 Bagues</option>
                        <option value="4" <?= $selectedCategory=='4'?'selected':'' ?>>✨ Boucles</option>
                    </select>
                    <span class="select-arrow">▾</span>
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <div class="empty-illustration">🔍</div>
                    <h3>Aucun produit trouvé</h3>
                    <p>
                        <?= $searchQuery ? 'Aucun résultat pour "'.htmlspecialchars($searchQuery).'". Essayez un autre terme.' : 'Cette catégorie est vide.' ?>
                    </p>
                    <a href="add.php" class="btn-primary" style="margin-top:8px">➕ Ajouter un produit</a>
                </div>
            <?php else: ?>
            <div class="table-toolbar">
                <span class="table-title">📋 Liste des produits</span>
                <div class="table-toolbar-right">
                    <span class="table-hint">💡 Cliquez sur une colonne pour trier</span>
                </div>
            </div>
            <div class="table-responsive">
                <table id="products-table" aria-label="Liste des produits">
                    <thead>
                        <tr>
                            <th class="sortable" data-col="0" scope="col">
                                <span>#ID</span><span class="sort-icon">⇅</span>
                            </th>
                            <th class="sortable" data-col="1" scope="col">
                                <span>Produit</span><span class="sort-icon">⇅</span>
                            </th>
                            <th class="sortable" data-col="2" scope="col">
                                <span>Prix</span><span class="sort-icon">⇅</span>
                            </th>
                            <th class="sortable" data-col="3" scope="col">
                                <span>Quantité</span><span class="sort-icon">⇅</span>
                            </th>
                            <th scope="col">Stock</th>
                            <th scope="col">Catégorie</th>
                            <th scope="col">Statut</th>
                            <th scope="col" class="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <?php
                        // Quantité max pour la barre de progression
                        $maxQty = max(array_column($products, 'quantity')) ?: 1;
                        ?>
                        <?php foreach ($products as $i => $p): ?>
                            <?php
                            $qty = (int)$p['quantity'];
                            $pct = min(100, round(($qty / max($maxQty, 1)) * 100));
                            $stockStatus = $qty === 0 ? 'rupture' : ($qty <= 5 ? 'faible' : 'ok');
                            $stockLabel  = $qty === 0 ? 'Rupture' : ($qty <= 5 ? 'Stock faible' : 'En stock');
                            ?>
                            <tr class="product-row" data-id="<?= (int)$p['id'] ?>"
                                style="animation-delay: <?= $i * 0.04 ?>s">
                                <td class="td-id">
                                    <span class="id-badge">#<?= htmlspecialchars($p['id']) ?></span>
                                </td>
                                <td class="td-name">
                                    <div class="product-info">
                                        <div class="product-avatar"><?= mb_strtoupper(mb_substr($p['name'], 0, 1)) ?></div>
                                        <div>
                                            <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                                            <div class="product-id-sub">REF-<?= str_pad($p['id'], 4, '0', STR_PAD_LEFT) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="td-price" data-val="<?= $p['price'] ?>">
                                    <span class="price-tag"><?= number_format((float)$p['price'], 2, ',', ' ') ?> Dh</span>
                                </td>
                                <td class="td-qty" data-val="<?= $qty ?>">
                                    <div class="qty-block">
                                        <span class="qty-num <?= $stockStatus ?>"><?= $qty ?></span>
                                        <div class="stock-bar-wrap">
                                            <div class="stock-bar stock-bar-<?= $stockStatus ?>" style="width:<?= $pct ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="td-stock-val">
                                    <span class="stock-pct-label"><?= $pct ?>%</span>
                                </td>
                                <td class="td-cat">
                                    <span class="category-badge"><?= htmlspecialchars($p['categories']) ?></span>
                                </td>
                                <td class="td-status">
                                    <span class="status-chip status-<?= $stockStatus ?>">
                                        <?= $stockLabel ?>
                                    </span>
                                </td>
                                <td class="td-actions">
                                    <a href="edit.php?id=<?= urlencode($p['id']) ?>"
                                       class="action-btn btn-edit"
                                       id="edit-<?= (int)$p['id'] ?>"
                                       title="Modifier <?= htmlspecialchars($p['name']) ?>">
                                        ✏️ Modifier
                                    </a>
                                    <a href="delete.php?id=<?= urlencode($p['id']) ?>"
                                       class="action-btn btn-delete"
                                       id="delete-<?= (int)$p['id'] ?>"
                                       title="Supprimer <?= htmlspecialchars($p['name']) ?>"
                                       onclick="return confirmDelete(event, '<?= htmlspecialchars(addslashes($p['name'])) ?>')">
                                        🗑️ Supprimer
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="table-footer">
                <span class="table-footer-info">
                    Affichage de <strong><?= $totalProducts ?></strong> produit<?= $totalProducts > 1 ? 's' : '' ?>
                    · Valeur totale : <strong><?= number_format($totalValue, 2, ',', ' ') ?> Dh</strong>
                </span>
            </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<!-- ════════════════════════════════════════════════════════
     MODAL SUPPRESSION
════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="delete-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="modal-box">
        <div class="modal-icon-wrap">🗑️</div>
        <h3 class="modal-title" id="modal-title">Confirmer la suppression</h3>
        <p class="modal-msg" id="modal-msg"></p>
        <div class="modal-alert">
            <span>⚠️</span>
            <span>Cette action est <strong>irréversible</strong>. Le produit sera définitivement supprimé.</span>
        </div>
        <div class="modal-actions">
            <button class="btn-cancel" id="btn-cancel-delete" onclick="closeModal()">
                ← Annuler
            </button>
            <a href="#" class="btn-confirm-delete" id="btn-confirm-delete">
                🗑️ Oui, supprimer
            </a>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════
     SCRIPTS
════════════════════════════════════════════════════════ -->
<script>
/* ── Sidebar Toggle (mobile) ─────────────────────────── */
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebar-overlay').classList.toggle('visible');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebar-overlay').classList.remove('visible');
}

/* ── Topbar clock ────────────────────────────────────── */
function updateClock() {
    const now = new Date();
    const opts = { weekday:'short', day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit' };
    document.getElementById('topbar-clock').textContent = now.toLocaleDateString('fr-FR', opts);
}
updateClock();
setInterval(updateClock, 60000);

/* ── Flash toast auto-dismiss ────────────────────────── */
const toast = document.getElementById('flash-toast');
if (toast) {
    setTimeout(() => toast.classList.add('toast-hide'), 4000);
    setTimeout(() => toast.remove(), 4500);
}

/* ── Modal suppression ───────────────────────────────── */
function confirmDelete(e, name) {
    e.preventDefault();
    const url = e.currentTarget.href;
    document.getElementById('modal-msg').textContent =
        'Êtes-vous sûr de vouloir supprimer "' + name + '" ?';
    document.getElementById('btn-confirm-delete').href = url;
    document.getElementById('delete-modal').classList.add('active');
    setTimeout(() => document.getElementById('btn-cancel-delete').focus(), 100);
    return false;
}
function closeModal() {
    document.getElementById('delete-modal').classList.remove('active');
}
document.getElementById('delete-modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});

/* ── Table Sorting ───────────────────────────────────── */
(function() {
    const table = document.getElementById('products-table');
    if (!table) return;

    let sortCol = -1, sortAsc = true;

    table.querySelectorAll('th.sortable').forEach(function(th) {
        th.addEventListener('click', function() {
            const col = parseInt(this.dataset.col);
            if (sortCol === col) { sortAsc = !sortAsc; }
            else { sortCol = col; sortAsc = true; }

            // Update icons
            table.querySelectorAll('th.sortable').forEach(t => {
                t.classList.remove('sort-asc', 'sort-desc');
                t.querySelector('.sort-icon').textContent = '⇅';
            });
            this.classList.add(sortAsc ? 'sort-asc' : 'sort-desc');
            this.querySelector('.sort-icon').textContent = sortAsc ? '↑' : '↓';

            // Sort rows
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            rows.sort(function(a, b) {
                let va = a.cells[col].dataset.val ?? a.cells[col].textContent.trim();
                let vb = b.cells[col].dataset.val ?? b.cells[col].textContent.trim();
                const na = parseFloat(va.replace(/[^0-9.,]/g, '').replace(',', '.'));
                const nb = parseFloat(vb.replace(/[^0-9.,]/g, '').replace(',', '.'));
                if (!isNaN(na) && !isNaN(nb)) return sortAsc ? na - nb : nb - na;
                return sortAsc ? va.localeCompare(vb, 'fr') : vb.localeCompare(va, 'fr');
            });
            rows.forEach(r => tbody.appendChild(r));
        });
        th.style.cursor = 'pointer';
    });
})();

/* ── Row animations ──────────────────────────────────── */
document.querySelectorAll('.product-row').forEach(function(row) {
    row.classList.add('row-animate');
});

/* ── Live search (client-side instant filter) ────────── */
const searchInput = document.getElementById('search-input');
if (searchInput) {
    let debounceTimer;
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            this.form.submit();
        }, 500);
    });
}

/* ── Topbar scroll shadow ────────────────────────────── */
window.addEventListener('scroll', function() {
    document.getElementById('topbar').classList.toggle('topbar-scrolled', window.scrollY > 10);
}, { passive: true });

/* ── Stock bar animation ─────────────────────────────── */
setTimeout(function() {
    document.querySelectorAll('.stock-bar').forEach(function(bar) {
        const w = bar.style.width;
        bar.style.width = '0%';
        requestAnimationFrame(() => {
            bar.style.transition = 'width 0.8s cubic-bezier(0.34,1.56,0.64,1)';
            bar.style.width = w;
        });
    });
}, 200);
</script>

</body>
</html>
