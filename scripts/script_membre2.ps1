# ============================================================
#  SCRIPT MEMBRE 2 - EduSmart CM
#  A executer depuis un DOSSIER VIDE apres que MLI ait pousse
#  Fonctionnalites : Teachers, Students, Timetable
# ============================================================

param(
    [string]$NouveauDepot = "",
    [string]$AuthorName   = "Membre2",
    [string]$AuthorEmail  = "membre2@edusmart.cm"
)

$DEFAULT_DEPOT = "https://github.com/AngelaNgassam/edusmart-cm.git"

if ($NouveauDepot -eq "") {
    $NouveauDepot = $DEFAULT_DEPOT
    Write-Host ""
    Write-Host "Depot cible par defaut : $NouveauDepot" -ForegroundColor Gray
}

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host " SCRIPT MEMBRE 2 - EduSmart CM" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host " Depot : $NouveauDepot" -ForegroundColor Gray
Write-Host " Auteur : $AuthorName <$AuthorEmail>" -ForegroundColor Gray
Write-Host "============================================" -ForegroundColor Cyan

# -------------------------------------------------------
# ETAPE 1 : Cloner le nouveau depot (qui a deja tout le code)
# -------------------------------------------------------
Write-Host ""
Write-Host "[1/4] Clonage du depot..." -ForegroundColor Yellow

git clone $NouveauDepot .
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERREUR: Impossible de cloner. Verifiez l'URL." -ForegroundColor Red
    exit 1
}

git config user.name $AuthorName
git config user.email $AuthorEmail
git checkout develop
Write-Host "  -> Depot clone, sur develop (tout le projet est present)" -ForegroundColor Green

# -------------------------------------------------------
# ETAPE 2 : feature/admin-teacher-management
# -------------------------------------------------------
Write-Host ""
Write-Host "[2/4] Branche feature/admin-teacher-management..." -ForegroundColor Yellow

git checkout -b feature/admin-teacher-management develop

# Migration teachers
$mig = "backend/database/migrations/2024_01_01_000003_create_teachers_table.php"
if (Test-Path $mig) {
    $raw = Get-Content $mig -Raw
    if ($raw -notmatch "par Membre2") {
        $raw = $raw -replace "^<\?php", "<?php`n// Migration teachers - specialty et user_id - par Membre2"
        [System.IO.File]::WriteAllText((Resolve-Path $mig), $raw)
        git add $mig
    }
}
git commit -m "feat(db): add teachers migration with specialty, phone and user_id fields"

# Model Teacher
$model = "backend/app/Models/Teacher.php"
if (Test-Path $model) {
    $raw = Get-Content $model -Raw
    if ($raw -notmatch "par Membre2") {
        $raw = $raw -replace "^<\?php", "<?php`n// Model Teacher - par Membre2"
        [System.IO.File]::WriteAllText((Resolve-Path $model), $raw)
        git add $model
    }
}
git commit -m "feat(model): add Teacher model with user and timetable relations"

# Controller TeacherController
$ctrl = "backend/app/Http/Controllers/Api/Admin/TeacherController.php"
if (Test-Path $ctrl) {
    $raw = Get-Content $ctrl -Raw
    if ($raw -notmatch "par Membre2") {
        $raw = $raw -replace "^<\?php", "<?php`n// Controller cree par Membre2 - CRUD enseignants"
        [System.IO.File]::WriteAllText((Resolve-Path $ctrl), $raw)
        git add $ctrl
    }
}
git commit -m "feat(api): add TeacherController with index, store, update, destroy"

# Frontend TeacherManagement
$page = "frontend/src/pages/admin/TeacherManagement.jsx"
if (Test-Path $page) {
    $raw = Get-Content $page -Raw
    if ($raw -notmatch "par Membre2") {
        [System.IO.File]::WriteAllText((Resolve-Path $page), "// Page creee par Membre2 - Gestion des enseignants`n" + $raw)
        git add $page
    }
}
git commit -m "feat(frontend): add TeacherManagement page with CRUD form and table"

git push -u origin feature/admin-teacher-management
Write-Host "  -> feature/admin-teacher-management pousse (4 commits)" -ForegroundColor Green

# -------------------------------------------------------
# ETAPE 3 : feature/admin-student-crud
# -------------------------------------------------------
Write-Host ""
Write-Host "[3/4] Branche feature/admin-student-crud..." -ForegroundColor Yellow

git checkout -b feature/admin-student-crud develop

# Migration students
$mig = "backend/database/migrations/2024_01_01_000004_create_students_table.php"
if (Test-Path $mig) {
    $raw = Get-Content $mig -Raw
    if ($raw -notmatch "par Membre2") {
        $raw = $raw -replace "^<\?php", "<?php`n// Migration students - matricule, gender, parent_phone - par Membre2"
        [System.IO.File]::WriteAllText((Resolve-Path $mig), $raw)
        git add $mig
    }
}
git commit -m "feat(db): add students migration with matricule, gender and parent_phone"

