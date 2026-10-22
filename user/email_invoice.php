<?php
declare(strict_types=1);
require_once __DIR__ . '/../auth/_bootstrap.php';

function invoice_email_response(int $status, array $response): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function invoice_email_text_hex(string $text): string
{
    $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
    if ($encoded === false) {
        throw new RuntimeException('Invoice text could not be encoded for PDF output.');
    }
    return strtoupper(bin2hex($encoded));
}

function invoice_pdf_text(float $x, float $y, int $size, string $text, bool $bold = false, string $color = '0 0 0'): string
{
    $font = $bold ? 'F2' : 'F1';
    return "{$color} rg BT /{$font} {$size} Tf {$x} {$y} Td <" . invoice_email_text_hex($text) . "> Tj ET\n";
}

function invoice_pdf_document(array $invoice, array $business): string
{
    $items = array_chunk($invoice['items'], 16);
    if ($items === []) {
        $items = [[]];
    }

    $pages = [];
    foreach ($items as $pageIndex => $pageItems) {
        $commands = "0.05 0.35 0.28 rg 0 550 408 242 re f\n";
        $commands .= "0.08 0.42 0.33 rg 408 550 102 242 re f\n";
        $commands .= "0.04 0.29 0.23 rg 510 550 102 242 re f\n";
        $commands .= "1 1 1 rg 35 38 542 555 re f\n";
        $commands .= invoice_pdf_text(60, 748, 13, mb_strimwidth($business['name'], 0, 42, '…', 'UTF-8'), true, '1 1 1');
        $commands .= invoice_pdf_text(60, 695, 30, 'INVOICE', true, '1 1 1');
        $commands .= invoice_pdf_text(60, 674, 12, '# ' . $invoice['id'], true, '1 1 1');
        $commands .= invoice_pdf_text(60, 645, 10, $business['name'] . ' - ' . $invoice['issued'], false, '1 1 1');
        $commands .= invoice_pdf_text(355, 748, 11, 'DATE INFORMATION', true, '1 1 1');
        $commands .= invoice_pdf_text(355, 730, 10, 'Issued: ' . $invoice['issued'], false, '1 1 1');
        $commands .= invoice_pdf_text(355, 714, 10, 'Due: ' . $invoice['due'], false, '1 1 1');
        $commands .= invoice_pdf_text(355, 681, 11, 'INVOICE TO', true, '1 1 1');
        $commands .= invoice_pdf_text(355, 663, 10, mb_strimwidth($invoice['client'], 0, 34, '…', 'UTF-8'), false, '1 1 1');
        $commands .= invoice_pdf_text(355, 628, 11, 'TOTAL DUE: ' . $business['currency'] . number_format($invoice['total'], 2), true, '1 1 1');
        $commands .= "0.91 0.92 0.93 rg 55 582 502 25 re f\n";
        $commands .= invoice_pdf_text(64, 591, 10, 'NO.', true);
        $commands .= invoice_pdf_text(104, 591, 10, 'ITEM DESCRIPTION', true);
        $commands .= invoice_pdf_text(350, 591, 10, 'PRICE', true);
        $commands .= invoice_pdf_text(425, 591, 10, 'QTY', true);
        $commands .= invoice_pdf_text(485, 591, 10, 'TOTAL', true);

        $rowY = 560;
        foreach ($pageItems as $index => $item) {
            if ($index % 2 === 1) {
                $commands .= "0.95 0.95 0.96 rg 55 " . ($rowY - 7) . " 502 28 re f\n";
            }
            $lineNumber = ($pageIndex * 16) + $index + 1;
            $commands .= invoice_pdf_text(64, $rowY, 10, str_pad((string) $lineNumber, 2, '0', STR_PAD_LEFT) . '.');
            $commands .= invoice_pdf_text(104, $rowY, 10, mb_strimwidth($item['description'], 0, 38, '…', 'UTF-8'));
            $commands .= invoice_pdf_text(350, $rowY, 10, $business['currency'] . number_format($item['price'], 2));
            $commands .= invoice_pdf_text(425, $rowY, 10, (string) $item['quantity']);
            $commands .= invoice_pdf_text(485, $rowY, 10, $business['currency'] . number_format($item['amount'], 2));
            $rowY -= 28;
        }

        if ($pageIndex === count($items) - 1) {
            $commands .= invoice_pdf_text(60, 170, 15, 'Thank you for your business', true);
            $commands .= invoice_pdf_text(60, 154, 9, $business['mail']);
            $paymentMethods = array_slice(array_values(array_filter(
                array_map('trim', preg_split('/\R/u', $business['payment_methods']) ?: []),
                static fn(string $method): bool => $method !== ''
            )), 0, 3);
            $bankDetails = array_filter([
                'Bank: ' . $business['bank_name'],
                'Account name: ' . $business['bank_account_name'],
                'Account number: ' . $business['bank_account_number'],
                'Branch: ' . $business['bank_branch'],
                'SWIFT / BIC: ' . $business['bank_swift'],
            ], static fn(string $detail): bool => !str_ends_with($detail, ': '));
            $paymentY = 136;
            if ($paymentMethods !== [] || $bankDetails !== []) {
                $commands .= invoice_pdf_text(60, $paymentY, 9, 'PAYMENT DETAILS', true);
                $paymentY -= 13;
                foreach ($paymentMethods as $index => $method) {
                    $label = $index === 0 ? 'Methods: ' : '          ';
                    $commands .= invoice_pdf_text(60, $paymentY, 8, $label . mb_strimwidth($method, 0, 45, '…', 'UTF-8'));
                    $paymentY -= 11;
                }
                foreach ($bankDetails as $detail) {
                    $commands .= invoice_pdf_text(60, $paymentY, 8, mb_strimwidth($detail, 0, 54, '…', 'UTF-8'));
                    $paymentY -= 11;
                }
            }
            $commands .= invoice_pdf_text(370, 112, 10, 'Subtotal: ' . $business['currency'] . number_format($invoice['subtotal'], 2));
            $commands .= invoice_pdf_text(370, 94, 10, 'Tax: ' . $business['currency'] . number_format($invoice['tax'], 2));
            $commands .= "0.05 0.35 0.28 rg 360 55 190 27 re f\n";
            $commands .= invoice_pdf_text(372, 64, 12, 'TOTAL: ' . $business['currency'] . number_format($invoice['total'], 2), true, '1 1 1');
        } else {
            $commands .= invoice_pdf_text(60, 70, 10, 'Continued on next page', false, '0.4 0.4 0.4');
        }
        $commands .= invoice_pdf_text(60, 24, 7, 'Mari Flow - Invoice Generator App by Smug Solutions.', false, '0.4 0.4 0.4');
        $commands .= invoice_pdf_text(520, 24, 8, 'Page ' . ($pageIndex + 1) . ' of ' . count($items), false, '0.4 0.4 0.4');
        $pages[] = $commands;
    }

    $objects = [
        1 => '<< /Type /Catalog /Pages 2 0 R >>',
        3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
        4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
    ];
    $pageReferences = [];
    foreach ($pages as $index => $content) {
        $contentId = 5 + ($index * 2);
        $pageId = $contentId + 1;
        $objects[$contentId] = '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . "endstream";
        $objects[$pageId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents {$contentId} 0 R >>";
        $pageReferences[] = "{$pageId} 0 R";
    }
    $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $pageReferences) . '] /Count ' . count($pages) . ' >>';
    ksort($objects);

    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    $maxObject = max(array_keys($objects));
    for ($objectId = 1; $objectId <= $maxObject; $objectId++) {
        $offsets[$objectId] = strlen($pdf);
        $pdf .= $objectId . " 0 obj\n" . $objects[$objectId] . "\nendobj\n";
    }
    $xrefOffset = strlen($pdf);
    $pdf .= "xref\n0 " . ($maxObject + 1) . "\n0000000000 65535 f \n";
    for ($objectId = 1; $objectId <= $maxObject; $objectId++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$objectId]);
    }
    $pdf .= "trailer\n<< /Size " . ($maxObject + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";
    return $pdf;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    invoice_email_response(405, ['success' => false, 'message' => 'Use POST to generate an invoice PDF.']);
}
if (!isset($_SESSION['auth_user']['id'])) {
    invoice_email_response(401, ['success' => false, 'message' => 'Sign in to generate invoice PDFs.']);
}
if (!auth_valid_csrf()) {
    invoice_email_response(403, ['success' => false, 'message' => 'Your session expired. Refresh the page and try again.']);
}

