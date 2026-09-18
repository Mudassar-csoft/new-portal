<?php

namespace App\Support;

use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use XMLWriter;
use ZipArchive;

class ExcelDownload
{
    public static function make(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $worksheetPath = tempnam(sys_get_temp_dir(), 'lead-sheet-');
            $workbookPath = tempnam(sys_get_temp_dir(), 'lead-excel-');

            try {
                if ($worksheetPath === false || $workbookPath === false) {
                    throw new RuntimeException('Unable to create the Excel download.');
                }

                $xml = new XMLWriter;
                if (! $xml->openUri($worksheetPath)) {
                    throw new RuntimeException('Unable to write the Excel worksheet.');
                }

                $xml->startDocument('1.0', 'UTF-8');
                $xml->startElement('worksheet');
                $xml->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                $xml->startElement('sheetData');
                self::writeRow($xml, $headers, 1);

                $rowNumber = 2;
                foreach ($rows as $row) {
                    self::writeRow($xml, $row, $rowNumber++);
                    $xml->flush();
                }

                $xml->endElement();
                $xml->endElement();
                $xml->endDocument();
                $xml->flush();
                unset($xml);

                $archive = new ZipArchive;
                if ($archive->open($workbookPath, ZipArchive::OVERWRITE) !== true) {
                    throw new RuntimeException('Unable to create the Excel workbook.');
                }

                $parts = [
                    '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8"?>'
                        .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                        .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                        .'<Default Extension="xml" ContentType="application/xml"/>'
                        .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
                        .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
                        .'</Types>',
                    '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8"?>'
                        .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                        .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
                        .'</Relationships>',
                    'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8"?>'
                        .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                        .'<sheets><sheet name="Leads" sheetId="1" r:id="rId1"/></sheets></workbook>',
                    'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8"?>'
                        .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                        .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
                        .'</Relationships>',
                ];

                foreach ($parts as $path => $contents) {
                    if (! $archive->addFromString($path, $contents)) {
                        throw new RuntimeException('Unable to write the Excel workbook.');
                    }
                }

                if (! $archive->addFile($worksheetPath, 'xl/worksheets/sheet1.xml') || ! $archive->close()) {
                    throw new RuntimeException('Unable to finish the Excel workbook.');
                }

                readfile($workbookPath);
            } finally {
                unset($xml, $archive);
                foreach ([$worksheetPath, $workbookPath] as $path) {
                    if ($path !== false && is_file($path)) {
                        unlink($path);
                    }
                }
            }
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    private static function writeRow(XMLWriter $xml, array $values, int $rowNumber): void
    {
        $xml->startElement('row');
        $xml->writeAttribute('r', (string) $rowNumber);

        foreach (array_values($values) as $index => $value) {
            $column = '';
            for ($position = $index + 1; $position > 0; $position = intdiv($position - 1, 26)) {
                $column = chr(65 + ($position - 1) % 26).$column;
            }

            $xml->startElement('c');
            $xml->writeAttribute('r', $column.$rowNumber);
            $xml->writeAttribute('t', 'inlineStr');
            $xml->startElement('is');
            $xml->startElement('t');
            $xml->writeAttribute('xml:space', 'preserve');
            $xml->text(preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', (string) $value) ?? '');
            $xml->endElement();
            $xml->endElement();
            $xml->endElement();
        }

        $xml->endElement();
    }
}
