# Guide des Tests Laravel - SGECN

## 📚 Table des Matières
1. [Introduction](#introduction)
2. [Configuration](#configuration)
3. [Types de Tests](#types-de-tests)
4. [Commandes de Base](#commandes-de-base)
5. [Exemples Pratiques](#exemples-pratiques)
6. [Bonnes Pratiques](#bonnes-pratiques)

---

## Introduction

Les tests automatisés permettent de vérifier que votre application fonctionne correctement et d'éviter les régressions lors des modifications.

## Configuration

### 1. Base de Données de Test

Dans votre fichier `.env`, vous pouvez configurer une base de données séparée pour les tests:

```env
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

Ou créer un fichier `.env.testing`:

```env
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=database/testing.sqlite
```

### 2. Configuration PHPUnit

Le fichier `phpunit.xml` est déjà configuré. Vérifiez qu'il contient:

```xml
<env name="APP_ENV" value="testing"/>
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

---

## Types de Tests

### 1. **Feature Tests** (tests/Feature/)
Testent des fonctionnalités complètes (routes, API, workflows)

**Exemple:** Tester la soumission d'une candidature

### 2. **Unit Tests** (tests/Unit/)
Testent des fonctions/méthodes isolées

**Exemple:** Tester la génération d'un code candidat

---

## Commandes de Base

```bash
# Exécuter tous les tests
php artisan test

# Exécuter avec PHPUnit directement
vendor/bin/phpunit

# Exécuter un fichier spécifique
php artisan test tests/Feature/CandidatureTest.php

# Exécuter une méthode spécifique
php artisan test --filter=un_candidat_peut_soumettre_une_candidature

# Afficher plus de détails
php artisan test --verbose

# Voir la couverture de code (nécessite Xdebug)
php artisan test --coverage

# Exécuter en parallèle (plus rapide)
php artisan test --parallel
```

---

## Exemples Pratiques

### Exemple 1: Tester une Route API

```php
/** @test */
public function un_candidat_peut_voir_ses_candidatures()
{
    // 1. Créer un utilisateur
    $candidat = User::factory()->create(['role' => 'candidat']);
    
    // 2. S'authentifier
    $token = auth()->login($candidat);
    
    // 3. Faire la requête
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/candidat/candidatures');
    
    // 4. Vérifier la réponse
    $response->assertStatus(200)
             ->assertJsonStructure(['candidatures']);
}
```

### Exemple 2: Tester avec Upload de Fichiers

```php
/** @test */
public function un_candidat_peut_uploader_des_documents()
{
    Storage::fake('public'); // Simuler le stockage
    
    $candidat = User::factory()->create(['role' => 'candidat']);
    $token = auth()->login($candidat);
    
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/candidat/candidatures', [
        'document_cni' => UploadedFile::fake()->image('cni.jpg'),
        'photo_candidat' => UploadedFile::fake()->image('photo.jpg'),
        // ... autres données
    ]);
    
    $response->assertStatus(201);
    
    // Vérifier que le fichier a été stocké
    Storage::disk('public')->assertExists('documents/document_cni/...');
}
```

### Exemple 3: Tester la Validation

```php
/** @test */
public function la_soumission_echoue_sans_documents_requis()
{
    $candidat = User::factory()->create(['role' => 'candidat']);
    $token = auth()->login($candidat);
    
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/candidat/candidatures', [
        'nom' => 'Test',
        // Documents manquants
    ]);
    
    $response->assertStatus(422) // Validation error
             ->assertJsonValidationErrors(['document_cni', 'photo_candidat']);
}
```

### Exemple 4: Tester les Permissions

```php
/** @test */
public function un_candidat_ne_peut_pas_valider_une_candidature()
{
    $candidat = User::factory()->create(['role' => 'candidat']);
    $token = auth()->login($candidat);
    
    $candidature = Candidature::factory()->create();
    
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson("/api/agent/depot/candidatures/{$candidature->id}/valider");
    
    $response->assertStatus(403); // Forbidden
}
```

### Exemple 5: Tester la Base de Données

```php
/** @test */
public function la_candidature_est_enregistree_en_base()
{
    $candidat = User::factory()->create();
    $concours = Concours::factory()->create();
    
    Candidature::create([
        'user_id' => $candidat->id,
        'concours_id' => $concours->id,
        'code_candidat' => 'TEST-001',
        'statut' => 'en_attente',
        // ... autres champs
    ]);
    
    // Vérifier que l'enregistrement existe
    $this->assertDatabaseHas('candidatures', [
        'user_id' => $candidat->id,
        'code_candidat' => 'TEST-001',
        'statut' => 'en_attente'
    ]);
    
    // Vérifier qu'un enregistrement n'existe pas
    $this->assertDatabaseMissing('candidatures', [
        'statut' => 'valide_depot'
    ]);
}
```

### Exemple 6: Tester les Notifications

```php
use Illuminate\Support\Facades\Notification;

/** @test */
public function une_notification_est_envoyee_apres_validation()
{
    Notification::fake();
    
    $candidat = User::factory()->create();
    $candidature = Candidature::factory()->create(['user_id' => $candidat->id]);
    
    // Valider la candidature
    $candidature->update(['statut' => 'valide_depot']);
    
    // Vérifier que la notification a été envoyée
    Notification::assertSentTo(
        $candidat,
        CandidatureValidatedNotification::class
    );
}
```

---

## Bonnes Pratiques

### 1. **Utiliser RefreshDatabase**
```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class CandidatureTest extends TestCase
{
    use RefreshDatabase; // Réinitialise la DB après chaque test
}
```

### 2. **Utiliser setUp() pour la Configuration Commune**
```php
protected function setUp(): void
{
    parent::setUp();
    
    // Configuration commune à tous les tests
    $this->candidat = User::factory()->create(['role' => 'candidat']);
    $this->concours = Concours::factory()->create();
}
```

### 3. **Nommer les Tests Clairement**
```php
// ✅ BON
public function un_candidat_peut_soumettre_une_candidature()

// ❌ MAUVAIS
public function test1()
```

### 4. **Un Test = Une Assertion Principale**
Chaque test doit vérifier une seule chose principale.

### 5. **Utiliser les Factories**
Créez des factories pour générer facilement des données de test:

```bash
php artisan make:factory CandidatureFactory
```

```php
// database/factories/CandidatureFactory.php
public function definition()
{
    return [
        'user_id' => User::factory(),
        'concours_id' => Concours::factory(),
        'code_candidat' => 'TEST-' . $this->faker->unique()->numberBetween(1000, 9999),
        'statut' => 'en_attente',
        'nom' => $this->faker->lastName,
        'prenom' => $this->faker->firstName,
        // ...
    ];
}
```

### 6. **Tester les Cas d'Erreur**
Ne testez pas seulement les cas de succès!

```php
/** @test */
public function la_validation_echoue_avec_email_invalide()
{
    $response = $this->postJson('/api/register', [
        'email' => 'invalid-email', // Email invalide
    ]);
    
    $response->assertStatus(422);
}
```

---

## Structure d'un Test Complet

```php
/** @test */
public function description_claire_du_comportement_teste()
{
    // 1. ARRANGE - Préparer les données
    $candidat = User::factory()->create(['role' => 'candidat']);
    $concours = Concours::factory()->create();
    
    // 2. ACT - Exécuter l'action
    $response = $this->actingAs($candidat)
                     ->postJson('/api/candidat/candidatures', [
                         'concours_id' => $concours->id,
                         // ... données
                     ]);
    
    // 3. ASSERT - Vérifier le résultat
    $response->assertStatus(201);
    $this->assertDatabaseHas('candidatures', [
        'user_id' => $candidat->id,
        'statut' => 'en_attente'
    ]);
}
```

---

## Assertions Courantes

```php
// Réponses HTTP
$response->assertStatus(200);
$response->assertOk();
$response->assertCreated(); // 201
$response->assertNoContent(); // 204
$response->assertNotFound(); // 404
$response->assertForbidden(); // 403
$response->assertUnauthorized(); // 401

// JSON
$response->assertJson(['key' => 'value']);
$response->assertJsonStructure(['data' => ['id', 'name']]);
$response->assertJsonCount(5, 'data');

// Base de données
$this->assertDatabaseHas('candidatures', ['id' => 1]);
$this->assertDatabaseMissing('candidatures', ['statut' => 'invalide']);
$this->assertDatabaseCount('candidatures', 10);

// Authentification
$this->assertAuthenticated();
$this->assertGuest();

// Redirections
$response->assertRedirect('/dashboard');
```

---

## Exécuter les Tests

```bash
# Tous les tests
php artisan test

# Avec détails
php artisan test --verbose

# Un fichier spécifique
php artisan test tests/Feature/CandidatureTest.php

# Arrêter au premier échec
php artisan test --stop-on-failure

# Voir les tests lents
php artisan test --profile
```

---

## Déboguer les Tests

```php
// Afficher la réponse
$response->dump();
$response->dumpHeaders();
$response->dumpSession();

// Afficher et arrêter
$response->dd();

// Dans le test
dump($candidature);
dd($candidature);
```

---

## Ressources

- [Documentation Laravel Testing](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- Tests créés: `tests/Feature/CandidatureTest.php` et `tests/Unit/CandidatureServiceTest.php`

---

## Commencer Maintenant

```bash
# 1. Exécuter les tests existants
php artisan test

# 2. Créer un nouveau test
php artisan make:test NomDuTest

# 3. Créer un test unitaire
php artisan make:test NomDuTest --unit
```

Bonne chance avec vos tests! 🚀