$invoiceInput = $_POST['invoice'] ?? '';
$businessInput = $_POST['business'] ?? '';
if (!is_string($invoiceInput) || !is_string($businessInput)) {
    invoice_email_response(422, ['success' => false, 'message' => 'Invoice details are incomplete.']);
}
$invoice = json_decode($invoiceInput, true);
$business = json_decode($businessInput, true);
if (!is_array($invoice) || !is_array($business)) {
    invoice_email_response(422, ['success' => false, 'message' => 'Invoice details could not be read.']);
}

$invoiceId = $invoice['id'] ?? null;
$client = $invoice['c'] ?? null;
$issued = $invoice['i'] ?? null;
$due = $invoice['d'] ?? null;
$itemsInput = $invoice['items'] ?? null;
$taxRate = $invoice['t'] ?? null;
if (
    !is_string($invoiceId) || !preg_match('/^[A-Za-z0-9_-]{1,80}$/', $invoiceId)
    || !is_string($client) || trim($client) === '' || mb_strlen($client) > 160
    || !is_string($issued) || !is_string($due)
    || !is_numeric($taxRate) || (float) $taxRate < 0 || (float) $taxRate > 100
    || !is_array($itemsInput) || count($itemsInput) > 64
) {
    invoice_email_response(422, ['success' => false, 'message' => 'Invoice details are invalid.']);
}
foreach ([$issued, $due] as $date) {
    $parsed = DateTime::createFromFormat('!Y-m-d', $date);
    if (!$parsed || $parsed->format('Y-m-d') !== $date) {
        invoice_email_response(422, ['success' => false, 'message' => 'Invoice dates are invalid.']);
    }
}

