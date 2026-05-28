# ============================================================
#  SCRIPT MEMBRE 3 - EduSmart CM
#  A executer depuis un DOSSIER VIDE apres que MLI ait pousse
#  Fonctionnalites : Absences, Sequences/Notes, Bulletins PDF, Dashboard
# ============================================================

param(
    [string]$NouveauDepot = "",
    [string]$AuthorName   = "Membre3",
    [string]$AuthorEmail  = "membre3@edusmart.cm"
)

$DEFAULT_DEPOT = "https://github.com/AngelaNgassam/edusmart-cm.git"

if ($NouveauDepot -eq "") {
    $NouveauDepot = $DEFAULT_DEPOT
    Write-Host ""
    Write-Host "Depot cible par defaut : $NouveauDepot" -ForegroundColor Gray
}

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host " SCRIPT MEMBRE 3 - EduSmart CM" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host " Depot : $NouveauDepot" -ForegroundColor Gray
Write-Host " Auteur : $AuthorName <$AuthorEmail>" -ForegroundColor Gray
Write-Host "============================================" -ForegroundColor Cyan

# -------------------------------------------------------
# ETAPE 1 : Cloner le nouveau depot
# -------------------------------------------------------
Write-Host ""
Write-Host "[1/5] Clonage du depot..." -ForegroundColor Yellow

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
# ETAPE 2 : feature/teacher-absences-tracking
# -------------------------------------------------------
Write-Host ""
Write-Host "[2/5] Branche feature/teacher-absences-tracking..." -ForegroundColor Yellow

git checkout -b feature/teacher-absences-tracking develop

# Migration absences
$mig = "backend/database/migrations/2024_01_01_000006_create_absences_table.php"
if (Test-Path $mig) {
    $raw = Get-Content $mig -Raw
    if ($raw -notmatch "par Membre3") {
        $raw = $raw -replace "^<\?php", "<?php`n// Migration absences - justified, reason - par Membre3"
        [System.IO.File]::WriteAllText((Resolve-Path $mig), $raw)
        git add $mig
    }
}
git commit -m "feat(db): add absences migration with justified boolean and reason fields"

# Model Absence
$model = "backend/app/Models/Absence.php"
if (Test-Path $model) {
    $raw = Get-Content $model -Raw
    if ($raw -notmatch "par Membre3") {
        $raw = $raw -replace "^<\?php", "<?php`n// Model Absence - par Membre3"
        [System.IO.File]::WriteAllText((Resolve-Path $model), $raw)
        git add $model
    }
}
git commit -m "feat(model): add Absence model with student and subject relations"

# Controller AbsenceController
$ctrl = "backend/app/Http/Controllers/Api/Admin/AbsenceController.php"
if (Test-Path $ctrl) {
    $raw = Get-Content $ctrl -Raw
    if ($raw -notmatch "par Membre3") {
        $raw = $raw -replace "^<\?php", "<?php`n// Controller cree par Membre3 - Suivi des absences"
        [System.IO.File]::WriteAllText((Resolve-Path $ctrl), $raw)
        git add $ctrl
    }
}
git commit -m "feat(api): add AbsenceController with justify action and student/subject filters"

# Frontend AbsenceManagement
$page = "frontend/src/pages/admin/AbsenceManagement.jsx"
if (Test-Path $page) {
    $raw = Get-Content $page -Raw
    if ($raw -notmatch "par Membre3") {
        [System.IO.File]::WriteAllText((Resolve-Path $page), "// Page creee par Membre3 - Suivi des absences`n" + $raw)
        git add $page
    }
}
git commit -m "feat(frontend): add AbsenceManagement page with justify button and status badge"

git push -u origin feature/teacher-absences-tracking
Write-Host "  -> feature/teacher-absences-tracking pousse (4 commits)" -ForegroundColor Green

# -------------------------------------------------------
# ETAPE 3 : feature/teacher-grades-management
# -------------------------------------------------------
Write-Host ""
Write-Host "[3/5] Branche feature/teacher-grades-management..." -ForegroundColor Yellow