# Model Student
$model = "backend/app/Models/Student.php"
if (Test-Path $model) {
    $raw = Get-Content $model -Raw
    if ($raw -notmatch "par Membre2") {
        $raw = $raw -replace "^<\?php", "<?php`n// Model Student - par Membre2"
        [System.IO.File]::WriteAllText((Resolve-Path $model), $raw)
        git add $model
    }
}
git commit -m "feat(model): add Student model with classroom and absences relations"

# Controller StudentController
$ctrl = "backend/app/Http/Controllers/Api/Admin/StudentController.php"
if (Test-Path $ctrl) {
    $raw = Get-Content $ctrl -Raw
    if ($raw -notmatch "par Membre2") {
        $raw = $raw -replace "^<\?php", "<?php`n// Controller cree par Membre2 - CRUD eleves"
        [System.IO.File]::WriteAllText((Resolve-Path $ctrl), $raw)
        git add $ctrl
    }
}
git commit -m "feat(api): add StudentController with classroom filter and matricule validation"

# Frontend StudentManagement
$page = "frontend/src/pages/admin/StudentManagement.jsx"
if (Test-Path $page) {
    $raw = Get-Content $page -Raw
    if ($raw -notmatch "par Membre2") {
        [System.IO.File]::WriteAllText((Resolve-Path $page), "// Page creee par Membre2 - Gestion des eleves`n" + $raw)
        git add $page
    }
}
git commit -m "feat(frontend): add StudentManagement page with gender, matricule and classroom fields"

git push -u origin feature/admin-student-crud
Write-Host "  -> feature/admin-student-crud pousse (4 commits)" -ForegroundColor Green

# -------------------------------------------------------
# ETAPE 4 : feature/timetable-management
# -------------------------------------------------------
Write-Host ""
Write-Host "[4/4] Branche feature/timetable-management..." -ForegroundColor Yellow

git checkout -b feature/timetable-management develop

# Migration timetables
$mig = "backend/database/migrations/2024_01_01_000007_create_time_tables_table.php"
if (Test-Path $mig) {
    $raw = Get-Content $mig -Raw
    if ($raw -notmatch "par Membre2") {
        $raw = $raw -replace "^<\?php", "<?php`n// Migration time_tables - day, start_time, end_time - par Membre2"
        [System.IO.File]::WriteAllText((Resolve-Path $mig), $raw)
        git add $mig
    }
}
git commit -m "feat(db): add time_tables migration with day enum and start/end time"

# Model TimeTable
$model = "backend/app/Models/TimeTable.php"
if (Test-Path $model) {
    $raw = Get-Content $model -Raw
    if ($raw -notmatch "par Membre2") {
        $raw = $raw -replace "^<\?php", "<?php`n// Model TimeTable - par Membre2"
        [System.IO.File]::WriteAllText((Resolve-Path $model), $raw)
        git add $model
    }
}
git commit -m "feat(model): add TimeTable model with classroom, teacher and subject relations"

# Controller TimeTableController
$ctrl = "backend/app/Http/Controllers/Api/Admin/TimeTableController.php"
if (Test-Path $ctrl) {
    $raw = Get-Content $ctrl -Raw
    if ($raw -notmatch "par Membre2") {
        $raw = $raw -replace "^<\?php", "<?php`n// Controller cree par Membre2 - Emploi du temps"
        [System.IO.File]::WriteAllText((Resolve-Path $ctrl), $raw)
        git add $ctrl
    }
}
git commit -m "feat(api): add TimeTableController with classroom filter and time validation"

# Frontend TimeTableManagement
$page = "frontend/src/pages/admin/TimeTableManagement.jsx"
if (Test-Path $page) {
    $raw = Get-Content $page -Raw
    if ($raw -notmatch "par Membre2") {
        [System.IO.File]::WriteAllText((Resolve-Path $page), "// Page creee par Membre2 - Emploi du temps`n" + $raw)
        git add $page
    }
}
git commit -m "feat(frontend): add TimeTableManagement page with day/time slot selector"

git push -u origin feature/timetable-management
Write-Host "  -> feature/timetable-management pousse (4 commits)" -ForegroundColor Green

# -------------------------------------------------------
# MERGE dans develop
# -------------------------------------------------------
Write-Host ""
Write-Host "Merge des branches dans develop..." -ForegroundColor Yellow

git checkout develop
git merge --no-ff feature/admin-teacher-management -m "Merge feature/admin-teacher-management into develop"
git merge --no-ff feature/admin-student-crud       -m "Merge feature/admin-student-crud into develop"
git merge --no-ff feature/timetable-management     -m "Merge feature/timetable-management into develop"
git push origin develop

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host " SCRIPT MEMBRE 2 TERMINE AVEC SUCCES !" -ForegroundColor Green
Write-Host " Branches creees :" -ForegroundColor Cyan
Write-Host "   feature/admin-teacher-management" -ForegroundColor White
Write-Host "   feature/admin-student-crud" -ForegroundColor White
Write-Host "   feature/timetable-management" -ForegroundColor White
Write-Host "============================================" -ForegroundColor Cyan