$items = [];
$subtotal = 0.0;
foreach ($itemsInput as $item) {
    if (!is_array($item)) {
        invoice_email_response(422, ['success' => false, 'message' => 'Invoice item details are invalid.']);
    }
    $description = $item['d'] ?? null;
    $quantity = $item['q'] ?? null;
    $price = $item['p'] ?? null;
    if (
        !is_string($description) || trim($description) === '' || mb_strlen($description) > 200
        || !is_numeric($quantity) || (float) $quantity <= 0 || (float) $quantity > 100000
        || !is_numeric($price) || (float) $price < 0 || (float) $price > 1000000000
    ) {
        invoice_email_response(422, ['success' => false, 'message' => 'Invoice item details are invalid.']);
    }
    $amount = (float) $quantity * (float) $price;
    $subtotal += $amount;
    $items[] = [
        'description' => trim($description),
        'quantity' => (float) $quantity,
        'price' => (float) $price,
        'amount' => $amount,
    ];
}
if ($items === []) {
    invoice_email_response(422, ['success' => false, 'message' => 'Add at least one invoice item before generating a PDF.']);
}

$businessName = $business['name'] ?? 'Ledgerly';
$businessMail = $business['mail'] ?? '';
$currency = $business['cur'] ?? '$';
$paymentMethods = $business['paymentMethods'] ?? '';
$bankDetailsInput = [
    'bank_name' => $business['bankName'] ?? '',
    'bank_account_name' => $business['bankAccountName'] ?? '',
    'bank_account_number' => $business['bankAccountNumber'] ?? '',
    'bank_branch' => $business['bankBranch'] ?? '',
    'bank_swift' => $business['bankSwift'] ?? '',
];
if (
    !is_string($businessName) || trim($businessName) === '' || mb_strlen($businessName) > 160
    || !is_string($businessMail) || mb_strlen($businessMail) > 254
    || !is_string($currency) || mb_strlen($currency) > 8
    || !is_string($paymentMethods) || mb_strlen($paymentMethods) > 500
) {
    invoice_email_response(422, ['success' => false, 'message' => 'Business details are invalid.']);
}
foreach ($bankDetailsInput as $value) {
    if (!is_string($value) || mb_strlen($value) > 120) {
        invoice_email_response(422, ['success' => false, 'message' => 'Bank details are invalid.']);
    }
}

$tax = $subtotal * (float) $taxRate / 100;
$invoice['id'] = $invoiceId;
$invoice['client'] = trim($client);
$invoice['issued'] = $issued;
$invoice['due'] = $due;
$invoice['items'] = $items;
$invoice['subtotal'] = $subtotal;
$invoice['tax'] = $tax;
$invoice['total'] = $subtotal + $tax;
$business = [
    'name' => trim($businessName),
    'mail' => $businessMail,
    'currency' => $currency,
    'payment_methods' => trim($paymentMethods),
    'bank_name' => trim($bankDetailsInput['bank_name']),
    'bank_account_name' => trim($bankDetailsInput['bank_account_name']),
    'bank_account_number' => trim($bankDetailsInput['bank_account_number']),
    'bank_branch' => trim($bankDetailsInput['bank_branch']),
    'bank_swift' => trim($bankDetailsInput['bank_swift']),
];

$pdf = invoice_pdf_document($invoice, $business);
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="invoice-' . $invoiceId . '.pdf"');
header('Content-Length: ' . strlen($pdf));
header('Cache-Control: private, no-store, max-age=0');
echo $pdf;