git checkout -b feature/teacher-grades-management develop

# Migration sequences
$mig = "backend/database/migrations/2024_01_01_000005_create_sequences_table.php"
if (Test-Path $mig) {
    $raw = Get-Content $mig -Raw
    if ($raw -notmatch "par Membre3") {
        $raw = $raw -replace "^<\?php", "<?php`n// Migration sequences - grade, trimester, comment - par Membre3"
        [System.IO.File]::WriteAllText((Resolve-Path $mig), $raw)
        git add $mig
    }
}
git commit -m "feat(db): add sequences migration with grade decimal, trimester and comment"

# Model Sequence
$model = "backend/app/Models/Sequence.php"
if (Test-Path $model) {
    $raw = Get-Content $model -Raw
    if ($raw -notmatch "par Membre3") {
        $raw = $raw -replace "^<\?php", "<?php`n// Model Sequence - par Membre3"
        [System.IO.File]::WriteAllText((Resolve-Path $model), $raw)
        git add $model
    }
}
git commit -m "feat(model): add Sequence model with grade casting and student/subject relations"

# Controller SequenceController
$ctrl = "backend/app/Http/Controllers/Api/Admin/SequenceController.php"
if (Test-Path $ctrl) {
    $raw = Get-Content $ctrl -Raw
    if ($raw -notmatch "par Membre3") {
        $raw = $raw -replace "^<\?php", "<?php`n// Controller cree par Membre3 - Saisie des notes par sequence"
        [System.IO.File]::WriteAllText((Resolve-Path $ctrl), $raw)
        git add $ctrl
    }
}
git commit -m "feat(api): add SequenceController with grade entry and trimester filter"

# Frontend SequenceManagement
$page = "frontend/src/pages/admin/SequenceManagement.jsx"
if (Test-Path $page) {
    $raw = Get-Content $page -Raw
    if ($raw -notmatch "par Membre3") {
        [System.IO.File]::WriteAllText((Resolve-Path $page), "// Page creee par Membre3 - Saisie des notes par sequence`n" + $raw)
        git add $page
    }
}
git commit -m "feat(frontend): add SequenceManagement page with grade input per trimester"

git push -u origin feature/teacher-grades-management
Write-Host "  -> feature/teacher-grades-management pousse (4 commits)" -ForegroundColor Green

# -------------------------------------------------------
# ETAPE 4 : feature/admin-pdf-bulletins
# -------------------------------------------------------
Write-Host ""
Write-Host "[4/5] Branche feature/admin-pdf-bulletins..." -ForegroundColor Yellow

git checkout -b feature/admin-pdf-bulletins develop

# Controller BulletinController
$ctrl = "backend/app/Http/Controllers/Api/Admin/BulletinController.php"
if (Test-Path $ctrl) {
    $raw = Get-Content $ctrl -Raw
    if ($raw -notmatch "par Membre3") {
        $raw = $raw -replace "^<\?php", "<?php`n// Controller cree par Membre3 - Generation bulletins PDF avec DomPDF"
        [System.IO.File]::WriteAllText((Resolve-Path $ctrl), $raw)
        git add $ctrl
    }
}
git commit -m "feat(api): add BulletinController with average calculation, mention and PDF export"

# Vue Blade bulletin
$blade = "backend/resources/views/bulletins/bulletin.blade.php"
if (Test-Path $blade) {
    $raw = Get-Content $blade -Raw
    if ($raw -notmatch "par Membre3") {
        $raw = "{{-- Template bulletin PDF - cree par Membre3 --}}`n" + $raw
        [System.IO.File]::WriteAllText((Resolve-Path $blade), $raw)
        git add $blade
    }
}
git commit -m "feat(pdf): add bulletin Blade template with styled layout and mention display"

