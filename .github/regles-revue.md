# Règles de revue de SCM Garnier

Référentiel lu par l'agent de revue à chaque PR. Alex l'enrichit à chaque faux positif ou oubli constaté : c'est ce fichier qui fait progresser la revue, pas le prompt.

Contexte : site public et back-office d'un cabinet infirmier, PHP 8.1 natif, MySQL, multi-cabinets (`tenant_id`), abonnement Stripe, déploiement FTP automatique sur Hostinger à chaque push sur `main`. Les rendez-vous contiennent des données de patients (nom, téléphone, email, adresse, type de soin, notes) : ce sont des données de santé.

## Ce que l'agent cherche, par ordre de gravité

| Gravité | Exemples propres à SCM Garnier |
|---|---|
| **Bloquant** | Requête sur `appointments`, `contacts`, `users`, `settings`, `available_slots` ou `audit_log` sans filtre `tenant_id` quand `App::instance()->tenantId()` est connu ; donnée patient exposée hors du back-office authentifié (réponse de `api/appointments.php`, log, email, page publique) ; page `admin/` ou action `api/billing.php` sans `requireAuth()` / `adminHeader()` ; action d'écriture sans `verifyCSRF()` ; SQL construit par concaténation d'une entrée utilisateur au lieu d'une requête préparée ; sortie HTML sans `e()` ; webhook Stripe sans `StripeClient::verifyWebhookSignature` ou sans contrôle `isDuplicate` ; plan ou abonnement écrit depuis le navigateur ; secret en clair (FTP, MySQL, Stripe, `APP_SECRET`, `ENCRYPTION_KEY`) ; fichier de `migrations/` déjà présent sur `main` réécrit |
| **Important** | Nouvelle colonne pouvant recevoir une donnée patient (texte libre, notes) sans chiffrement via `encryptData()` ni justification ; règle métier vérifiée seulement dans `assets/js/app.js` (créneau libre, quota, consentement RGPD) ; quota `QuotaMiddleware::enforce` oublié sur une nouvelle création ; écriture multi-requêtes sans transaction (création de rendez-vous + consommation de quota, changement de plan) ; nouvelle action admin sensible sans `auditLog()` ; nouvelle migration non idempotente (`CREATE TABLE` sans `IF NOT EXISTS`, `ALTER` rejoué) ; `setup.php`, `install.php` ou `scripts/` rendus exploitables en production ; régression du mode maintenance (`.htaccess`, `maintenance.html`) |
| **Mineur** | Libellé hors vocabulaire des infirmiers libéraux ; message d'erreur qui révèle un détail technique au patient ; README non mis à jour alors que le comportement change ; nouvelle entrée ajoutée à `phpstan-baseline.neon` au lieu de corriger |

## Ce que l'agent ne commente pas

- Ce que la CI « Revue de code (qualité) » vérifie déjà : PHPStan, PHP_CodeSniffer (PSR-12), SonarQube. Il lit leur statut (`gh pr checks`) et le cite, il ne le refait pas.
- Les préférences de style sans conséquence.
- Le code non modifié par la PR, sauf si le changement le casse.

## Filtre anti faux positifs

Un constat n'est publié que si l'agent a :
1. relu le code autour du diff (appelants, `BaseModel::buildWhere`, middleware concerné, migration concernée) ;
2. décrit un scénario concret de défaillance (« un administrateur du cabinet A peut lire les rendez-vous du cabinet B en changeant `id` dans l'URL… ») ;
3. vérifié qu'aucun contrôle existant (contrainte `NOT NULL` de la migration 009, middleware, `.htaccess`) ne le couvre déjà.

Sinon, le constat est écarté ou reformulé en question.

## Faux positifs connus (à ne plus signaler)

- Branche `else` sans filtre `tenant_id` quand `tenantId()` vaut `null` : repli mono-cabinet voulu pendant l'installation ; à signaler seulement si le diff élargit ce repli.
- Valeurs `CHANGEZ_CECI…`, `VotreMotDePasseMySQL`, `sk_test_...` dans `env.example.php` : modèles, pas des secrets.
- Hôte et utilisateur FTP en clair dans les workflows de déploiement : identifiants non secrets, le mot de passe est dans `secrets.FTP_PASS`.
