#!/bin/bash

# ============================================================
# Script Git Flow Intelligent - EDUSMART-CM Backend
# Analyse les fichiers modifiés et pousse sur la bonne branche
# Usage: bash deploy-git-flow.sh <URL_GITHUB>
# ============================================================

REMOTE_URL=$1
PROJECT_DIR="/c/Users/FLEXY TECH/Desktop/backend/edusmart-cm"

# ── Helpers ──────────────────────────────────────────────────
ok()    { echo "  ✅ $1"; }
info()  { echo "  ➜  $1"; }
err()   { echo "  ❌ $1"; exit 1; }
sep()   { echo ""; echo "════════════════════════════════════════════════════"; echo "  $1"; echo "════════════════════════════════════════════════════"; echo ""; }

# ── Vérifications ───────────────────────────────────────────
[ -z "$REMOTE_URL" ] && err "URL GitHub manquante."
cd "$PROJECT_DIR" || err "Répertoire introuvable: $PROJECT_DIR"

if git remote get-url origin &>/dev/null; then
  git remote set-url origin "$REMOTE_URL"
else
  git remote add origin "$REMOTE_URL"
fi

# ── Initialisation Main & Develop ───────────────────────────
sep "VÉRIFICATION DES BRANCHES PRINCIPALES"
if ! git log --oneline -1 &>/dev/null; then
  git add -A
  git commit -m "chore: Initial commit — EDUSMART-CM Backend"
fi
git branch -M main &>/dev/null
git push -u origin main &>/dev/null

if ! git show-ref --quiet refs/heads/develop; then
  git checkout -b develop main &>/dev/null
  git push -u origin develop &>/dev/null
fi
git checkout develop &>/dev/null

# ── Fonction d'analyse et push automatique ──────────────────
auto_push_feature() {
  local BRANCH=$1
  local MSG=$2
  local PATTERN=$3

  # Étape 1 : Vérifier s'il y a des modifications locales pour ce module
  if ! git status --porcelain | grep -E "$PATTERN" &>/dev/null; then
    return 0
  fi

  sep "CHANGEMENTS DÉTECTÉS -> $BRANCH"
  info "Des modifications correspondent à ce module. Préparation du push..."

  # Étape 2 : Gestion de la branche
  if git show-ref --quiet refs/heads/"$BRANCH"; then
    git checkout "$BRANCH" &>/dev/null
  else
    git checkout -b "$BRANCH" develop &>/dev/null
    ok "Branche $BRANCH créée."
  fi

  # Étape 3 : Ajouter les fichiers spécifiques à ce module
  git status --porcelain | grep -E "$PATTERN" | awk '{print $2}' | xargs -I {} git add {}

  # Étape 4 : Commit et Push
  if ! git diff --cached --quiet; then
    git commit -m "$MSG"
    ok "Commit enregistré."
    git push -u origin "$BRANCH" && ok "$BRANCH poussée sur GitHub !"
  else
    info "Pas de nouveaux changements à commiter."
  fi

  # Retourner sur develop
  git checkout develop &>/dev/null
}

# ════════════════════════════════════════════════════════════
# ── ANALYSE ET DEPLOYEMENT AUTOMATIQUE PAS À PAS ────────────
# ════════════════════════════════════════════════════════════

# 1. Base de données
auto_push_feature "feature/admin-setup-database" \
"feat(admin): Setup database — migrations, models, seeders" \
"database/migrations/|database/seeders/|app/Models/"

# 2. Classroom CRUD
auto_push_feature "feature/admin-classroom-crud" \
"feat(admin): Classroom CRUD endpoints" \
"ClassroomController|ClassroomRequest|ClassroomResource"

# 3. Student CRUD
auto_push_feature "feature/admin-student-crud" \
"feat(admin): Student CRUD + transfer + expel" \
"StudentController|StudentRequest|StudentResource"

# 4. Teacher Management
auto_push_feature "feature/admin-teacher-management" \
"feat(admin): Teacher management + classroom assignments" \
"TeacherController|TeacherRequest|TeacherResource"

# 5. Bulletins PDF
auto_push_feature "feature/admin-pdf-bulletins" \
"feat(admin): Bulletin generation (JSON + PDF)" \
"BulletinController|BulletinService"

# 6. Saisie des Notes (Enseignant)
auto_push_feature "feature/teacher-grades-management" \
"feat(teacher): Grade management — bulk entry + validation" \
"GradeController|GradeRequest|GradeResource"

# 7. Suivi des Absences (Enseignant)
auto_push_feature "feature/teacher-absences-tracking" \
"feat(teacher): Absence tracking with justification" \
"AbsenceController|AbsenceRequest|AbsenceResource"

# 8. Cahier de texte & Appréciations
auto_push_feature "feature/teacher-remarks-progress" \
"feat(teacher): Remarks + Course progressions (cahier de texte)" \
"RemarkController|CourseProgressionController|StoreRemarkRequest|StoreCourseProgressionRequest|UpdateRemarkRequest|UpdateCourseProgressionRequest|RemarkResource|CourseProgressionResource"

# ── Retour au propre ─────────────────────────────────────────
git checkout develop &>/dev/null
sep "FIN DE L'ANALYSE"
info "Branche active : $(git branch --show-current)"
ok "Seules les branches avec des modifications réelles ont été mises à jour !"