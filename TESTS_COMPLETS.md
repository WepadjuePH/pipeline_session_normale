# Tests Complets - SGECN

## 📋 Vue d'Ensemble

Ce document liste tous les tests créés pour le système SGECN (Système de Gestion d'Enrôlement aux Concours Nationaux).

---

## 🧪 Tests Feature (Intégration)

### 1. AuthTest.php
Tests d'authentification et gestion des utilisateurs.

**Tests inclus:**
- ✅ `test_un_utilisateur_peut_se_connecter` - Connexion avec email/password
- ✅ `test_la_connexion_echoue_avec_mauvais_mot_de_passe` - Validation mot de passe
- ✅ `test_un_utilisateur_peut_s_inscrire` - Inscription nouveau candidat
- ✅ `test_l_inscription_echoue_avec_email_deja_utilise` - Unicité email
- ✅ `test_un_utilisateur_peut_se_deconnecter` - Déconnexion
- ✅ `test_un_utilisateur_peut_rafraichir_son_token` - Refresh JWT
- ✅ `test_un_utilisateur_peut_obtenir_son_profil` - Récupération profil

**Commande:**
```bash
php artisan test tests/Feature/AuthTest.php
```

---

### 2. CandidatureTest.php
Tests de gestion des candidatures par les candidats.

**Tests inclus:**
- ✅ `test_un_candidat_peut_soumettre_une_candidature` - Soumission complète
- ✅ `test_un_candidat_ne_peut_pas_soumettre_sans_documents` - Validation documents
- ✅ `test_un_candidat_peut_voir_ses_candidatures` - Liste candidatures
- ✅ `test_un_agent_depot_peut_valider_une_candidature` - Validation agent
- ✅ `test_un_agent_depot_peut_rejeter_une_candidature` - Rejet avec motif
- ✅ `test_un_candidat_ne_peut_pas_acceder_aux_candidatures_dun_autre` - Sécurité

**Commande:**
```bash
php artisan test tests/Feature/CandidatureTest.php
```

---

### 3. ConcoursTest.php
Tests de gestion des concours.

**Tests inclus:**
- ✅ `test_un_candidat_peut_voir_les_concours_ouverts` - Liste concours
- ✅ `test_un_admin_peut_creer_un_concours` - Création par admin
- ✅ `test_un_candidat_ne_peut_pas_creer_un_concours` - Contrôle permissions
- ✅ `test_un_admin_peut_modifier_un_concours` - Modification
- ✅ `test_un_admin_peut_supprimer_un_concours` - Suppression
- ✅ `test_un_concours_ferme_n_accepte_pas_de_candidatures` - Validation statut

**Commande:**
```bash
php artisan test tests/Feature/ConcoursTest.php
```

---

### 4. AgentDepotTest.php
Tests des fonctionnalités agent de dépôt.

**Tests inclus:**
- ✅ `test_un_agent_depot_peut_voir_les_candidatures_de_son_centre` - Liste centre
- ✅ `test_un_agent_depot_peut_valider_une_candidature` - Validation
- ✅ `test_un_agent_depot_peut_rejeter_une_candidature` - Rejet
- ✅ `test_un_agent_depot_ne_peut_pas_valider_candidature_autre_centre` - Sécurité
- ✅ `test_un_agent_depot_peut_generer_un_rapport` - Création rapport
- ✅ `test_un_agent_depot_peut_voir_ses_rapports` - Liste rapports
- ✅ `test_un_agent_depot_peut_annuler_une_validation` - Annulation

**Commande:**
```bash
php artisan test tests/Feature/AgentDepotTest.php
```

---

### 5. AgentExamenTest.php
Tests des fonctionnalités agent d'examen.

**Tests inclus:**
- ✅ `test_un_agent_examen_peut_voir_les_candidatures_de_son_centre` - Liste centre
- ✅ `test_un_agent_examen_peut_scanner_un_qr_code` - Scan QR
- ✅ `test_un_agent_examen_peut_marquer_presence` - Présence candidat
- ✅ `test_un_agent_examen_peut_generer_un_rapport` - Rapport examen
- ✅ `test_un_agent_examen_ne_peut_pas_scanner_candidat_autre_centre` - Sécurité

**Commande:**
```bash
php artisan test tests/Feature/AgentExamenTest.php
```

---

### 6. AdminTest.php
Tests des fonctionnalités administrateur.

**Tests inclus:**
- ✅ `test_un_admin_peut_voir_toutes_les_candidatures` - Vue globale
- ✅ `test_un_admin_peut_creer_un_utilisateur` - Création utilisateur
- ✅ `test_un_admin_peut_modifier_un_utilisateur` - Modification
- ✅ `test_un_admin_peut_supprimer_un_utilisateur` - Suppression
- ✅ `test_un_admin_peut_voir_les_statistiques` - Dashboard stats
- ✅ `test_un_admin_peut_creer_un_centre_depot` - Création centre dépôt
- ✅ `test_un_admin_peut_creer_un_centre_examen` - Création centre examen
- ✅ `test_un_admin_peut_voir_tous_les_rapports` - Vue rapports
- ✅ `test_un_admin_peut_exporter_les_candidatures` - Export données
- ✅ `test_un_non_admin_ne_peut_pas_acceder_aux_fonctions_admin` - Sécurité

**Commande:**
```bash
php artisan test tests/Feature/AdminTest.php
```

---

### 7. NotificationTest.php
Tests du système de notifications.

**Tests inclus:**
- ✅ `test_notification_envoyee_apres_soumission_candidature` - Notif soumission
- ✅ `test_notification_envoyee_apres_validation` - Notif validation
- ✅ `test_notification_envoyee_apres_rejet` - Notif rejet
- ✅ `test_un_candidat_peut_voir_ses_notifications` - Liste notifications
- ✅ `test_un_candidat_peut_marquer_notification_comme_lue` - Marquer lu

**Commande:**
```bash
php artisan test tests/Feature/NotificationTest.php
```

---

### 8. RapportTest.php
Tests de gestion des rapports.

**Tests inclus:**
- ✅ `test_un_agent_peut_creer_un_rapport` - Création
- ✅ `test_un_agent_peut_voir_ses_rapports` - Liste
- ✅ `test_un_agent_peut_modifier_un_rapport_brouillon` - Modification brouillon
- ✅ `test_un_agent_ne_peut_pas_modifier_un_rapport_envoye` - Protection envoyé
- ✅ `test_un_agent_peut_envoyer_un_rapport` - Envoi
- ✅ `test_un_agent_peut_supprimer_un_rapport_brouillon` - Suppression
- ✅ `test_un_admin_peut_voir_tous_les_rapports` - Vue admin
- ✅ `test_un_agent_ne_peut_voir_que_ses_rapports` - Isolation

**Commande:**
```bash
php artisan test tests/Feature/RapportTest.php
```

---

## 🔬 Tests Unit (Unitaires)

### 1. UserModelTest.php
Tests du modèle User.

**Tests inclus:**
- ✅ `test_un_utilisateur_peut_etre_cree` - Création
- ✅ `test_le_mot_de_passe_est_hash` - Hashing password
- ✅ `test_le_role_par_defaut_est_candidat` - Rôle par défaut
- ✅ `test_un_utilisateur_peut_avoir_des_candidatures` - Relation
- ✅ `test_verification_du_role_admin` - Rôle admin
- ✅ `test_verification_du_role_agent_depot` - Rôle agent dépôt
- ✅ `test_verification_du_role_agent_examen` - Rôle agent examen
- ✅ `test_le_telephone_est_requis` - Validation téléphone
- ✅ `test_l_email_doit_etre_unique` - Unicité email

**Commande:**
```bash
php artisan test tests/Unit/UserModelTest.php
```

---

### 2. ConcoursModelTest.php
Tests du modèle Concours.

**Tests inclus:**
- ✅ `test_un_concours_peut_etre_cree` - Création
- ✅ `test_le_code_doit_etre_unique` - Unicité code
- ✅ `test_un_concours_peut_avoir_des_candidatures` - Relation
- ✅ `test_les_dates_sont_converties_en_carbon` - Cast dates
- ✅ `test_le_statut_par_defaut_est_ouvert` - Statut défaut
- ✅ `test_verification_des_statuts_possibles` - Validation statuts

**Commande:**
```bash
php artisan test tests/Unit/ConcoursModelTest.php
```

---

### 3. CandidatureModelTest.php
Tests du modèle Candidature.

**Tests inclus:**
- ✅ `test_une_candidature_peut_etre_creee` - Création
- ✅ `test_le_code_candidat_doit_etre_unique` - Unicité code
- ✅ `test_une_candidature_appartient_a_un_utilisateur` - Relation user
- ✅ `test_une_candidature_appartient_a_un_concours` - Relation concours
- ✅ `test_le_statut_par_defaut_est_en_attente` - Statut défaut
- ✅ `test_verification_des_statuts_possibles` - Validation statuts
- ✅ `test_la_date_naissance_est_convertie_en_carbon` - Cast date

**Commande:**
```bash
php artisan test tests/Unit/CandidatureModelTest.php
```

---

### 4. CandidatureServiceTest.php
Tests du service Candidature.

**Tests inclus:**
- ✅ `test_generer_code_candidat_retourne_un_code_valide` - Génération code
- ✅ `test_peut_etre_validee_retourne_false_si_documents_manquants` - Validation docs
- ✅ `test_peut_etre_validee_retourne_true_si_tous_documents_presents` - Validation OK
- ✅ `test_peut_etre_validee_retourne_false_si_deja_valide` - Protection double validation

**Commande:**
```bash
php artisan test tests/Unit/CandidatureServiceTest.php
```

---

## 📊 Statistiques des Tests

### Répartition
- **Tests Feature:** 8 fichiers, ~60 tests
- **Tests Unit:** 4 fichiers, ~30 tests
- **Total:** 12 fichiers, ~90 tests

### Couverture Fonctionnelle
- ✅ Authentification (7 tests)
- ✅ Candidatures (6 tests)
- ✅ Concours (6 tests)
- ✅ Agent Dépôt (7 tests)
- ✅ Agent Examen (5 tests)
- ✅ Administration (10 tests)
- ✅ Notifications (5 tests)
- ✅ Rapports (8 tests)
- ✅ Modèles (30 tests)

---

## 🚀 Commandes Utiles

### Exécuter tous les tests
```bash
php artisan test
```

### Exécuter les tests Feature uniquement
```bash
php artisan test tests/Feature/
```

### Exécuter les tests Unit uniquement
```bash
php artisan test tests/Unit/
```

### Exécuter un fichier spécifique
```bash
php artisan test tests/Feature/AuthTest.php
```

### Exécuter un test spécifique
```bash
php artisan test --filter=test_un_utilisateur_peut_se_connecter
```

### Arrêter au premier échec
```bash
php artisan test --stop-on-failure
```

### Voir les tests lents
```bash
php artisan test --profile
```

### Exécuter en parallèle (plus rapide)
```bash
php artisan test --parallel
```

---

## 🔧 Configuration Requise

### 1. Base de Données de Test
Créer un fichier `.env.testing`:
```env
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

### 2. Factories
Les factories suivantes doivent être configurées:
- ✅ UserFactory (déjà créée)
- ConcoursFactory (optionnel)
- CandidatureFactory (optionnel)

### 3. Seeders pour Tests
Les seeders suivants sont utilisés:
- RegionSeeder
- CentreSeeder
- ConcoursSeeder
- UserSeeder

---

## 📝 Bonnes Pratiques Appliquées

1. **RefreshDatabase** - Base réinitialisée après chaque test
2. **setUp()** - Configuration commune dans setUp()
3. **Noms descriptifs** - Tests nommés clairement
4. **Isolation** - Chaque test est indépendant
5. **Assertions claires** - Vérifications explicites
6. **Factories** - Génération de données cohérentes
7. **Fake Notifications** - Tests sans envoi réel
8. **Fake Storage** - Tests sans fichiers réels

---

## 🐛 Dépannage

### Erreur: "Table not found"
```bash
php artisan migrate:fresh --env=testing
```

### Erreur: "Class not found"
```bash
composer dump-autoload
```

### Tests lents
```bash
# Utiliser SQLite en mémoire
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

### Erreur de permissions
```bash
# Windows
icacls storage /grant Users:F /t
icacls bootstrap/cache /grant Users:F /t
```

---

## 📚 Ressources

- [Documentation Laravel Testing](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Guide des Tests SGECN](GUIDE_TESTS.md)

---

## ✅ Checklist Avant Production

- [ ] Tous les tests passent
- [ ] Couverture > 80%
- [ ] Tests de sécurité OK
- [ ] Tests de permissions OK
- [ ] Tests de validation OK
- [ ] Tests d'intégration OK
- [ ] Tests de performance OK

---

**Dernière mise à jour:** 31 Janvier 2026
**Version:** 1.0.0
**Auteur:** Équipe SGECN
