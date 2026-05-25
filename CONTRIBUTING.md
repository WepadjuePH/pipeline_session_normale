# Guide de Contribution - Kaba-Delivery

Pour que notre historique reste propre, nous utilisons la convention **Conventional Commits**.

## Format des messages de commit
Chaque commit doit suivre cette structure : `type: description courte`

- **feat:** Nouvelle fonctionnalité (ex: `feat: ajout du suivi en temps réel`)
- **fix:** Correction d'un bug (ex: `fix: résolution du crash de l'API`)
- **chore:** Tâche de maintenance, configuration (ex: `chore: config de la CI`)
- **test:** Ajout ou modification de tests

## Règles de la forge
1. Ne travaillez jamais directement sur la branche `main`.
2. Créez une branche `feature/...` ou `fix/...` depuis `develop`.
3. Ouvrez une Pull Request vers `develop` pour fusionner.
