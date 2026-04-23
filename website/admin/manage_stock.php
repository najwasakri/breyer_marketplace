<?php
$produk_list = [
    'BAJU KORPORAT',
    'BAJU T-SHIRT KOLEJ',
    'BAJU COMPUTER SYSTEM',
    'BAJU INFORMATION SYSTEM',
    'BAJU ELECTRICAL',
    'BAJU CHEF'
];
$saiz_list = ['S', 'M', 'L', 'XL'];

$stok_file = __DIR__ . '/stok_produk.json';
if (!file_exists($stok_file)) {
    $contoh = [
        'BAJU KORPORAT' => ['S' => 0, 'M' => 0, 'L' => 0, 'XL' => 0],
        'BAJU T-SHIRT KOLEJ' => ['S' => 2, 'M' => 4, 'L' => 0, 'XL' => 1],
        'BAJU COMPUTER SYSTEM' => ['S' => 5, 'M' => 0, 'L' => 3, 'XL' => 0],
        'BAJU INFORMATION SYSTEM' => ['S' => 0, 'M' => 0, 'L' => 0, 'XL' => 0],
        'BAJU ELECTRICAL' => ['S' => 0, 'M' => 0, 'L' => 0, 'XL' => 0],
        'BAJU CHEF' => ['S' => 0, 'M' => 0, 'L' => 0, 'XL' => 0],
    ];
    file_put_contents($stok_file, json_encode($contoh, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$stok_produk = json_decode(file_get_contents($stok_file), true);
if (!is_array($stok_produk)) {
    $stok_produk = [];
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stok']) && is_array($_POST['stok'])) {
    foreach ($_POST['stok'] as $produk => $sizes) {
        if (!isset($stok_produk[$produk]) || !is_array($sizes)) {
            continue;
        }

        foreach ($sizes as $size => $value) {
            $stok_produk[$produk][$size] = (int) $value;
        }
    }

    file_put_contents($stok_file, json_encode($stok_produk, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $message = 'Kemaskini stok berjaya!';
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Pengurusan Stok Produk</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        h2 { color: #003B95; }
        input[type=number] { width: 60px; }
        button { background: #003B95; color: white; border: none; padding: 8px 18px; border-radius: 6px; margin-top: 15px; }
        button:hover { background: #002b70; }
        .message { margin: 0 0 16px; color: green; }
    </style>
</head>
<body>
    <h2>Pengurusan Stok Produk</h2>

    <?php if ($message !== ''): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post">
        <table style="border-collapse:collapse; min-width:600px; background:white;">
            <thead>
                <tr style="background:#003B95; color:white;">
                    <th style="padding:10px; border:1px solid #ccc;">Nama Item</th>
                    <th style="padding:10px; border:1px solid #ccc;">S</th>
                    <th style="padding:10px; border:1px solid #ccc;">M</th>
                    <th style="padding:10px; border:1px solid #ccc;">L</th>
                    <th style="padding:10px; border:1px solid #ccc;">XL</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produk_list as $produk): ?>
                <tr>
                    <td style="padding:8px 12px; border:1px solid #ccc; font-weight:bold; background:#f7faff;">
                        <?= htmlspecialchars($produk) ?>
                    </td>
                    <?php foreach ($saiz_list as $saiz): ?>
                    <td style="padding:8px; border:1px solid #ccc;">
                        <input type="number" name="stok[<?= htmlspecialchars($produk) ?>][<?= $saiz ?>]" value="<?= isset($stok_produk[$produk][$saiz]) ? (int) $stok_produk[$produk][$saiz] : 0 ?>" min="0" style="width:60px;">
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" style="margin-top:24px;">Simpan</button>
    </form>
</body>
</html>
