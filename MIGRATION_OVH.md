# Migration Bar'OOF vers OVH

## 📋 Prérequis
- Accès FTP OVH
- Domaine baroof.fr pointé vers OVH
- Hébergement web OVH avec PHP

## 📁 Fichiers à uploader via FTP

Tous les fichiers du projet doivent être uploadés dans le dossier `www` ou `public_html` de l'hébergement OVH :

```
/www/
├── index.html
├── contact.php ⭐ (nouveau - gère le formulaire)
├── script.js ⭐ (modifié - appelle contact.php)
├── style.css
├── .htaccess ⭐ (nouveau - config Apache)
├── favicon.ico
├── favicon.png
├── apple-touch-icon.png
├── logo.svg
├── hero-cocktail.mp4
├── atelier1.jpg, atelier2.jpg, atelier3.jpg, atelier4.jpg
├── cocktail1.jpg, cocktail2.jpg, cocktail3.jpg
├── slide1.jpg, slide2.jpg, slide3.jpg
├── temoignage2.jpg, temoignage4.jpg, temoignage5.jpg
├── client-*.png (tous les logos clients)
├── thomas-avatar.jpg
├── tony-avatar.jpg
└── ludovik-avatar.jpg
```

## 🔧 Configuration Email

### Option 1 : Utiliser la fonction mail() PHP (par défaut)
Le fichier `contact.php` utilise la fonction `mail()` de PHP qui fonctionne directement avec OVH.

**Email de réception actuel :** `ldvk@me.com` (pour tests)

**Pour changer vers l'email Baroof :**
1. Créer l'adresse `contact@baroof.fr` dans le manager OVH
2. Modifier dans `contact.php` ligne 8 :
   ```php
   $to = 'contact@baroof.fr'; // Au lieu de ldvk@me.com
   ```

### Option 2 : SMTP OVH (si mail() ne fonctionne pas)
Si la fonction mail() ne marche pas, utiliser PHPMailer avec SMTP OVH :
- Serveur SMTP : `ssl0.ovh.net`
- Port : `587` (TLS) ou `465` (SSL)
- Authentification : email@baroof.fr + mot de passe

## 🌐 DNS - Vérifier que le domaine pointe vers OVH

Dans l'interface OVH DNS, vérifier :
```
@ (ou vide)  →  A  →  IP du serveur OVH
www          →  CNAME  →  baroof.fr
```

## 📊 Google Analytics
✅ Déjà configuré : `G-VDN1M2323N`

## ✅ Checklist après migration

- [ ] Tester le site sur https://baroof.fr
- [ ] Tester le formulaire de contact (devrait envoyer à ldvk@me.com)
- [ ] Vérifier que le HTTPS fonctionne (force HTTPS dans .htaccess)
- [ ] Vérifier Google Analytics dans la console GA
- [ ] Créer contact@baroof.fr dans OVH
- [ ] Modifier contact.php pour utiliser contact@baroof.fr
- [ ] Tester à nouveau le formulaire

## 🔑 Accès FTP OVH

**Serveur FTP :** ftp.cluster0XX.hosting.ovh.net (vérifier dans le manager OVH)
**Login :** identifiant FTP (fourni par OVH)
**Password :** mot de passe FTP

**Client FTP recommandé :**
- FileZilla (gratuit) : https://filezilla-project.org/
- Cyberduck (macOS) : https://cyberduck.io/

## 📝 Notes
- Le site actuel sur GitHub Pages continuera de fonctionner jusqu'à ce que le DNS pointe vers OVH
- Une fois migré, tu peux désactiver GitHub Pages
- Les emails de test vont sur `ldvk@me.com` pour vérifier que ça marche
- N'oublie pas de changer vers `contact@baroof.fr` une fois créé dans OVH
