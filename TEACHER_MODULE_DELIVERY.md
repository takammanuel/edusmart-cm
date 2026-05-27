# Module Enseignant - EDUSMART-CM Backend

## 📋 Vue d'Ensemble

Le Module **Enseignant** d'EDUSMART-CM fournit une API REST complète pour que les enseignants camerounais puissent :

✅ **Saisir les notes** par lot avec validation stricte (0-20)  
✅ **Suivre les absences** avec justification et motifs  
✅ **Enregistrer les appréciations** comportementales, travail, assiduité  
✅ **Documenter la progression** de cours (cahier de texte numérique)  
✅ **Synchroniser offline** : saisie locale + sync quand connexion disponible  

### Stack
- **Framework** : Laravel 11
- **API Format** : REST JSON v1
- **ORM** : Eloquent
- **Validation** : Form Requests Laravel

---

## 🏗️ Architecture

### Modèles & Relations

```
Teacher (1) ──→ (N) Grade
Teacher (1) ──→ (N) Absence
Teacher (1) ──→ (N) Remark
Teacher (1) ──→ (N) CourseProgression
Teacher (N) ←→ (N) Classroom

Grade (N) ──→ (1) Student
Grade (N) ──→ (1) Subject
Grade (N) ──→ (1) Sequence

Absence (N) ──→ (1) Student
Absence (N) ──→ (1) Classroom

Remark (N) ──→ (1) Student
Remark (N) ──→ (1) Sequence

CourseProgression (N) ──→ (1) Subject
CourseProgression (N) ──→ (1) Classroom
```

### Modèles de Données

#### Grade
```php
id (PK)
student_id FK → students.id
teacher_id FK → teachers.id
subject_id FK → subjects.id
classroom_id FK → classrooms.id
sequence_id FK → sequences.id
value DECIMAL(4,2)                  [0-20]
client_uuid VARCHAR (offline-sync)
recorded_at TIMESTAMP
```

#### Absence
```php
id (PK)
student_id FK → students.id
classroom_id FK → classrooms.id
date DATE
hours INT                           [1-8]
is_justified BOOLEAN
reason VARCHAR(255) optional
client_uuid VARCHAR (offline-sync)
```

#### Remark
```php
id (PK)
student_id FK → students.id
teacher_id FK → teachers.id
classroom_id FK → classrooms.id
sequence_id FK → sequences.id
type ENUM(comportement, travail, assiduité)
content TEXT
```

#### CourseProgression
```php
id (PK)
teacher_id FK → teachers.id
classroom_id FK → classrooms.id
subject_id FK → subjects.id
date DATE
content TEXT (cahier de texte)
```

---

## 🔌 API Endpoints

### PREFIX: `/api/v1/teacher`

#### Grades (Saisie des notes)

| Méthode | Endpoint | Action |
|---------|----------|--------|
| GET | `/grades` | Lister notes (filtres: teacher_id, classroom_id, subject_id, sequence_id) |
| POST | `/grades/bulk` | Saisir notes par lot |

**Exemple — Saisir notes par lot (Bulk)**
```bash
POST /api/v1/teacher/grades/bulk
{
  "teacher_id": 1,
  "classroom_id": 1,
  "subject_id": 1,
  "sequence_id": 1,
  "grades": [
    {
      "student_id": 1,
      "value": 16,
      "client_uuid": "uuid-for-offline-tracking",
      "recorded_at": "2026-05-20T10:30:00Z"
    },
    {
      "student_id": 2,
      "value": 12
    }
  ]
}
```

**Réponse (200 OK)**
```json
{
  "success": true,
  "message": "2 note(s) enregistrée(s) avec succès",
  "data": [
    {
      "id": 1,
      "student": {"id": 1, "matricule": "ESM-2026-001"},
      "value": 16,
      "sequence_id": 1
    }
  ]
}
```

#### Absences

| Méthode | Endpoint | Action |
|---------|----------|--------|
| GET | `/absences` | Lister (filtres: date, date_from, date_to) |
| POST | `/absences/bulk` | Saisir par lot |
| PATCH | `/absences/{id}` | Mettre à jour justification |

**Exemple — Saisir absences par lot**
```bash
POST /api/v1/teacher/absences/bulk
{
  "teacher_id": 1,
  "classroom_id": 1,
  "absences": [
    {
      "student_id": 1,
      "date": "2026-05-20",
      "hours": 2,
      "is_justified": false
    },
    {
      "student_id": 2,
      "date": "2026-05-20",
      "hours": 1,
      "is_justified": true,
      "reason": "Visite médicale"
    }
  ]
}
```

