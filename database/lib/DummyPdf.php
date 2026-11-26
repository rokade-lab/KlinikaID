<?php

declare(strict_types=1);

namespace KlinikaID\Data;

final class DummyPdf
{
    public static function render(array $lines): string
    {
        $ops = "BT\n/F1 12 Tf\n14 TL\n50 790 Td\n";
        foreach ($lines as $line) {
            $ascii = preg_replace('/[^\x20-\x7E]/', '?', $line) ?? '';
            $ops .= '(' . strtr($ascii, ['\\' => '\\\\', '(' => '\\(', ')' => '\\)']) . ") Tj T*\n";
        }
        $ops .= "ET\n";

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Length ' . strlen($ops) . " >>\nstream\n" . $ops . 'endstream',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $body) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1) . " 0 obj\n" . $body . "\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= 'xref' . "\n" . '0 ' . (count($objects) + 1) . "\n" . "0000000000 65535 f \n";
        foreach ($offsets as $o) {
            $pdf .= sprintf("%010d 00000 n \n", $o);
        }
        $pdf .= 'trailer' . "\n" . '<< /Size ' . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF\n";
        return $pdf;
    }
}
