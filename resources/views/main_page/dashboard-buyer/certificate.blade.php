<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat {{ $order->certificate_number }} - CAMAR</title>
    <style>
        :root {
            color: #17382f;
            background: #edf5ef;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            padding: 32px;
        }

        .toolbar {
            display: flex;
            justify-content: flex-end;
            max-width: 960px;
            margin: 0 auto 16px;
        }

        .toolbar button {
            border: 0;
            border-radius: 6px;
            background: #1f7a4d;
            color: #fff;
            cursor: pointer;
            font-weight: 700;
            padding: 10px 16px;
        }

        .certificate {
            max-width: 960px;
            margin: 0 auto;
            background: #fff;
            border: 10px solid #1f7a4d;
            box-shadow: 0 20px 60px rgba(18, 55, 42, .15);
            min-height: 640px;
            padding: 56px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #d9eadf;
            padding-bottom: 24px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand img {
            height: 58px;
            width: auto;
        }

        .brand strong {
            display: block;
            font-size: 24px;
            letter-spacing: 0;
        }

        .number {
            text-align: right;
            color: #527067;
            font-size: 14px;
        }

        h1 {
            font-size: 42px;
            margin: 52px 0 14px;
            text-align: center;
        }

        .subtitle {
            color: #527067;
            font-size: 16px;
            margin: 0 auto 42px;
            max-width: 620px;
            text-align: center;
        }

        .recipient {
            margin: 0 auto 36px;
            max-width: 720px;
            text-align: center;
        }

        .recipient span {
            color: #527067;
            display: block;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .recipient strong {
            border-bottom: 2px solid #1f7a4d;
            display: block;
            font-size: 32px;
            padding-bottom: 12px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-top: 42px;
        }

        .item {
            border: 1px solid #d9eadf;
            border-radius: 6px;
            padding: 16px;
        }

        .item span {
            color: #527067;
            display: block;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .item strong {
            display: block;
            font-size: 17px;
        }

        .footer {
            align-items: end;
            display: flex;
            justify-content: space-between;
            margin-top: 58px;
        }

        .signature {
            text-align: right;
        }

        .signature .line {
            border-top: 2px solid #17382f;
            margin-top: 54px;
            padding-top: 8px;
            min-width: 260px;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .toolbar {
                display: none;
            }

            .certificate {
                border-width: 8px;
                box-shadow: none;
                max-width: none;
                min-height: calc(100vh - 112px);
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>

    <main class="certificate">
        <header class="header">
            <div class="brand">
                <img src="{{ asset('images/logo.png') }}" alt="CAMAR">
                <div>
                    <strong>CAMAR</strong>
                    <span>Carbon Offset Certificate</span>
                </div>
            </div>
            <div class="number">
                <div>No. Sertifikat</div>
                <strong>{{ $order->certificate_number }}</strong>
            </div>
        </header>

        <h1>Sertifikat Carbon Offset</h1>
        <p class="subtitle">
            Sertifikat ini menyatakan bahwa pembelian kredit karbon berikut telah diverifikasi dan diterbitkan melalui platform CAMAR.
        </p>

        <section class="recipient">
            <span>Diberikan kepada</span>
            <strong>{{ $order->buyer_name ?? $order->user?->name }}</strong>
        </section>

        <section class="grid">
            <div class="item">
                <span>Proyek</span>
                <strong>{{ $order->project?->name ?? '-' }}</strong>
            </div>
            <div class="item">
                <span>Penjual / Pengelola Proyek</span>
                <strong>{{ $order->project?->seller?->name ?? $order->project?->company_name ?? '-' }}</strong>
            </div>
            <div class="item">
                <span>Jumlah Offset</span>
                <strong>{{ number_format($order->quantity, 0, ',', '.') }} ton CO2e</strong>
            </div>
            <div class="item">
                <span>Nomor Order</span>
                <strong>{{ $order->order_number }}</strong>
            </div>
            <div class="item">
                <span>Tanggal Terbit</span>
                <strong>{{ $order->certificate_issued_at?->format('d M Y') ?? '-' }}</strong>
            </div>
            <div class="item">
                <span>Standar Proyek</span>
                <strong>{{ $order->project?->standard ?? '-' }}</strong>
            </div>
        </section>

        <footer class="footer">
            <div>
                <strong>CAMAR</strong>
                <div>Platform Carbon Offset</div>
            </div>
            <div class="signature">
                <div>Diverifikasi oleh</div>
                <div class="line">
                    <strong>{{ $order->certificateIssuer?->name ?? 'Auditor Pemerintah' }}</strong>
                </div>
            </div>
        </footer>
    </main>
</body>
</html>
