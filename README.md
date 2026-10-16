# Software Exploration

Site de tests et d’avis de logiciels (IA, SEO, marketing), en anglais à la racine et en français sous `/fr/`.

Ce dépôt contient :

| Dossier | Contenu |
| --- | --- |
| `wordpress/plugins/se-reviews/` | Plugin **Software Exploration Reviews** : fiches logiciels, notes, blocs Gutenberg (verdict, comparatifs, classements…), avis lecteurs, liens affiliés `/go/…`, compositions de pages. |
| `wordpress/themes/software-exploration/` | Thème enfant **Blocksy** : polices hébergées localement, couleurs et typographie du design system, modèle de commentaires. |
| `design/tokens.json` | Les couleurs, tailles et espacements du design system (source unique). |
| `bin/build-tokens.php` | Régénère `assets/css/tokens.css` du plugin à partir de `design/tokens.json`. |
| `bin/build-zips.sh` | Fabrique les deux fichiers zip à téléverser dans WordPress. |

Le design system complet (gabarits, composants, règles) est publié en artefact : https://claude.ai/artifact/XS77mUvtDqYGtzGuc1pa25

## Installation en une commande (cPanel)

1. Dans cPanel → **Domaines**, ajouter `software-exploration.com` (s’il n’y est pas déjà).
2. Ouvrir cPanel → **Terminal** et coller :

```sh
curl -fsSL https://raw.githubusercontent.com/Link6689/software-exploration/main/bin/install-cpanel.sh | bash -s -- software-exploration.com votre@email.fr
```

Le script crée la base de données, installe WordPress, Blocksy, Blocksy Companion, Polylang, le thème enfant et le plugin, règle les langues (anglais à la racine, français sous `/fr/`), les permaliens, la modération des commentaires, l’en-tête Blocksy et crée les pages d’accueil EN et FR. Il affiche à la fin l’identifiant et le mot de passe administrateur. S’il trouve déjà un WordPress dans le dossier du domaine, il s’arrête sans rien modifier.

Restent à faire à la main : les menus EN et FR, et masquer « Méta de l’article » (voir *Réglages Blocksy* ci-dessous).

## Installation manuelle

1. **WordPress** 6.5 ou plus récent, PHP 7.4 ou plus.
2. Installer et activer le thème **Blocksy** (gratuit) depuis *Apparence → Thèmes → Ajouter*.
3. Lancer `sh bin/build-zips.sh`, puis téléverser :
   - `dist/software-exploration.zip` dans *Apparence → Thèmes → Ajouter → Téléverser*, et l’activer ;
   - `dist/se-reviews.zip` dans *Extensions → Ajouter → Téléverser*, et l’activer.
4. Installer **Polylang** (gratuit) et le régler dans *Langues → Réglages* :
   - langues : English (par défaut) et Français ;
   - *URL* : « La langue est définie par le nom du répertoire », cocher « Cacher le code de langue dans l’URL pour la langue par défaut » → anglais à la racine, français sous `/fr/` ;
   - Polylang gère seul les balises `hreflang` et `x-default`.
5. *Réglages → Permaliens* : « Titre de la publication ». Enregistrer (cela active aussi `/go/…`).
6. Recommandé : **Rank Math** (SEO, fil d’Ariane, sommaire), **Antispam Bee** (spam des commentaires, sans service externe), un cache (LiteSpeed Cache ou WP Rocket).

### Réglages Blocksy

- *Personnaliser → Couleurs → Palette globale* (le thème force déjà ces valeurs, les reporter ici aligne l’aperçu du personnalisateur) :
  1 `#0e5a48` · 2 `#121c18` · 3 `#121c18` · 4 `#121c18` · 5 `#d9d5c8` · 6 `#ebe8de` · 7 `#f5f3ec` · 8 `#ffffff`.
- *Typographie* : laisser « Instrument Sans » pour le texte et « Bricolage Grotesque » pour les titres ; les fichiers sont fournis par le thème (aucun appel à Google Fonts, conforme RGPD).
- *En-tête* (constructeur d’en-tête) :
  - logo : retirer l’élément « Logo » et glisser à sa place un élément **HTML** contenant `[se_wordmark]` (logo + nom, lien vers l’accueil de la langue courante) ;
  - menu : IA, SEO, Marketing, Comparatifs, Alternatives, Bons plans, Guides — créer un menu par langue (*Apparence → Menus*, Polylang propose un emplacement par langue) ;
  - sélecteur de langue : ajouter un **second élément HTML** (bouton « dupliquer » de l’élément HTML) contenant `[se_lang_switch]`, à droite du menu ;
  - sur mobile, mettre aussi `[se_wordmark]` à la place du logo.
- *Articles → Article seul → Titre de la publication* : désactiver l’élément **Méta de l’article** (auteur, date, catégorie), sinon il fait doublon avec le bloc *Author and dates* des compositions.
- *Articles → Article seul → Commentaires* : désactiver le champ **Site web** (il n’attire que du spam sur un site d’avis).
- *Articles* : barre latérale à droite pour les tests (y placer le bloc *Verdict box* en mode compact et le sommaire Rank Math). Pages « classement », « comparatif » et accueil : structure **pleine largeur** sans barre latérale.
- Favicon : *Personnaliser → Identité du site → Icône* avec `wordpress/themes/software-exploration/assets/img/se-mark.svg` (en attendant, le thème l’affiche automatiquement).

