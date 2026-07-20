<?php
/**
 * Fichier indépendant : ne dépend d'aucune variable transmise par le parent.
 * Charge lui-même le helper 'url' dont il a besoin pour base_url(),
 * et lit directement le nom de l'opérateur actif depuis la session.
 */
helper('url');
$operateurNomActif = session()->get('operateurNom');
?>
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="d-flex align-items-center gap-2">
                    <div class="brand-logo">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="5" y="2" width="14" height="20" rx="2" />
                            <line x1="12" y1="18" x2="12.01" y2="18" />
                        </svg>
                    </div>
                    <span class="brand-text">Nova<span class="accent">Money</span></span>
                </div>
                <div class="brand-sub"><?= $operateurNomActif !== null ? esc($operateurNomActif) : 'Espace Opérateur' ?></div>
            </div>

            <nav class="sidebar-nav">
                <button class="nav-btn active" data-tab="prefixes" onclick="switchTab('prefixes',this)">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="4" y1="9" x2="20" y2="9" />
                        <line x1="4" y1="15" x2="20" y2="15" />
                        <line x1="10" y1="3" x2="8" y2="21" />
                        <line x1="16" y1="3" x2="14" y2="21" />
                    </svg>
                    Préfixes
                </button>
                <button class="nav-btn" data-tab="operations" onclick="switchTab('operations',this)">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3" />
                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14" />
                    </svg>
                    Opérations
                </button>
                <button class="nav-btn" data-tab="gains" onclick="switchTab('gains',this)">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 7 13.5 15.5 8.5 10.5 2 17" />
                        <polyline points="16 7 22 7 22 13" />
                    </svg>
                    Gains
                </button>
                <button class="nav-btn" data-tab="accounts" onclick="switchTab('accounts',this)">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                    Comptes
                </button>
            </nav>

            <div class="sidebar-footer">
                <a href="<?= base_url('operator/logout') ?>" class="nav-btn" style="text-decoration:none">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3" />
                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14" />
                    </svg>
                    Changer d'opérateur
                </a>
                <a href="<?= base_url('/') ?>" class="nav-btn" style="text-decoration:none">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <polyline points="16 17 21 12 16 7" />
                        <line x1="21" y1="12" x2="9" y2="12" />
                    </svg>
                    Retour à l'accueil
                </a>
            </div>
        </aside>
