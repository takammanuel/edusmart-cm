<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin - <?php echo e($bulletin['student']['matricule']); ?></title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #1e40af; padding-bottom: 12px; }
        .header h1 { margin: 0; font-size: 18px; color: #1e40af; }
        .header p { margin: 4px 0; }
        .info { margin-bottom: 20px; }
        .info table { width: 100%; }
        .info td { padding: 4px 0; }
        table.grades { width: 100%; border-collapse: collapse; margin-top: 16px; }
        table.grades th, table.grades td { border: 1px solid #cbd5e1; padding: 8px; text-align: left; }
        table.grades th { background: #eff6ff; }
        .summary { margin-top: 24px; padding: 16px; background: #f8fafc; border: 1px solid #e2e8f0; }
        .summary strong { color: #1e40af; }
        .footer { margin-top: 32px; text-align: center; font-size: 10px; color: #64748b; }
    </style>
</head>
<body>
    <div class="header">
        <h1>EDUSMART-CM</h1>
        <p>République du Cameroun — Ministère des Enseignements Secondaires</p>
        <p><strong>Bulletin scolaire — <?php echo e($bulletin['sequence']['name']); ?></strong></p>
        <p>Année scolaire : <?php echo e($bulletin['sequence']['school_year']); ?></p>
    </div>

    <div class="info">
        <table>
            <tr>
                <td><strong>Élève :</strong> <?php echo e($bulletin['student']['last_name']); ?> <?php echo e($bulletin['student']['first_name']); ?></td>
                <td><strong>Matricule :</strong> <?php echo e($bulletin['student']['matricule']); ?></td>
            </tr>
            <tr>
                <td><strong>Classe :</strong> <?php echo e($bulletin['classroom']['name']); ?> (<?php echo e($bulletin['classroom']['level']); ?>)</td>
                <td><strong>Spécialité :</strong> <?php echo e($bulletin['classroom']['specialty']); ?></td>
            </tr>
            <tr>
                <td><strong>Date de naissance :</strong> <?php echo e($bulletin['student']['birth_date']); ?></td>
                <td><strong>Absences (heures) :</strong> <?php echo e($bulletin['summary']['total_absence_hours']); ?></td>
            </tr>
        </table>
    </div>

    <table class="grades">
        <thead>
            <tr>
                <th>Matière</th>
                <th>Coef.</th>
                <th>Note /20</th>
                <th>Enseignant</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $bulletin['subjects']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($line['subject_name']); ?></td>
                <td><?php echo e($line['coefficient']); ?></td>
                <td><?php echo e(number_format($line['grade'], 2)); ?></td>
                <td><?php echo e($line['teacher'] ?? '—'); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <div class="summary">
        <p><strong>Moyenne générale :</strong> <?php echo e(number_format($bulletin['summary']['general_average'], 2)); ?> / 20</p>
        <p><strong>Mention :</strong> <?php echo e($bulletin['summary']['mention']); ?></p>
        <p><strong>Total coefficients :</strong> <?php echo e($bulletin['summary']['total_coefficients']); ?></p>
    </div>

    <div class="footer">
        Document généré le <?php echo e(\Carbon\Carbon::parse($bulletin['generated_at'])->format('d/m/Y à H:i')); ?> — EDUSMART-CM
    </div>
</body>
</html>
<?php /**PATH C:\Users\FLEXY TECH\Desktop\backend\edusmart-cm\resources\views/bulletins/student.blade.php ENDPATH**/ ?>