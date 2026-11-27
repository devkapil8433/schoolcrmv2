<?php
$logo = $this->application_model->getBranchImage($basic['branch_id'], 'printing-logo');
$pageSize = isset($page_size) ? $page_size : 'A4';
$pageSizes = array(
    'A4' => array('css' => 'A4 portrait', 'content_height' => 281, 'copies_per_sheet' => 2),
    'A5' => array('css' => 'A5 portrait', 'content_height' => 194, 'copies_per_sheet' => 1),
    'LETTER' => array('css' => 'Letter portrait', 'content_height' => 263.4, 'copies_per_sheet' => 2),
    'LEGAL' => array('css' => 'Legal portrait', 'content_height' => 340.9, 'copies_per_sheet' => 2),
);
$pageConfig = isset($pageSizes[$pageSize]) ? $pageSizes[$pageSize] : $pageSizes['A4'];
$copiesPerSheet = $pageConfig['copies_per_sheet'];
foreach ($receipts as &$receipt) {
    $itemsByFeeType = array();
    foreach ($receipt['items'] as $item) {
        $feeTypeKey = strtolower(preg_replace('/\s+/', ' ', trim($item['name'])));
        if (!isset($itemsByFeeType[$feeTypeKey])) {
            $itemsByFeeType[$feeTypeKey] = array(
                'name' => $item['name'],
                'amount' => 0,
                'discount' => 0,
                'paid' => 0,
            );
        }
        $itemsByFeeType[$feeTypeKey]['amount'] += (float) $item['amount'];
        $itemsByFeeType[$feeTypeKey]['discount'] += (float) $item['discount'];
        $itemsByFeeType[$feeTypeKey]['paid'] += (float) $item['paid'];
    }
    $receipt['items'] = array_values($itemsByFeeType);
}
unset($receipt);
$receiptPages = array();
$copyLabels = array('Student Copy', 'Office Copy');
foreach ($receipts as $receipt) {
    foreach (array_chunk($copyLabels, $copiesPerSheet) as $pageCopyLabels) {
        $receiptPages[] = array(
            'receipt' => $receipt,
            'copy_labels' => $pageCopyLabels,
        );
    }
}
$studentName = trim(($basic['first_name'] ?? '') . ' ' . ($basic['last_name'] ?? ''));
$barcodePatterns = array(
    '0' => 'nnnwwnwnn', '1' => 'wnnwnnnnw', '2' => 'nnwwnnnnw', '3' => 'wnwwnnnnn',
    '4' => 'nnnwwnnnw', '5' => 'wnnwwnnnn', '6' => 'nnwwwnnnn', '7' => 'nnnwnnwnw',
    '8' => 'wnnwnnwnn', '9' => 'nnwwnnwnn', 'A' => 'wnnnnwnnw', 'B' => 'nnwnnwnnw',
    'C' => 'wnwnnwnnn', 'D' => 'nnnnwwnnw', 'E' => 'wnnnwwnnn', 'F' => 'nnwnwwnnn',
    'G' => 'nnnnnwwnw', 'H' => 'wnnnnwwnn', 'I' => 'nnwnnwwnn', 'J' => 'nnnnwwwnn',
    'K' => 'wnnnnnnww', 'L' => 'nnwnnnnww', 'M' => 'wnwnnnnwn', 'N' => 'nnnnwnnww',
    'O' => 'wnnnwnnwn', 'P' => 'nnwnwnnwn', 'Q' => 'nnnnnnwww', 'R' => 'wnnnnnwwn',
    'S' => 'nnwnnnwwn', 'T' => 'nnnnwnwwn', 'U' => 'wwnnnnnnw', 'V' => 'nwwnnnnnw',
    'W' => 'wwwnnnnnn', 'X' => 'nwnnwnnnw', 'Y' => 'wwnnwnnnn', 'Z' => 'nwwnwnnnn',
    '-' => 'nwnnnnwnw', '.' => 'wwnnnnwnn', ' ' => 'nwwnnwnnn', '$' => 'nwnwnwnnn',
    '/' => 'nwnwnnnwn', '+' => 'nwnnnwnwn', '%' => 'nnnwnwnwn', '*' => 'nwnnwnwnn',
);
$makeBarcode = function ($value) use ($barcodePatterns) {
    $value = '*' . strtoupper(preg_replace('/[^A-Z0-9. $/+%-]/', '', (string) $value)) . '*';
    $x = 0;
    $rectangles = '';
    foreach (str_split($value) as $character) {
        if (!isset($barcodePatterns[$character])) {
            continue;
        }
        $pattern = $barcodePatterns[$character];
        for ($i = 0; $i < 9; $i++) {
            $width = $pattern[$i] === 'w' ? 3 : 1;
            if ($i % 2 === 0) {
                $rectangles .= '<rect x="' . $x . '" y="0" width="' . $width . '" height="42" />';
            }
            $x += $width;
        }
        $x += 1;
    }
    return '<svg class="receipt-barcode" viewBox="0 0 ' . max(1, $x) . ' 42" role="img" aria-label="Receipt barcode">' . $rectangles . '</svg>';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Fee Receipt</title>
    <style>
        @page { size: <?= $pageConfig['css'] ?>; margin: 8mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #202938; font: 12px Arial, sans-serif; }
        .receipt-page { height: <?= $pageConfig['content_height'] ?>mm; display: grid; grid-template-columns: minmax(0, 1fr); grid-template-rows: repeat(<?= $copiesPerSheet ?>, minmax(0, 1fr)); gap: 6mm; page-break-after: always; break-after: page; }
        .receipt-page:last-child { page-break-after: auto; break-after: auto; }
        .receipt-card { min-height: 0; height: 100%; border: 1px solid #cad3df; border-top: 4px solid #f59e0b; border-radius: 7px; padding: 5mm; position: relative; page-break-inside: avoid; break-inside: avoid; }
        .receipt-heading { display: flex; gap: 8px; align-items: center; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0; }
        .receipt-logo { width: 64px; height: 54px; object-fit: contain; }
        .school-name { font-size: 17px; font-weight: 700; margin: 0 0 3px; color: #172033; }
        .school-meta { color: #667085; font-size: 10px; line-height: 1.5; }
        .receipt-header-right { margin-left: auto; display: flex; align-items: center; gap: 7px; }
        .receipt-tag { text-align: right; }
        .receipt-tag strong { display: block; color: #b45309; font-size: 16px; }
        .receipt-tag span { color: #667085; }
        .copy-label { display: inline-block; margin-top: 3px; padding: 2px 6px; border: 1px solid #b45309; border-radius: 3px; color: #92400e; font-size: 9px; font-weight: 700; text-transform: uppercase; }
        .receipt-barcode-tile { width: 25mm; height: 25mm; padding: 3mm 2mm 2mm; border: 1px solid #d8e0eb; border-radius: 5px; background: #fff; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .receipt-barcode { display: block; width: 21mm; height: 10mm; fill: #111827; }
        .receipt-barcode-label { margin-top: 1mm; color: #667085; font-size: 8px; line-height: 1; }
        .receipt-page-a5 .receipt-logo { width: 44px; height: 40px; }
        .receipt-page-a5 .school-name { font-size: 13px; }
        .receipt-page-a5 .school-meta { font-size: 8px; }
        .receipt-page-a5 .receipt-tag strong { font-size: 12px; }
        .receipt-page-a5 .receipt-barcode-tile { width: 22mm; height: 22mm; padding: 2mm; }
        .receipt-page-a5 .receipt-barcode { width: 18mm; height: 8mm; }
        .receipt-page-a5 .receipt-barcode-label { font-size: 7px; }
        .receipt-page-a5 .student-grid { gap: 4px 8px; padding: 7px 0; font-size: 10px; }
        .receipt-page-a5 th, .receipt-page-a5 td { padding: 4px 5px; }
        .student-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 16px; padding: 10px 0; }
        .field { color: #475467; }
        .field b { color: #182230; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th, td { border: 1px solid #d9e0e8; padding: 6px 7px; text-align: left; }
        th { background: #f3f6fa; font-size: 10px; text-transform: uppercase; color: #475467; }
        .numeric { text-align: right; white-space: nowrap; }
        .receipt-totals { width: 55%; margin: 10px 0 0 auto; }
        .receipt-totals td { border: 0; padding: 3px 5px; }
        .receipt-totals tr:last-child { border-top: 1px solid #aab5c3; font-weight: 700; font-size: 13px; }
        .receipt-footer { display: flex; justify-content: flex-end; align-items: end; gap: 12px; position: absolute; left: 7mm; right: 7mm; bottom: 7mm; color: #667085; font-size: 9px; }
        .signature { min-width: 110px; border-top: 1px solid #667085; text-align: center; padding-top: 5px; }
        .reversed-stamp { position: absolute; top: 48%; left: 12%; right: 12%; transform: rotate(-20deg); color: rgba(185, 28, 28, .72); border: 4px solid rgba(185, 28, 28, .65); text-align: center; font-size: 30px; font-weight: 700; letter-spacing: 3px; padding: 8px; pointer-events: none; }
        .reversal-reason { color: #b91c1c; font-weight: 700; }
        .empty-state { padding: 30px; text-align: center; color: #667085; }
        @media print {
            .receipt-page { height: <?= $pageConfig['content_height'] ?>mm; }
            .receipt-card { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <main id="receiptSheet">
        <?php foreach ($receiptPages as $pageReceipts): ?>
            <section class="receipt-page <?= $pageSize === 'A5' ? 'receipt-page-a5' : '' ?>">
            <?php $receipt = $pageReceipts['receipt']; ?>
            <?php foreach ($pageReceipts['copy_labels'] as $copyLabel): ?>
            <article class="receipt-card">
                <header class="receipt-heading">
                    <?php if (!empty($logo)): ?>
                        <img class="receipt-logo" src="<?= html_escape($logo) ?>" alt="School logo">
                    <?php endif; ?>
                    <div>
                        <h1 class="school-name"><?= html_escape($basic['school_name'] ?? '') ?></h1>
                        <div class="school-meta">
                            <?= html_escape($basic['school_address'] ?? '') ?><br>
                            <?= html_escape($basic['school_mobileno'] ?? '') ?>
                            <?php if (!empty($basic['school_email'])): ?> · <?= html_escape($basic['school_email']) ?><?php endif; ?>
                        </div>
                    </div>
                    <div class="receipt-header-right">
                        <div class="receipt-tag">
                            <strong><?= !empty($receipt['is_reversed']) ? 'VOID / REVERSED' : 'FEE RECEIPT' ?></strong>
                            <?= html_escape($receipt['receipt_label']) ?><br>
                            <span><?= html_escape(_d($receipt['date'])) ?></span>
                            <div class="copy-label"><?= html_escape($copyLabel) ?></div>
                        </div>
                        <div class="receipt-barcode-tile">
                            <?= $makeBarcode($receipt['receipt_label']) ?>
                            <div class="receipt-barcode-label"><?= html_escape($receipt['receipt_label']) ?></div>
                        </div>
                    </div>
                </header>
                <section class="student-grid">
                    <div class="field">Student: <b><?= html_escape($studentName) ?></b></div>
                    <div class="field">Register No: <b><?= html_escape($basic['register_no'] ?? '') ?></b></div>
                    <div class="field">Class: <b><?= html_escape(($basic['class_name'] ?? '') . ' (' . ($basic['section_name'] ?? '') . ')') ?></b></div>
                    <div class="field">Father/Guardian: <b><?= html_escape($basic['father_name'] ?? '') ?></b></div>
                    <div class="field">Payment method: <b><?= html_escape($receipt['payment_method'] ?: '—') ?></b></div>
                    <div class="field">Remarks: <b><?= html_escape($receipt['remarks'] ?: '—') ?></b></div>
                    <?php if (!empty($receipt['is_reversed'])): ?>
                        <div class="field reversal-reason">Reversal reason: <?= html_escape($receipt['reversal_reason']) ?></div>
                    <?php endif; ?>
                </section>
                <table>
                    <thead><tr><th>#</th><th>Fee head</th><th class="numeric">Amount</th><th class="numeric">Discount</th><th class="numeric">Paid</th></tr></thead>
                    <tbody>
                        <?php foreach ($receipt['items'] as $itemIndex => $item): ?>
                            <tr>
                                <td><?= $itemIndex + 1 ?></td>
                                <td><?= html_escape($item['name']) ?></td>
                                <td class="numeric"><?= currencyFormat($item['amount']) ?></td>
                                <td class="numeric"><?= currencyFormat($item['discount']) ?></td>
                                <td class="numeric"><?= currencyFormat($item['paid']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <table class="receipt-totals">
                    <tr><td>Fine</td><td class="numeric"><?= currencyFormat($receipt['total_fine']) ?></td></tr>
                    <tr><td>Total received</td><td class="numeric"><?= currencyFormat($receipt['total_paid'] + $receipt['total_fine']) ?></td></tr>
                </table>
                <footer class="receipt-footer">
                    <div class="signature">Authorized signature</div>
                </footer>
                <?php if (!empty($receipt['is_reversed'])): ?>
                    <div class="reversed-stamp">REVERSED</div>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
            </section>
        <?php endforeach; ?>
    </main>
    <script>
        (function() {
            window.addEventListener('load', function() {
                window.print();
            });
        }());
    </script>
</body>
</html>
