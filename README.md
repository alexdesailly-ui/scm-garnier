# ☤ SCM Garnier — Cabinet Infirmier à Nice

> Site web complet pour un cabinet d'infirmiers libéraux. Du booking patient au panneau d'admin, tout est là.

**[Voir la démo live →](https://alexdesailly-ui.github.io/scm-garnier-infirmier.fr)**

---

## Ce qu'on a livré

### Site public — responsive, rapide, accessible

| Page d'accueil | Prise de rendez-vous |
|:-:|:-:|
| ![Accueil](docs/screenshots/home.png) | ![RDV](docs/screenshots/rdv.png) |

| Prévention santé (vidéos + illustrations) | Contact & WhatsApp |
|:-:|:-:|
| ![Prévention](docs/screenshots/prevention.png) | ![Contact](docs/screenshots/contact.png) |

### Back-office admin — tout se gère sans toucher au code

| Dashboard | Gestion des RDV |
|:-:|:-:|
| ![Dashboard](docs/screenshots/admin-dashboard.png) | ![RDV Admin](docs/screenshots/admin-rdv.png) |

| Contacts / équipe | Paramètres |
|:-:|:-:|
| ![Contacts](docs/screenshots/admin-contacts.png) | ![Settings](docs/screenshots/admin-settings.png) |

---

## Stack technique

```
PHP 8.x natif (pas de framework, pas de dépendances, rien à npm install)
MySQL / MariaDB (InnoDB, utf8mb4)
Vanilla JS (pas de React, pas de Vue, juste du JS qui marche)
CSS custom properties (pas de Tailwind, pas de Bootstrap)
LiteSpeed / Apache (Hostinger ready)
```

### Sécurité — pas juste du blabla

- **Argon2id** pour le hash des mots de passe
- **AES-256-CBC** pour le chiffrement des données patients
- **CSRF tokens** rotatifs sur chaque formulaire
- **Rate limiting** login (5 tentatives → lockout 15 min)
- **Audit log** RGPD (qui a fait quoi, quand)
- Headers sécu : HSTS, X-Frame-Options, CSP

### Déploiement — push & forget

```
git push → GitHub Actions → FTP → Hostinger → en ligne
```

Un seul secret à configurer (`FTP_PASS`), le reste est automatique.

---

## Comment ça tourne

```bash
# 1. Clone
git clone https://github.com/alexdesailly-ui/scm-garnier-infirmier.fr.git

# 2. Déploie (ou juste push sur main)
git push origin main  # → auto-deploy sur Hostinger

# 3. Configure
# Va sur https://scm-garnier-infirmier.fr/setup.php
# → MySQL, admin, tables, c'est fait en 30 secondes
```

Pas de `composer install`. Pas de `npm run build`. Pas de Docker.
Tu poses les fichiers, tu ouvres setup.php, c'est en prod.

---

## Arborescence

```
├── index.php              # Accueil (hero, services, équipe)
├── rendez-vous.php        # Booking 4 étapes avec AJAX
├── prevention.php         # Vidéos YouTube + illustrations SVG
├── contact.php            # Formulaire + WhatsApp + carte équipe
├── mentions-legales.php   # RGPD complet
├── setup.php              # Installation tout-en-un
│
├── admin/
│   ├── login.php          # Auth Argon2id + rate limiting
│   ├── index.php          # Dashboard (stats, RDV récents)
│   ├── appointments.php   # CRUD rendez-vous + filtres
│   ├── contacts.php       # Gestion équipe + WhatsApp
│   └── settings.php       # Paramètres cabinet
│
├── api/
│   └── appointments.php   # REST: GET slots, POST create
│
├── includes/
│   ├── config.php         # PDO, env.php loader
│   ├── functions.php      # CSRF, crypto, helpers
│   ├── header.php         # Layout public
│   └── footer.php         # Footer + WhatsApp FAB
│
├── assets/
│   ├── css/style.css      # ~300 lignes, full responsive
│   └── js/app.js          # Vanilla JS, booking flow
│
└── docs/                  # Preview GitHub Pages (HTML statique)
```

---

## Features en bref

| Feature | Détail |
|---|---|
| Booking patient | 4 étapes : soin → date/heure → infos → confirmation |
| Créneaux AJAX | Slots dispo en temps réel par infirmier |
| WhatsApp intégré | Bouton flottant + liens directs par infirmier |
| Admin complet | Dashboard, RDV, contacts, paramètres |
| Responsive | Mobile-first, testé sur iPhone/Android |
| RGPD | Chiffrement, consentement, audit log, mentions légales |
| SEO ready | Balises meta, URLs propres (.php masqué), sitemap-ready |
| Zéro dépendance | Pas de node_modules, pas de vendor/, juste du PHP |

---

*Fait avec du code et du café par [alexdesailly-ui](https://github.com/alexdesailly-ui)*