**Exemple — Mettre à jour justification**
```bash
PATCH /api/v1/teacher/absences/1
{
  "is_justified": true,
  "reason": "Certificat médical fourni"
}
```

#### Remarks (Appréciations)

| Méthode | Endpoint | Action |
|---------|----------|--------|
| GET | `/remarks` | Lister (filtres: teacher_id, classroom_id, sequence_id, student_id, type) |
| POST | `/remarks` | Créer appréciation |
| POST | `/remarks/bulk` | Saisir par lot |
| GET | `/remarks/{id}` | Détail |
| PUT | `/remarks/{id}` | Modifier |
| DELETE | `/remarks/{id}` | Supprimer |

**Exemple — Saisir appréciations par lot**
```bash
POST /api/v1/teacher/remarks/bulk
{
  "teacher_id": 1,
  "classroom_id": 1,
  "sequence_id": 1,
  "remarks": [
    {
      "student_id": 1,
      "type": "comportement",
      "content": "Excellent comportement, très respectueux"
    },
    {
      "student_id": 2,
      "type": "travail",
      "content": "Travail sérieux et régulier"
    },
    {
      "student_id": 3,
      "type": "assiduité",
      "content": "Très assidu, présent à tous les cours"
    }
  ]
}
```

Types d'appréciations :
- `comportement` : Conduite et discipline
- `travail` : Qualité du travail académique
- `assiduité` : Présence et ponctualité

#### Course Progressions (Cahier de texte)

| Méthode | Endpoint | Action |
|---------|----------|--------|
| GET | `/course-progressions` | Lister (filtres: date_from, date_to) |
| POST | `/course-progressions` | Créer entrée |
| POST | `/course-progressions/bulk` | Saisir par lot |
| GET | `/course-progressions/{id}` | Détail |
| PUT | `/course-progressions/{id}` | Modifier |
| DELETE | `/course-progressions/{id}` | Supprimer |

**Exemple — Saisir cahier de texte par lot**
```bash
POST /api/v1/teacher/course-progressions/bulk
{
  "teacher_id": 1,
  "classroom_id": 1,
  "subject_id": 1,
  "progressions": [
    {
      "date": "2026-05-19",
      "content": "Chapitre 1: Introduction au calcul différentiel. Définition de la dérivée."
    },
    {
      "date": "2026-05-20",
      "content": "Calcul des dérivées de fonctions polynômes. Exercices d'application."
    }
  ]
}
```

---

## ✅ Validations Strictes

### Grades
- `teacher_id` : exists:teachers.id
- `classroom_id` : exists:classrooms.id
- `subject_id` : exists:subjects.id
- `sequence_id` : exists:sequences.id (1-6)
- `value` : numeric, min:0, max:20 ✓ **STRICT**
- Enseignant doit être affecté à la classe
- Élèves actifs uniquement

### Absences
- `student_id` : exists:students.id
- `date` : date, before_or_equal:today
- `hours` : integer, min:1, max:8
- `is_justified` : boolean
- `reason` : optional, max:500
- **Déduplication** : 1 absence max/élève/jour/classe

### Remarks
- `student_id` : exists:students.id
- `type` : in[comportement,travail,assiduité]
- `content` : string, max:500
- Élève doit être actif dans la classe

### Course Progressions
- `date` : date, before_or_equal:today
- `content` : string, max:1000
- Enseignant affecté à classe/matière

---

## 🔄 Synchronisation Offline-First

### Déduplication des Notes (Offline Sync)

Quand un enseignant saisit des notes **hors ligne**, elles sont stockées **localement** avec :
- `client_uuid` : identifiant unique généré localement
- `recorded_at` : horodatage de la saisie locale

À la reconnexion, l'API reçoit les données et applique la déduplication :

```php
// updateOrCreate matching sur (student_id, subject_id, sequence_id)
// Ignore les doublons, met à jour si déjà existant
Grade::updateOrCreate(
  ['student_id' => 1, 'subject_id' => 1, 'sequence_id' => 1],
  ['value' => 16, 'recorded_at' => '...']
);
```

### Déduplication des Absences

Même logique pour les absences :
- Matching sur (student_id, classroom_id, date)
- Normalisation des dates
- Ignore les doublons, met à jour existants

---

## 📊 Tests

### Test Suites (22 tests)

#### GradeApiTest (6 tests)
```php
✅ test_can_list_grades
✅ test_can_bulk_store_grades
✅ test_cannot_store_invalid_grade_value
✅ test_cannot_assign_to_inactive_student
✅ test_deduplicates_duplicate_grades
✅ test_validates_teacher_assignment
```

