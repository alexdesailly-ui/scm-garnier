## Intention

<!-- En une ou deux phrases : quel problème, pour quel utilisateur (patient, infirmier, administrateur du cabinet). C'est ce que l'humain juge en dernier. -->

## Ticket

<!-- Closes #… ou lien vers la demande -->

## Ce qui change

-

## Risques

- [ ] Touche la base (migration, requête, filtre `tenant_id`)
- [ ] Touche les données patients (rendez-vous, notes, adresse)
- [ ] Touche le paiement (Stripe, abonnement, quotas)
- [ ] Touche l'authentification ou le back-office `admin/`
- [ ] Touche le déploiement ou le mode maintenance (`.htaccess`, `scripts/`)
