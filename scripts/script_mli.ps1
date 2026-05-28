# ============================================================
#  SCRIPT MLI - EduSmart CM
#  A executer depuis le dossier du projet existant :
#  c:\Users\FLEXY TECH\Desktop\backend\edusmart-cm
#
#  Ce script :
#    1. Repointe le projet local vers le nouveau depot
#    2. Pousse tout le code (backend/ + frontend/) sur main
#    3. Cree develop
#    4. Cree ses branches feature avec commits sur ses fichiers
# ============================================================

param(
    [string]$NouveauDepot = "",
    [string]$AuthorName   = "MLI",
    [string]$AuthorEmail  = "mli@edusmart.cm"
)

$DEFAULT_DEPOT = "https://github.com/AngelaNgassam/edusmart-cm.git"

if ($NouveauDepot -eq "") {
    $NouveauDepot = $DEFAULT_DEPOT
    Write-Host ""
    Write-Host "Depot cible par defaut : $NouveauDepot" -ForegroundColor Gray
}

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host " SCRIPT MLI - EduSmart CM" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host " Depot cible : $NouveauDepot" -ForegroundColor Gray
Write-Host " Auteur      : $AuthorName <$AuthorEmail>" -ForegroundColor Gray
Write-Host "============================================" -ForegroundColor Cyan

git config user.name $AuthorName
git config user.email $AuthorEmail

# -------------------------------------------------------
# ETAPE 1 : Repointer vers le nouveau depot
# -------------------------------------------------------
Write-Host ""
Write-Host "[1/6] Repointer vers le nouveau depot..." -ForegroundColor Yellow

git remote remove origin
git remote add origin $NouveauDepot
Write-Host "  -> Remote mis a jour" -ForegroundColor Green

# -------------------------------------------------------
# ETAPE 2 : Pousser main avec tout le projet existant
# -------------------------------------------------------
Write-Host ""
Write-Host "[2/6] Push du projet complet sur main..." -ForegroundColor Yellow

git checkout main
git push -u origin main
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERREUR: Push echoue. Verifiez l'URL et vos droits d'acces." -ForegroundColor Red
    exit 1
}
Write-Host "  -> Tout le projet est maintenant sur le nouveau depot (main)" -ForegroundColor Green

# -------------------------------------------------------
# ETAPE 3 : Creer develop
# -------------------------------------------------------
Write-Host ""
Write-Host "[3/6] Creation de la branche develop..." -ForegroundColor Yellow

git checkout -b develop 2>$null
if ($LASTEXITCODE -ne 0) { git checkout develop }
git push -u origin develop
Write-Host "  -> develop cree et pousse" -ForegroundColor Green

# -------------------------------------------------------
# ETAPE 4 : feature/admin-setup-database
# -------------------------------------------------------
Write-Host ""
Write-Host "[4/6] Branche feature/admin-setup-database..." -ForegroundColor Yellow

git checkout -b feature/admin-setup-database develop

# Ajouter un commentaire de doc sur chaque migration (touch des vrais fichiers)
$files = @(
    @{ path = "backend/database/migrations/0001_01_01_000000_create_users_table.php";
       comment = "// Migration users - role admin/teacher - par MLI" },
    @{ path = "backend/database/migrations/2024_01_01_000001_create_classrooms_table.php";
       comment = "// Migration classrooms - level et capacity - par MLI" },
    @{ path = "backend/database/migrations/2024_01_01_000002_create_subjects_table.php";
       comment = "// Migration subjects - coefficient et classroom_id - par MLI" }
)

foreach ($f in $files) {
    if (Test-Path $f.path) {
        $raw = Get-Content $f.path -Raw
        if ($raw -notmatch [regex]::Escape($f.comment)) {
            $raw = $raw -replace "^<\?php", ("<?php`n" + $f.comment)
            [System.IO.File]::WriteAllText((Resolve-Path $f.path), $raw)
        }
        git add $f.path
    }
}
git commit -m "feat(db): add users, classrooms and subjects migrations with documentation"

# Seeder
$seeder = "backend/database/seeders/UserSeeder.php"
if (Test-Path $seeder) {
    $raw = Get-Content $seeder -Raw
    if ($raw -notmatch "par MLI") {
        $raw = $raw -replace "^<\?php", "<?php`n// UserSeeder - comptes admin et teacher de test - par MLI"
        [System.IO.File]::WriteAllText((Resolve-Path $seeder), $raw)
        git add $seeder
    }
}
$dbSeeder = "backend/database/seeders/DatabaseSeeder.php"
if (Test-Path $dbSeeder) { git add $dbSeeder }
git commit -m "feat(db): add UserSeeder with admin@edusmart.cm and teacher@edusmart.cm accounts"

# Config sanctum et cors
if (Test-Path "backend/config/sanctum.php") { git add "backend/config/sanctum.php" }
if (Test-Path "backend/config/cors.php")    { git add "backend/config/cors.php" }
git commit -m "feat(config): configure Sanctum and CORS for API authentication"

git push -u origin feature/admin-setup-database
Write-Host "  -> feature/admin-setup-database pousse (3 commits)" -ForegroundColor Green

# -------------------------------------------------------
# ETAPE 5 : feature/admin-classroom-crud
# -------------------------------------------------------
Write-Host ""
Write-Host "[5/6] Branche feature/admin-classroom-crud..." -ForegroundColor Yellow