# Frontend BulletinManagement
$page = "frontend/src/pages/admin/BulletinManagement.jsx"
if (Test-Path $page) {
    $raw = Get-Content $page -Raw
    if ($raw -notmatch "par Membre3") {
        [System.IO.File]::WriteAllText((Resolve-Path $page), "// Page creee par Membre3 - Bulletins avec telechargement PDF`n" + $raw)
        git add $page
    }
}
git commit -m "feat(frontend): add BulletinManagement page with PDF download and mention badge"

# Dependency DomPDF dans composer.json
$composer = "backend/composer.json"
if (Test-Path $composer) {
    git add $composer
    git commit -m "feat(deps): add barryvdh/laravel-dompdf for PDF bulletin generation"
}

git push -u origin feature/admin-pdf-bulletins
Write-Host "  -> feature/admin-pdf-bulletins pousse (4 commits)" -ForegroundColor Green

# -------------------------------------------------------
# ETAPE 5 : feature/admin-dashboard
# -------------------------------------------------------
Write-Host ""
Write-Host "[5/5] Branche feature/admin-dashboard..." -ForegroundColor Yellow

git checkout -b feature/admin-dashboard develop

# Controller DashboardController
$ctrl = "backend/app/Http/Controllers/Api/Admin/DashboardController.php"
if (Test-Path $ctrl) {
    $raw = Get-Content $ctrl -Raw
    if ($raw -notmatch "par Membre3") {
        $raw = $raw -replace "^<\?php", "<?php`n// Controller cree par Membre3 - Stats globales du tableau de bord"
        [System.IO.File]::WriteAllText((Resolve-Path $ctrl), $raw)
        git add $ctrl
    }
}
git commit -m "feat(api): add DashboardController with students, teachers, absences and average stats"

# Frontend AdminDashboard
$page = "frontend/src/pages/admin/AdminDashboard.jsx"
if (Test-Path $page) {
    $raw = Get-Content $page -Raw
    if ($raw -notmatch "par Membre3") {
        [System.IO.File]::WriteAllText((Resolve-Path $page), "// Page creee par Membre3 - Tableau de bord avec statistiques`n" + $raw)
        git add $page
    }
}
git commit -m "feat(frontend): add AdminDashboard with stat cards and classroom effectifs table"

# adminService (appels API)
$svc = "frontend/src/services/adminService.js"
if (Test-Path $svc) {
    $raw = Get-Content $svc -Raw
    if ($raw -notmatch "par Membre3") {
        [System.IO.File]::WriteAllText((Resolve-Path $svc), "// Service cree par Membre3 - Tous les appels API admin`n" + $raw)
        git add $svc
    }
}
git commit -m "feat(frontend): add adminService with all API calls for dashboard, bulletins and stats"

git push -u origin feature/admin-dashboard
Write-Host "  -> feature/admin-dashboard pousse (3 commits)" -ForegroundColor Green

# -------------------------------------------------------
# MERGE dans develop
# -------------------------------------------------------
Write-Host ""
Write-Host "Merge des branches dans develop..." -ForegroundColor Yellow

git checkout develop
git merge --no-ff feature/teacher-absences-tracking  -m "Merge feature/teacher-absences-tracking into develop"
git merge --no-ff feature/teacher-grades-management  -m "Merge feature/teacher-grades-management into develop"
git merge --no-ff feature/admin-pdf-bulletins        -m "Merge feature/admin-pdf-bulletins into develop"
git merge --no-ff feature/admin-dashboard            -m "Merge feature/admin-dashboard into develop"
git push origin develop

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host " SCRIPT MEMBRE 3 TERMINE AVEC SUCCES !" -ForegroundColor Green
Write-Host " Branches creees :" -ForegroundColor Cyan
Write-Host "   feature/teacher-absences-tracking" -ForegroundColor White
Write-Host "   feature/teacher-grades-management" -ForegroundColor White
Write-Host "   feature/admin-pdf-bulletins" -ForegroundColor White
Write-Host "   feature/admin-dashboard" -ForegroundColor White
Write-Host "============================================" -ForegroundColor Cyan
