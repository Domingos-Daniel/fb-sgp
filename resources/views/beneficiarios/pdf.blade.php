<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha do Beneficiário - {{ $beneficiario->nome }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            border-radius: 50%;
            width: 120px;
            height: 120px;
            object-fit: cover;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header h2 {
            font-size: 18px;
            color: #555;
            font-weight: normal;
            margin-top: 0;
        }
        .details {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .details th, .details td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
            vertical-align: top;
        }
        .details th {
            background-color: #f7f7f7;
            font-weight: bold;
        }
        .details td {
            background-color: #fff;
        }
        .section-title {
            background-color: #f0f0f0;
            padding: 8px 12px;
            font-size: 18px;
            margin-top: 20px;
        }
        .details .important {
            background-color: #e9f7ef;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 12px;
            color: #666;
            padding: 10px 0;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($beneficiario->imagem)
                <img src="{{ public_path('storage/' . $beneficiario->imagem) }}" alt="Foto do Beneficiário">
            @endif
            <h1>{{ $beneficiario->nome }}</h1>
            <h2>{{ $beneficiario->tipo_beneficiario === 'Individual' ? 'Ficha Pessoal' : 'Ficha da Instituição' }}</h2>
        </div>

        <h3 class="section-title">Informações Básicas</h3>
        <table class="details">
            @if($beneficiario->tipo_beneficiario === 'Individual')
                <tr>
                    <th>BI</th>
                    <td>{{ $beneficiario->bi }}</td>
                </tr>
                <tr>
                    <th>Data de Nascimento</th>
                    <td>{{ $beneficiario->data_nascimento ? date('d/m/Y', strtotime($beneficiario->data_nascimento)) : 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Gênero</th>
                    <td>{{ $beneficiario->genero }}</td>
                </tr>
            @else
                <tr>
                    <th>NIF</th>
                    <td>{{ $beneficiario->nif }}</td>
                </tr>
                <tr>
                    <th>Endereço</th>
                    <td>{{ $beneficiario->endereco }}</td>
                </tr>
            @endif
            <tr class="important">
                <th>Tipo de Beneficiário</th>
                <td>{{ $beneficiario->tipo_beneficiario }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $beneficiario->email }}</td>
            </tr>
            <tr>
                <th>Telemóvel</th>
                <td>{{ $beneficiario->telemovel }}</td>
            </tr>
            <tr>
                <th>Telemóvel Alternativo</th>
                <td>{{ $beneficiario->telemovel_alternativo }}</td>
            </tr>
        </table>

        <h3 class="section-title">Educação e Profissão</h3>
        <table class="details">
            @if($beneficiario->tipo_beneficiario === 'Individual')
                <tr>
                    <th>Ano de Frequência</th>
                    <td>{{ $beneficiario->ano_frequencia }}</td>
                </tr>
                <tr>
                    <th>Curso</th>
                    <td>{{ $beneficiario->curso }}</td>
                </tr>
                <tr>
                    <th>Universidade ou Escola</th>
                    <td>{{ $beneficiario->universidade_ou_escola }}</td>
                </tr>
            @endif
        </table>

        <h3 class="section-title">Outras Informações</h3>
        <table class="details">
            <tr>
                <th>Observações</th>
                <td>{!! nl2br(e($beneficiario->observacoes)) !!}</td>
            </tr>
            @if($beneficiario->tipo_beneficiario === 'Institucional')
                <tr>
                    <th>Coordenadas Bancárias</th>
                    <td>{{ $beneficiario->coordenadas_bancarias }}</td>
                </tr>
            @endif
        </table>

        <h3 class="section-title">Informações de Sistema</h3>
        <table class="details">
            <tr>
                <th>Criado em</th>
                <td>{{ $beneficiario->created_at->format('d M Y, H:i') }}</td>
            </tr>
            <tr>
                <th>Atualizado em</th>
                <td>{{ $beneficiario->updated_at->format('d M Y, H:i') }}</td>
            </tr>
        </table>
    </div>
    <div class="footer">
        Documento gerado por computador pelo SGP em {{ now()->format('d/m/Y H:i') }}.
    </div>
</body>
</html>
