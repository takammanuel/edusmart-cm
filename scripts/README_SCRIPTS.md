# Guide d'utilisation des scripts Git — EduSmart CM

## Comment ca fonctionne

```
Projet existant (ta machine)
        |
        | script_mli.ps1 : repointe l'URL + git push
        v
Nouveau depot GitHub (vide)  <-- tout le vrai code arrive ici
        |
        |-- Membre 2 clone + fait ses commits sur ses fichiers
        |-- Membre 3 clone + fait ses commits sur ses fichiers
```

Le vrai code du projet est sur le nouveau depot. Chaque membre
fait des commits sur les fichiers qui lui appartiennent.

---

## Repartition des fonctionnalites

| Script | Membre | Branches | Fichiers touches |
|--------|--------|----------|-----------------|
| `script_mli.ps1` | MLI | `main`, `develop`, `feature/admin-setup-database`, `feature/admin-classroom-crud`, `feature/auth-login` | Migrations users/classrooms/subjects, UserSeeder, Models Classroom+Subject+User, AuthController, AuthContext, LoginPage, authService, routes/api.php |
| `script_membre2.ps1` | Membre 2 | `feature/admin-teacher-management`, `feature/admin-student-crud`, `feature/timetable-management` | Migrations teachers/students/timetables, Models Teacher+Student+TimeTable, Controllers Teacher+Student+TimeTable, Pages TeacherManagement+StudentManagement+TimeTableManagement |
| `script_membre3.ps1` | Membre 3 | `feature/teacher-absences-tracking`, `feature/teacher-grades-management`, `feature/admin-pdf-bulletins`, `feature/admin-dashboard` | Migrations absences/sequences, Models Absence+Sequence, Controllers Absence+Sequence+Bulletin+Dashboard, Pages AbsenceManagement+SequenceManagement+BulletinManagement+AdminDashboard, adminService |

---

## Etapes d'execution

### ETAPE 1 — MLI : depuis le dossier du projet existant

```powershell
# Aller dans le dossier du projet
cd "c:\Users\FLEXY TECH\Desktop\backend\edusmart-cm"

# Autoriser l'execution des scripts (une seule fois)
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser

# Executer
.\scripts\script_mli.ps1 -AuthorName "Ton Nom" -AuthorEmail "ton@email.com"

# Ou avec le lien explicite (meme resultat) :
.\scripts\script_mli.ps1 -NouveauDepot "https://github.com/AngelaNgassam/edusmart-cm.git" `
                          -AuthorName "Ton Nom" `
                          -AuthorEmail "ton@email.com"
```

Ce script :
- Repointe le remote vers le nouveau depot
- Pousse tout le projet sur `main`
- Cree `develop`
- Cree ses 3 branches feature avec commits sur ses fichiers

---

### ETAPE 2 — Membre 2 : depuis un DOSSIER VIDE

```powershell
# Creer un dossier vide
mkdir C:\projets\edusmart-m2
cd C:\projets\edusmart-m2

# Copier le script dans ce dossier, puis executer
.\script_membre2.ps1 -AuthorName "Nom Membre 2" -AuthorEmail "m2@email.com"

# Ou avec le lien explicite :
.\script_membre2.ps1 -NouveauDepot "https://github.com/AngelaNgassam/edusmart-cm.git" `
                     -AuthorName "Nom Membre 2" `
                     -AuthorEmail "m2@email.com"
```

---

### ETAPE 3 — Membre 3 : depuis un DOSSIER VIDE

```powershell
mkdir C:\projets\edusmart-m3
cd C:\projets\edusmart-m3

.\script_membre3.ps1 -AuthorName "Nom Membre 3" -AuthorEmail "m3@email.com"

# Ou avec le lien explicite :
.\script_membre3.ps1 -NouveauDepot "https://github.com/AngelaNgassam/edusmart-cm.git" `
                     -AuthorName "Nom Membre 3" `
                     -AuthorEmail "m3@email.com"
```

> Membres 2 et 3 peuvent executer leurs scripts en meme temps.

---

## Resultat final

```
Branches sur le nouveau depot :
  main                              (MLI - tout le projet)
  develop                           (MLI)
  feature/admin-setup-database      (MLI    - 3 commits)
  feature/admin-classroom-crud      (MLI    - 3 commits)
  feature/auth-login                (MLI    - 3 commits)
  feature/admin-teacher-management  (Membre2 - 4 commits)
  feature/admin-student-crud        (Membre2 - 4 commits)
  feature/timetable-management      (Membre2 - 4 commits)
  feature/teacher-absences-tracking (Membre3 - 4 commits)
  feature/teacher-grades-management (Membre3 - 4 commits)
  feature/admin-pdf-bulletins       (Membre3 - 4 commits)
  feature/admin-dashboard           (Membre3 - 3 commits)
```

---

## Problemes frequents

**Erreur d'authentification GitHub**
Utiliser un token personnel dans l'URL :
```
https://TON_TOKEN@github.com/groupe/edusmart-cm.git
```

**"Updates were rejected" sur main**
Le depot n'est pas completement vide. Supprimer et recreer le depot,
ou utiliser `git push --force` (seulement si le depot est vraiment vide).

**Script bloque : "cannot be loaded"**
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

**"nothing to commit" sur un fichier**
Le fichier a deja le commentaire de marquage. C'est normal,
le script detecte et ignore les fichiers deja traites.
