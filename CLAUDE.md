# SCM Garnier — consignes pour les agents

Lu à chaque exécution par les agents (revue de PR, correctifs `@claude`). Rester court.

## Le produit

Site public et back-office d'un cabinet infirmier à Nice : prise de rendez-vous patient, prévention, contact, gestion de l'équipe. PHP 8.1 natif sans framework, MySQL, multi-cabinets (`tenant_id`), abonnement Stripe, déploiement FTP sur Hostinger.

## Commandes

| But | Commande |
|---|---|
| Installer les outils de qualité | `composer install` |
| Tout vérifier avant de pousser | `composer quality` (PHP_CodeSniffer PSR-12 puis PHPStan niveau 5) |
| Syntaxe d'un fichier | `php -l <fichier>` |
| Appliquer les migrations | `php scripts/migrate.php` (base MySQL requise) |

## Règles non négociables

1. **Données de santé.** Les rendez-vous contiennent des données patients : elles ne sortent jamais du back-office authentifié (pas de log, pas de réponse d'API publique, pas d'email en clair). Tout nouveau champ libre patient se justifie.
2. **Isolation des cabinets.** Toute requête sur une table métier filtre `tenant_id` ; passer par `BaseModel::buildWhere()` / `buildParams()` dès que possible.
3. **Formulaires.** Toute écriture vérifie `verifyCSRF()` ; toute sortie HTML passe par `e()` ; toute requête est préparée.
4. **Back-office.** Chaque page `admin/` et `api/billing.php` exige `requireAuth()` (ou `adminHeader()`).
5. **Stripe.** L'abonnement n'est écrit que par `api/stripe-webhook.php` après `verifyWebhookSignature` et contrôle d'idempotence ; aucune logique de paiement dans `assets/js/`.
6. **Migrations : on ajoute, on ne réécrit pas.** Jamais de modification d'un fichier de `migrations/` déjà présent sur `main` ; toute nouvelle migration est idempotente.
7. **Secrets.** Rien en clair dans le dépôt : `env.php` et `env.secrets.php` restent ignorés, `env.example.php` ne contient que des modèles.
8. **Déploiement.** Un push sur `main` part en production par FTP : aucune PR ne cible `main` sans validation d'Alex.

## Conventions

- Interface, messages de commit et documentation en français, vocabulaire des infirmiers libéraux.
- Pas de dépendance d'exécution : `composer.json` ne contient que des outils de développement.
- Ne jamais fusionner sur la branche par défaut, ne jamais déployer, ne jamais toucher à .github/workflows/ sans demande explicite d'Alex.