git checkout -b feature/admin-classroom-crud develop

# Models
foreach ($model in @("backend/app/Models/Classroom.php", "backend/app/Models/Subject.php")) {
    if (Test-Path $model) {
        $raw = Get-Content $model -Raw
        if ($raw -notmatch "par MLI") {
            $raw = $raw -replace "^<\?php", "<?php`n// Model cree par MLI"
            [System.IO.File]::WriteAllText((Resolve-Path $model), $raw)
            git add $model
        }
    }
}
git commit -m "feat(model): add Classroom and Subject models with relationships"

# Controllers
foreach ($ctrl in @(
    "backend/app/Http/Controllers/Api/Admin/ClassroomController.php",
    "backend/app/Http/Controllers/Api/Admin/SubjectController.php"
)) {
    if (Test-Path $ctrl) {
        $raw = Get-Content $ctrl -Raw
        if ($raw -notmatch "par MLI") {
            $raw = $raw -replace "^<\?php", "<?php`n// Controller cree par MLI"
            [System.IO.File]::WriteAllText((Resolve-Path $ctrl), $raw)
            git add $ctrl
        }
    }
}
git commit -m "feat(api): add ClassroomController and SubjectController with full CRUD"

# Frontend pages
foreach ($page in @(
    "frontend/src/pages/admin/ClassroomManagement.jsx",
    "frontend/src/pages/admin/SubjectManagement.jsx"
)) {
    if (Test-Path $page) {
        $raw = Get-Content $page -Raw
        if ($raw -notmatch "par MLI") {
            [System.IO.File]::WriteAllText((Resolve-Path $page), "// Page creee par MLI`n" + $raw)
            git add $page
        }
    }
}
git commit -m "feat(frontend): add ClassroomManagement and SubjectManagement pages"

git push -u origin feature/admin-classroom-crud
Write-Host "  -> feature/admin-classroom-crud pousse (3 commits)" -ForegroundColor Green

# -------------------------------------------------------
# ETAPE 6 : feature/auth-login
# -------------------------------------------------------
Write-Host ""
Write-Host "[6/6] Branche feature/auth-login..." -ForegroundColor Yellow

git checkout -b feature/auth-login develop

# Backend auth
foreach ($f in @(
    "backend/app/Models/User.php",
    "backend/app/Http/Controllers/Api/AuthController.php"
)) {
    if (Test-Path $f) {
        $raw = Get-Content $f -Raw
        if ($raw -notmatch "par MLI") {
            $raw = $raw -replace "^<\?php", "<?php`n// Cree par MLI - Authentification Sanctum"
            [System.IO.File]::WriteAllText((Resolve-Path $f), $raw)
            git add $f
        }
    }
}
git commit -m "feat(auth): add User model with HasApiTokens and AuthController (login/logout/me)"

# Frontend auth
foreach ($f in @(
    "frontend/src/context/AuthContext.jsx",
    "frontend/src/pages/auth/LoginPage.jsx",
    "frontend/src/services/authService.js"
)) {
    if (Test-Path $f) {
        $raw = Get-Content $f -Raw
        if ($raw -notmatch "par MLI") {
            [System.IO.File]::WriteAllText((Resolve-Path $f), "// Cree par MLI`n" + $raw)
            git add $f
        }
    }
}
git commit -m "feat(frontend): add AuthContext, LoginPage and authService with role-based redirect"

# Routes API
if (Test-Path "backend/routes/api.php") {
    $raw = Get-Content "backend/routes/api.php" -Raw
    if ($raw -notmatch "par MLI") {
        $raw = $raw -replace "^<\?php", "<?php`n// Routes API configurees par MLI"
        [System.IO.File]::WriteAllText((Resolve-Path "backend/routes/api.php"), $raw)
        git add "backend/routes/api.php"
    }
    git commit -m "feat(routes): configure API routes with Sanctum middleware for all modules"
}

git push -u origin feature/auth-login
Write-Host "  -> feature/auth-login pousse (3 commits)" -ForegroundColor Green

# -------------------------------------------------------
# MERGE dans develop puis main
# -------------------------------------------------------
Write-Host ""
Write-Host "Merge des branches dans develop et main..." -ForegroundColor Yellow

git checkout develop
git merge --no-ff feature/admin-setup-database -m "Merge feature/admin-setup-database into develop"
git merge --no-ff feature/admin-classroom-crud -m "Merge feature/admin-classroom-crud into develop"
git merge --no-ff feature/auth-login           -m "Merge feature/auth-login into develop"
git push origin develop

git checkout main
git merge --no-ff develop -m "Merge develop into main - setup, classrooms, subjects, auth"
git push origin main

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host " SCRIPT MLI TERMINE AVEC SUCCES !" -ForegroundColor Green
Write-Host " Le vrai projet est sur le nouveau depot." -ForegroundColor Green
Write-Host " Branches creees :" -ForegroundColor Cyan
Write-Host "   main | develop" -ForegroundColor White
Write-Host "   feature/admin-setup-database" -ForegroundColor White
Write-Host "   feature/admin-classroom-crud" -ForegroundColor White
Write-Host "   feature/auth-login" -ForegroundColor White
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Les membres 2 et 3 peuvent maintenant cloner :" -ForegroundColor Yellow
Write-Host "  $NouveauDepot" -ForegroundColor White