## Utilisation au quotidien

### 1. Créer une fiche logiciel

Menu **Software → Add software**. Une fiche par outil, partagée par les deux langues :
note /10, lien affilié, offre gratuite, interface en français, puis pour chaque texte un champ EN et un champ FR (catégorie, prix, accroche, « idéal pour », verdict, points forts et faibles, offres tarifaires, bon plan avec date d’expiration, URL de nos avis). Le logo est l’image mise en avant (carré, 256 px).

Changer un prix ou une note sur la fiche le met à jour sur **toutes** les pages qui affichent l’outil.

Le lien affilié est servi par `https://software-exploration.com/go/<slug>/` (redirection, `noindex`, exclu dans `robots.txt`) : c’est ce lien que les boutons orange utilisent.

### 2. Écrire une page

Nouvel article ou nouvelle page → onglet **Compositions** → catégorie *Software Exploration* :

| Composition | Pour |
| --- | --- |
| Review / test | un avis complet sur un outil |
| Best X ranking | « Les 8 meilleurs outils de rédaction IA » |
| Comparison of several tools | un comparatif en tableau |
| Versus (A vs B) | « Draftly vs Quill » |
| Alternatives to X | « Les meilleures alternatives à Jasper » |
| Deals and promo codes | la page bons plans (les offres expirées disparaissent seules) |
| Home page | l’accueil |

Puis cliquer sur chaque bloc et choisir le ou les outils dans la barre latérale. Les libellés (« Points forts », « Idéal pour », « Essayer »…) passent automatiquement en français sur les pages en français.

Formats des champs texte des blocs :

- **FAQ** : `Q: question` puis `A: réponse`, une ligne vide entre deux questions. Produit le balisage `FAQPage`.
- **Comparison table → Extra rows** : `Libellé EN | Libellé FR | valeur 1 | valeur 2 …` ; valeurs `yes`, `no`, `score:8.5` ou du texte ; titre de groupe : `## Groupe EN | Groupe FR`.
- **Category tiles** : `Nom | sous-titre | icône | URL | featured`.

### 3. Contact, mentions légales et cookies

- **Formulaire de contact** : bloc *Contact form* (catégorie Software Exploration). Les messages partent par e-mail à l’adresse d’administration du site (*Réglages → Général*), rien n’est stocké. Anti-spam sans captcha : champ piège invisible, délai minimal avant envoi, 5 messages par heure et par adresse IP. Autre destinataire : filtre `se_contact_to`.
- **Pied de page** : le thème remplace le crédit Blocksy par « © année Software Exploration · Contact · Mentions légales ». Les liens pointent vers les pages de slug `contact` / `legal-notice` (EN) et `nous-contacter` / `mentions-legales` (FR), s’ils sont publiés.
- **Google Analytics 4 et bandeau cookies** : définir l’identifiant de mesure, par exemple dans `wp-config.php` : `define( 'SE_GA4_ID', 'G-XXXXXXXXXX' );` (ou le filtre `se_ga4_id`). Le bandeau s’affiche alors, GA4 ne se charge qu’après « Accepter » (cookies limités à 13 mois), le choix est gardé 6 mois et un lien « Gérer les cookies » s’ajoute au pied de page. Sans identifiant, ni bandeau ni GA4. Ne pas ajouter GA4 par un autre plugin en plus.

### 4. Avis des lecteurs

Sur un test (une page contenant un bloc *Verdict box*), le formulaire de commentaire demande une note de 1 à 5 étoiles et l’ancienneté d’utilisation. La page affiche la moyenne, la répartition et notre note /10 à côté (jamais mélangées). Les réponses d’un membre de l’équipe sont signalées par le logo et un badge « Équipe ». À partir de 5 avis approuvés, la note moyenne est ajoutée au balisage `Review`.

Réglages conseillés dans *Réglages → Discussion* : « Le commentaire doit être approuvé manuellement », 1 lien maximum avant modération.

## Données structurées

Le bloc *Verdict box* produit un `Review` (note /10, auteur, dates) et le bloc *FAQ* un `FAQPage`. Si Rank Math génère déjà ces balisages, désactiver ceux du plugin :

```php
add_filter( 'se_reviews_output_schema', '__return_false' );
```

## Développement

- Modifier les couleurs ou espacements dans `design/tokens.json`, puis `php bin/build-tokens.php`.
- Le CSS des composants (`wordpress/plugins/se-reviews/assets/css/components.css`) reprend celui du design system ; les blocs s’adaptent à la largeur qu’on leur donne (requêtes de conteneur), pas seulement à celle de l’écran.
- Pas d’étape de compilation : le JavaScript de l’éditeur est un simple fichier (`assets/js/editor.js`).

### Ce qui a été testé

Sur un WordPress 7.1.3 local (SQLite) avec **Blocksy 2.1.59**, Blocksy Companion et Polylang 3.9 : création des fiches, toutes les compositions en anglais et en français, rendu public sur ordinateur (1280 px) et mobile (390 px), en-tête Blocksy configuré comme ci-dessus (`[se_wordmark]` et `[se_lang_switch]` dans des éléments HTML), éditeur de blocs (insertion, réglages, aperçu en direct), dépôt d’un avis noté par un visiteur, modification d’une fiche, redirection `/go/…`.