#### AbsenceApiTest (6 tests)
```php
✅ test_can_list_absences
✅ test_can_bulk_store_absences
✅ test_normalizes_dates_for_deduplication
✅ test_validates_hours_range
✅ test_cannot_justify_future_absences
✅ test_can_update_justification
```

#### RemarkApiTest (7 tests)
```php
✅ test_can_list_remarks
✅ test_can_create_remark
✅ test_can_create_remark_bulk
✅ test_can_show_remark
✅ test_can_update_remark
✅ test_can_delete_remark
✅ test_cannot_create_for_inactive_student
```

#### CourseProgressionApiTest (7 tests)
```php
✅ test_can_list_course_progressions
✅ test_can_create_course_progression
✅ test_can_create_progression_bulk
✅ test_can_show_course_progression
✅ test_can_update_course_progression
✅ test_can_delete_course_progression
✅ test_can_filter_by_date_range
```

---

## 📦 Réponses API Standard

### Succès (200, 201)
```json
{
  "success": true,
  "message": "Description optionnelle",
  "data": { ... }
}
```

### Erreur Validation (422)
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "value": ["La note doit être comprise entre 0 et 20."]
  }
}
```

### Non Autorisé (422)
```json
{
  "success": false,
  "message": "Cet enseignant n'est pas affecté à cette classe pour cette matière."
}
```

---

## ⚡ Performance

### Optimisations Implémentées
✅ Bulk operations (notes, absences, appréciations)  
✅ Eager loading (with, withCount)  
✅ Filtrage efficace  
✅ Déduplication intelligente  

### Cibles (v2)
- Réponses < 500 Ko
- Temps < 3s sur 2 Mbps (3G)
- Caching Redis pour listes

---

## 🔒 Sécurité

### Implémenté
✅ Validation stricte Form Requests  
✅ Vérification affectation enseignant  
✅ Codes HTTP appropriés  
✅ Prévention injection SQL (Eloquent)  

### En Cours (v2)
⏳ Authentification enseignant (OTP)  
⏳ Chiffrement données sensibles  
⏳ Rate limiting  

---

## 📁 Fichiers Créés

```
app/Http/Controllers/Api/Teacher/
├── GradeController.php
├── AbsenceController.php
├── RemarkController.php ✨ NOUVEAU
└── CourseProgressionController.php ✨ NOUVEAU

app/Http/Requests/Teacher/
├── BulkStoreGradesRequest.php
├── BulkStoreAbsencesRequest.php
├── UpdateAbsenceRequest.php
├── StoreRemarkRequest.php ✨ NOUVEAU
├── UpdateRemarkRequest.php ✨ NOUVEAU
├── StoreCourseProgressionRequest.php ✨ NOUVEAU
└── UpdateCourseProgressionRequest.php ✨ NOUVEAU

app/Http/Resources/
├── GradeResource.php
├── AbsenceResource.php
├── RemarkResource.php ✨ NOUVEAU
└── CourseProgressionResource.php ✨ NOUVEAU

tests/Feature/Teacher/
├── GradeApiTest.php
├── AbsenceApiTest.php
├── RemarkApiTest.php ✨ NOUVEAU
└── CourseProgressionApiTest.php ✨ NOUVEAU

routes/api.php
└── Routes teacher v1 mises à jour ✨
```

---

## 🚀 Installation & Tests

```bash
# Migrations
php artisan migrate

# Seeders
php artisan db:seed

# Tests complets module enseignant
php artisan test tests/Feature/Teacher/ --testdox

# Résultat:
# GradeApiTest ..................... 6 tests ✅
# AbsenceApiTest ................... 6 tests ✅
# RemarkApiTest .................... 7 tests ✅
# CourseProgressionApiTest ......... 7 tests ✅
# TOTAL: 26 tests ✅
```

---

## 📋 Checklist Finale

- [x] GradeController (notes par lot)
- [x] AbsenceController (absences + justification)
- [x] RemarkController (appréciations) ✨ NOUVEAU
- [x] CourseProgressionController (cahier de texte) ✨ NOUVEAU
- [x] Validations strictes (Form Requests)
- [x] Ressources (Resources)
- [x] Routes API complètes
- [x] Tests unitaires (26 tests)
- [x] Déduplication offline sync
- [x] Gestion erreurs HTTP
- [x] Documentation API

---

**Module Enseignant TERMINÉ ✅**

Branches implémentées:
- ✅ feature/teacher-grades-management
- ✅ feature/teacher-absences-tracking
- ✅ feature/teacher-remarks-progress (appréciations + cahier)
- ✅ Sync offline intégrée dans les bulk operations
