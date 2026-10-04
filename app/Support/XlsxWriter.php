<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

/**
 * Minimal native OOXML (.xlsx) writer.
 *
 * Accepts the same sheet structure produced by ReportingService:
 * each sheet = ['name' => string, 'widths' => array, 'rows' => array],
 * each row   = ['cells' => array, 'style' => ?'sTitle'|'sHeader'],
 * each cell  = scalar string OR ['v' => mixed, 'num' => bool, 'cur' => bool].
 */
class XlsxWriter
{
    public function write(array $sheets): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        if ($tmp === false) {
            throw new RuntimeException('Unable to create a temporary file for the Excel export.');
        }

        @unlink($tmp);

        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::CREATE) !== true) {
            throw new RuntimeException('Unable to open the Excel archive for writing.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes($sheets));
        $zip->addFromString('_rels/.rels', $this->rootRels());
        $zip->addFromString('xl/workbook.xml', $this->workbook($sheets));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRels($sheets));
        $zip->addFromString('xl/styles.xml', $this->styles());

        foreach (array_values($sheets) as $index => $sheet) {
            $zip->addFromString('xl/worksheets/sheet' . ($index + 1) . '.xml', $this->worksheet($sheet));
        }

        $zip->close();

        $binary = file_get_contents($tmp);
        @unlink($tmp);

        if ($binary === false) {
            throw new RuntimeException('Unable to read the generated Excel file.');
        }

        return $binary;
    }

    private function contentTypes(array $sheets): string
    {
        $overrides = '';
        foreach (array_values($sheets) as $index => $sheet) {
            $n = $index + 1;
            $overrides .= '<Override PartName="/xl/worksheets/sheet' . $n . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . $overrides
            . '</Types>';
    }

    private function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function workbook(array $sheets): string
    {
        $items = '';
        foreach (array_values($sheets) as $index => $sheet) {
            $rId = $index + 1;
            $items .= '<sheet name="' . $this->xml($this->safeSheetName((string) $sheet['name'])) . '" sheetId="' . $rId . '" r:id="rId' . $rId . '"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>' . $items . '</sheets>'
            . '</workbook>';
    }

    private function workbookRels(array $sheets): string
    {
        $rels = '';
        foreach (array_values($sheets) as $index => $sheet) {
            $rId = $index + 1;
            $rels .= '<Relationship Id="rId' . $rId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $rId . '.xml"/>';
        }
        $rels .= '<Relationship Id="rId' . (count($sheets) + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . $rels . '</Relationships>';
    }

    private function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="2">'
            . '<numFmt numFmtId="164" formatCode="#,##0"/>'
            . '<numFmt numFmtId="165" formatCode="&quot;₱&quot;#,##0.00"/>'
            . '</numFmts>'
            . '<fonts count="3">'
            . '<font><sz val="11"/><color theme="1"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="14"/><color theme="1"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="3">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF1D1D1F"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="5">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            . '<xf numFmtId="0" fontId="2" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            . '<xf numFmtId="165" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private function worksheet(array $sheet): string
    {
        $cols = '';
        foreach (array_values($sheet['widths'] ?? []) as $i => $width) {
            $chars = round(max(8, (float) $width) / 7, 2);
            $cols .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $chars . '" customWidth="1"/>';
        }

        $rowXml = '';
        $rowNumber = 0;
        foreach ($sheet['rows'] as $row) {
            $rowNumber++;
            $rowStyle = $row['style'] ?? null;
            $cellsXml = '';
            $colIndex = 0;
            foreach (array_values($row['cells'] ?? []) as $cell) {
                $colIndex++;
                $cellsXml .= $this->cell($this->columnName($colIndex) . $rowNumber, $cell, $rowStyle);
            }
            $rowXml .= '<row r="' . $rowNumber . '">' . $cellsXml . '</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . ($cols !== '' ? '<cols>' . $cols . '</cols>' : '')
            . '<sheetData>' . $rowXml . '</sheetData>'
            . '</worksheet>';
    }

    private function cell(string $ref, mixed $cell, ?string $rowStyle): string
    {
        if (is_array($cell)) {
            $value = $cell['v'] ?? '';
            $isNumber = (bool) ($cell['num'] ?? false);
            $isCurrency = (bool) ($cell['cur'] ?? false);
        } else {
            $value = $cell;
            $isNumber = false;
            $isCurrency = false;
        }

        if ($value === null || $value === '') {
            $style = $this->styleIndex($rowStyle);
            return '<c r="' . $ref . '"' . ($style > 0 ? ' s="' . $style . '"' : '') . '/>';
        }

        if ($isNumber) {
            return '<c r="' . $ref . '" s="' . ($isCurrency ? 4 : 3) . '"><v>' . (0 + $value) . '</v></c>';
        }

        $style = $this->styleIndex($rowStyle);
        return '<c r="' . $ref . '"' . ($style > 0 ? ' s="' . $style . '"' : '') . ' t="inlineStr"><is><t xml:space="preserve">' . $this->xml((string) $value) . '</t></is></c>';
    }

    private function styleIndex(?string $rowStyle): int
    {
        return match ($rowStyle) {
            'sTitle' => 1,
            'sHeader' => 2,
            default => 0,
        };
    }

    private function columnName(int $index): string
    {
        $name = '';
        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)) . $name;
            $index = intdiv($index, 26);
        }

        return $name;
    }

    private function safeSheetName(string $name): string
    {
        $name = str_replace(['\\', '/', '?', '*', '[', ']', ':'], ' ', $name);

        return mb_substr(trim($name), 0, 31);
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
