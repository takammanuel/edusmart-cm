<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin - {{ $bulletin['student']['matricule'] }}</title>
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
        <p><strong>Bulletin scolaire — {{ $bulletin['sequence']['name'] }}</strong></p>
        <p>Année scolaire : {{ $bulletin['sequence']['school_year'] }}</p>
    </div>

    <div class="info">
        <table>
            <tr>
                <td><strong>Élève :</strong> {{ $bulletin['student']['last_name'] }} {{ $bulletin['student']['first_name'] }}</td>
                <td><strong>Matricule :</strong> {{ $bulletin['student']['matricule'] }}</td>
            </tr>
            <tr>
                <td><strong>Classe :</strong> {{ $bulletin['classroom']['name'] }} ({{ $bulletin['classroom']['level'] }})</td>
                <td><strong>Spécialité :</strong> {{ $bulletin['classroom']['specialty'] }}</td>
            </tr>
            <tr>
                <td><strong>Date de naissance :</strong> {{ $bulletin['student']['birth_date'] }}</td>
                <td><strong>Absences (heures) :</strong> {{ $bulletin['summary']['total_absence_hours'] }}</td>
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
            @foreach ($bulletin['subjects'] as $line)
            <tr>
                <td>{{ $line['subject_name'] }}</td>
                <td>{{ $line['coefficient'] }}</td>
                <td>{{ number_format($line['grade'], 2) }}</td>
                <td>{{ $line['teacher'] ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <p><strong>Moyenne générale :</strong> {{ number_format($bulletin['summary']['general_average'], 2) }} / 20</p>
        <p><strong>Mention :</strong> {{ $bulletin['summary']['mention'] }}</p>
        <p><strong>Total coefficients :</strong> {{ $bulletin['summary']['total_coefficients'] }}</p>
    </div>

    <div class="footer">
        Document généré le {{ \Carbon\Carbon::parse($bulletin['generated_at'])->format('d/m/Y à H:i') }} — EDUSMART-CM
    </div>
</body>
</html>
