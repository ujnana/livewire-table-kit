<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }} Export</title>
    <style>
        @php
            $paperSize = strtoupper($paperSize ?? 'A4');
            $orientation = $orientation ?? 'landscape';
            $margins = $margins ?? ['top' => '14mm', 'right' => '12mm', 'bottom' => '14mm', 'left' => '12mm'];
            $fontSize = $fontSize ?? '10px';
        @endphp

        @page {
            size: {{ $paperSize }} {{ $orientation }};
            margin: {{ $margins['top'] }} {{ $margins['right'] }} {{ $margins['bottom'] }} {{ $margins['left'] }};
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: {{ $fontSize }};
            color: #111827;
        }

        .header {
            margin-bottom: 16px;
        }

        .title {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 4px;
        }

        .subtitle {
            color: #6b7280;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            vertical-align: top;
            text-align: left;
            word-break: break-word;
        }

        th {
            background: #f9fafb;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">{{ $title }}</h1>
        <p class="subtitle">Exported at {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                @foreach ($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ max(count($headings), 1) }}">No data found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
