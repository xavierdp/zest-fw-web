# Cahier des Charges - ZestPHP Web Framework

## 1. Introduction

### 1.1 Contexte
Le projet consiste à développer un framework web appelé "ZestPHP Web" qui s'appuie sur le framework existant "ZestPHP Core". Ce nouveau framework ajoutera des fonctionnalités orientées web tout en conservant la simplicité et l'efficacité du framework de base.

### 1.2 Objectifs
- Créer une extension web au framework ZestPHP Core
- Intégrer des technologies modernes pour le développement frontend
- Maintenir la compatibilité avec l'architecture existante
- Faciliter le développement d'applications web complètes

## 2. Architecture Technique

### 2.1 Technologies Utilisées
- **Backend**: PHP, ZestPHP Core
- **Frontend**: 
  - Twig (moteur de templates)
  - jQuery (manipulation DOM)
  - TailwindCSS (framework CSS)

### 2.2 Structure du Projet
Le framework respectera l'organisation suivante:
```
/apps/fw-v5/zest-fw-web      # Code source du framework web
/apps/fw-v5/zest-fw-core     # Framework core (ne pas modifier)
/apps/fw-v5/app              # Application utilisant le framework
/apps/fw-v5/app/webroot      # Point d'entrée web
/apps/fw-v5/app/webroot/static        # Ressources statiques
/apps/fw-v5/app/webroot/static/css    # Fichiers CSS
/apps/fw-v5/app/webroot/static/js     # Fichiers JavaScript
/apps/fw-v5/app/webroot/static/img    # Images
/apps/fw-v5/app/webroot/static/fonts  # Polices
```

### 2.3 Mécanisme d'Inclusion
- ZestPHP Web inclura ZestPHP Core
- Les applications incluront ZestPHP Web (qui lui-même inclut Core)

## 3. Fonctionnalités Requises

### 3.1 Système de Routage
- Utiliser le système de routage existant de ZestPHP Core
- Étendre si nécessaire pour les besoins spécifiques au web

### 3.2 Moteur d'API
- Réutiliser le moteur d'API de ZestPHP Core
- Assurer la compatibilité avec les nouvelles fonctionnalités web

### 3.3 Moteur de Templates
- Développer un nouveau moteur de templates basé sur Twig
- Intégrer des fonctions d'aide pour simplifier le développement frontend
- Permettre l'utilisation de layouts et de composants réutilisables

### 3.4 Gestion des Assets
- Mettre en place un système de gestion des ressources statiques (CSS, JS, images)
- Organiser les assets dans la structure `/webroot/static/`
- Fournir des mécanismes pour l'inclusion optimisée des assets

### 3.5 Intégration Frontend
- Intégrer jQuery pour la manipulation du DOM
- Configurer TailwindCSS pour le styling
- Fournir des composants UI réutilisables

### 3.6 Gestion des Dépendances
- Utiliser Composer pour gérer les dépendances PHP (Twig, etc.)
- Installer les dépendances dans le répertoire `/apps/fw-v5/app/lib`
- Conserver les fichiers de configuration (composer.json, composer.lock) dans `zest-fw-web/boilerplate/lib`
- Assurer la portabilité des dépendances entre les environnements

### 3.7 Système de Composants
- Implémenter un système de composants réutilisables avec une structure standardisée
- Chaque composant sera organisé dans un dossier dédié sous `app/webroot/static/components/{component_name}/`
- Structure de fichiers par composant :
  - `{component_name}.php` : Classe PHP du composant
  - `{component_name}.twig` : Template Twig du composant
  - `{component_name}.js` : JavaScript du composant
  - `{component_name}.css` : Styles CSS du composant
- Autoload automatique des classes de composants
- Intégration transparente des assets (CSS/JS) des composants dans les templates
- Communication AJAX entre les composants et l'API via les classes PHP des composants

## 4. Contraintes et Exigences

### 4.1 Compatibilité
- Maintenir la compatibilité avec l'écosystème turbinobash-web
- Assurer le fonctionnement dans la structure de répertoires standard

### 4.2 Restrictions
- Ne pas modifier le code source de ZestPHP Core sans autorisation explicite
- Développer et tester dans les répertoires spécifiés:
  - `/apps/fw-v5/zest-fw-web` pour le framework
  - `/apps/fw-v5/app` pour les tests d'application

### 4.3 Performance
- Minimiser l'overhead ajouté par les nouvelles fonctionnalités
- Optimiser le chargement des ressources frontend

## 5. Livrables

### 5.1 Code Source
- Framework ZestPHP Web complet
- Documentation d'utilisation
- Exemples d'intégration

### 5.2 Démonstrateur
- Application exemple utilisant toutes les fonctionnalités du framework
- Démonstration des capacités de templating et d'intégration frontend

## 6. Méthodologie de Développement

### 6.1 Approche
- Développement itératif
- Tests réguliers dans l'environnement cible

### 6.2 Phases de Développement
1. Mise en place de la structure de base et intégration avec ZestPHP Core
2. Développement du moteur de templates Twig
3. Intégration des technologies frontend (jQuery, TailwindCSS)
4. Création des mécanismes de gestion d'assets
5. Tests et optimisations
6. Documentation et finalisation

## 7. Conclusion

Ce cahier des charges définit les bases du développement du framework ZestPHP Web, qui viendra enrichir l'écosystème ZestPHP avec des fonctionnalités orientées web tout en conservant la philosophie de simplicité et d'efficacité du framework core.
