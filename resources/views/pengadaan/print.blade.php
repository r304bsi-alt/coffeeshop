<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Purchase Order #{{ $procurement->po_number }} - Kopi Senja</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #333; margin: 0; padding: 30px; font-size: 13px; line-height: 1.5; }
        .header { border-bottom: 2px solid #b08456; padding-bottom: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-end; }
        .brand { font-size: 24px; font-weight: bold; color: #5c3d2e; }
        .po-title { font-size: 18px; font-weight: bold; color: #333; text-align: right; }
        .meta-grid { display: flex; justify-content: space-between; margin-bottom: 25px; }
        .box { width: 48%; }
        .box-title { font-weight: bold; font-size: 11px; text-transform: uppercase; color: #777; border-bottom: 1px solid #ddd; padding-bottom: 3px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        th { background: #f5efe9; color: #5c3d2e; font-weight: bold; font-size: 11px; text-transform: uppercase; padding: 10px; border-bottom: 2px solid #dbc4a1; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row td { font-weight: bold; font-size: 14px; background: #faf8f5; border-top: 2px solid #b08456; }
        .signatures { margin-top: 40px; display: flex; justify-content: space-between; }
        .sign-box { width: 30%; text-align: center; }
        .sign-line { margin-top: 60px; border-top: 1px solid #333; font-weight: bold; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #b08456; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">
            Cetak Dokumen PO Sekarang
        </button>
    </div>

    <div class="header">
        <div>
            <div class="brand">KOPI SENJA</div>
            <div>Jl. Senja Kopi No. 88, Jakarta Selatan</div>
            <div>Email: procurement@kopisenja.com | Telp: (021) 789-1234</div>
        </div>
        <div class="po-title">
            PURCHASE ORDER (PO)<br>
            <span style="font-size: 14px; font-weight: normal; color: #666;">No: {{ $procurement->po_number }}</span><br>
            <span style="font-size: 11px; font-weight: normal; color: #888;">Tanggal: {{ $procurement->created_at->format('d F Y') }}</span>
        </div>
    </div>

    <div class="meta-grid">
        <div class="box">
            <div class="box-title">Vendor / Supplier:</div>
            <strong>{{ $procurement->supplier_name }}</strong><br>
            Email: {{ $procurement->supplier_email ?? '-' }}<br>
            Telepon: {{ $procurement->supplier_phone ?? '-' }}
        </div>
        <div class="box" style="text-align: right;">
            <div class="box-title">Pengiriman & Persetujuan:</div>
            Gudang Kopi Senja (Warehouse Division)<br>
            Disetujui Oleh: <strong>{{ $procurement->approver?->name ?? 'Owner' }}</strong><br>
            Tanggal Disetujui: {{ $procurement->approved_at?->format('d F Y, H:i') ?? '-' }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 45%;">Deskripsi Bahan Baku</th>
                <th class="text-center" style="width: 15%;">Satuan</th>
                <th class="text-right" style="width: 15%;">Jumlah</th>
                <th class="text-right" style="width: 20%;">Harga Satuan</th>
                <th class="text-right" style="width: 20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($procurement->items as $idx => $it)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td>
                    <strong>{{ $it->ingredient?->name }}</strong><br>
                    <span style="font-size: 10px; color: #777;">Kode: {{ $it->ingredient?->code }}</span>
                </td>
                <td class="text-center">{{ $it->ingredient?->unit }}</td>
                <td class="text-right">{{ number_format($it->quantity_requested, 2) }}</td>
                <td class="text-right">Rp {{ number_format($it->unit_price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($it->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="5" class="text-right">TOTAL ESTIMASI PEMBELIAN:</td>
                <td class="text-right">Rp {{ number_format($procurement->total_cost, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    @if($procurement->notes)
    <div style="margin-bottom: 25px; padding: 10px; background: #fdfaf6; border-left: 3px solid #b08456;">
        <strong>Catatan:</strong> {{ $procurement->notes }}
    </div>
    @endif

    <div class="signatures">
        <div class="sign-box">
            Dibuat Oleh:
            <div class="sign-line">{{ $procurement->creator?->name }}<br><span style="font-size: 11px; font-weight: normal;">Bagian Pengadaan</span></div>
        </div>
        <div class="sign-box">
            Disetujui Oleh:
            <div class="sign-line">{{ $procurement->approver?->name ?? 'Owner' }}<br><span style="font-size: 11px; font-weight: normal;">Owner / Pemilik Toko</span></div>
        </div>
        <div class="sign-box">
            Diterima Oleh Supplier:
            <div class="sign-line">{{ $procurement->supplier_name }}<br><span style="font-size: 11px; font-weight: normal;">Tanda Tangan & Cap</span></div>
        </div>
    </div>
</body>
</html>
