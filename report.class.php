<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Generic report class.
 *
 * @package   report_ncccscensus
 * @author    Justin Filip <jfilip@remote-learner.net>
 * @copyright 2009 Remote Learner - http://www.remote-learner.net/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Generic report class.
 *
 * @copyright 2009 Remote Learner - http://www.remote-learner.net/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_ncccscensus_report {

    /**
     * An array of strings for the top row of headers.
     *
     * @var array
     */
    public $topheaders;

    /**
     * An array of strings for the bottom row of headers.
     *
     * @var array
     */
    public $bottomheaders;

    /**
     * An array of table data.
     *
     * @var array
     */
    public $data;

    /**
     * An array of headers to appear at the top of the report.
     *
     * @var array
     */
    public $top;

    /**
     * A string to appear in the footer.
     *
     * @var string
     */
    public $bottom;

    /**
     * The left margin in inches.
     *
     * @var float
     */
    public $leftmargin;

    /**
     * The right margin in inches.
     *
     * @var float
     */
    public $rightmargin;

    /**
     * The top margin in inches.
     *
     * @var float
     */
    public $topmargin;

    /**
     * The css style string for the table borders.
     *
     * @var string
     */
    public $reportborder;

    /**
     * Whether to display a signature line.
     *
     * @var bool
     */
    public $signatureline;

    /**
     * Whether to display a date line.
     *
     * @var bool
     */
    public $dateline;

    /**
     * The footnote string that appears below the table.
     *
     * @var string
     */
    public $footnote;

    /**
     * The name of the font used in the report.
     *
     * @var string
     */
    public $fontname;

    /**
     * The maximum number of rows to be displayed per page.
     * This can be decremented if the header data runs to extra lines.
     *
     * @var int
     */
    public $maxrowsperpage;

    /**
     * The width of the header image in inches.
     *
     * @var float
     */
    public $imagewidth;

    /**
     * The font size of the certify message that appears on the last page of the report.
     *
     * @var int
     */
    public $certifyfontsize;

    /**
     * The table padding for the report in pixels.
     *
     * @var int
     */
    public $reportpaddingpixels;

    /**
     * The ratio of the student column to the activity column.
     * These are the columns that require the most amount of space, so we allot them the remaining
     * report width according to this ratio.
     *
     * @var float
     */
    public $studentcolratio;

    /**
     * The x position of the certify block in inches.
     *
     * @var float
     */
    public $certifyblockxpos;

    /**
     * The calculated report width in inches.
     *
     * @var float
     */
    public $reportwidth;

    /**
     * The alignment of the lower part of the footer (C, L, or R).
     *
     * @var char
     */
    public $footerbottomhalign;

    /**
     * The alignment of the note in the footer (C, L, or R).
     *
     * @var char
     */
    public $footernotealign;

    /**
     * The size of the default font in the footer.
     *
     * @var int
     */
    public $footerfontsize;

    /**
     * The margin for the footer in inches measured from the bottom (ie. negative value).
     *
     * @var float
     */
    public $footermargin;

    /**
     * The left margin for the legend.
     *
     * @var float
     */
    public $legendmargin;

    /**
     * The font size for the header.
     *
     * @var int
     */
    public $headerfontsize;

    /**
     * The pdf object.
     *
     * @var mixed
     * @see TCPDF
     */
    private $pdf;            // OBJECT - The pdf object.


    /**
     * The constructor which sets defaults values and initializes properties.
     *
     * @return none
     */
    public function __construct() {

        $this->topheaders    = array();
        $this->bottomheaders = array();
        $this->data          = array();
        $this->top           = array();
        $this->bottom        = '';
        $this->leftmargin = $this->rightmargin = 0.73;
        $this->topmargin = 0.59;
        $this->reportborder = '1 solid black';
        $this->signatureline = false;
        $this->dateline      = false;
        $this->footnote = get_string('studentfootnote', 'report_ncccscensus');
        $this->fontname = 'helvetica';
        $this->maxrowsperpage = 20;
        $this->imagewidth = 5;
        $this->certifyfontsize = 10;
        $this->reportpaddingpixels = 2;
        $this->studentcolratio = 0.4; // The student column should be 40% (so activity column is 60%).
        $this->certifyblockxpos = $this->leftmargin + 0.48;
        $this->footerbottomhalign = 'C';
        $this->footernotealign = 'L';
        $this->footerfontsize = 8;
        $this->footermargin = -1.4;
        $this->legendmargin = -4;
        $this->headerfontsize = 10;

        global $CFG;
        require_once('cr2_pdf.php');
        $this->pdf = new CR2_PDF('L', 'in', 'letter');
        $this->pdf->SetFont($this->fontname, '', 9);
        $this->reportwidth = $this->pdf->getPageWidth() - $this->leftmargin - $this->rightmargin;
        $this->pdf->SetX($this->leftmargin, true);

    }

    /**
     * The function which creates and initiates the download of the PDF file.
     *
     * @param string $saveas The filename to save to.
     * @return an empty string if no data is found
     */
    public function download($saveas = false) {

        if (empty($this->data)) {
            return '';
        }

        // Punt values needed by the CR2_PDF class for headers and footers.
        $this->pdf->top                 = $this->top;
        $this->pdf->bottom              = $this->bottom;
        $this->pdf->imagewidth          = $this->imagewidth;
        $this->pdf->leftmargin          = $this->leftmargin;
        $this->pdf->rightmargin         = $this->rightmargin;
        $this->pdf->topmargin           = $this->topmargin;
        $this->pdf->footnote            = $this->footnote;
        $this->pdf->reportpaddingpixels = $this->reportpaddingpixels;
        $this->pdf->reportborder        = $this->reportborder;
        $this->pdf->lastpage            = false;
        $this->pdf->footerbottomhalign  = $this->footerbottomhalign;
        $this->pdf->footernotealign     = $this->footernotealign;
        $this->pdf->footerfontsize      = $this->footerfontsize;
        $this->pdf->footermargin        = $this->footermargin;
        $this->pdf->legendmargin        = $this->legendmargin;
        $this->pdf->headerfontsize      = $this->headerfontsize;

        $this->pdf->AddPage();

        $studentcolspan2 = false;
        $headerwidths = array();
        $colwidths = array();
        $colborders = array();
        $bheaders = array();
        $coltotalwidth = 0;

        // Calculate widths and borders for the second header line.
        foreach ($this->bottomheaders as $id => $bottomheader) {

            // If not showing student ID then we need to span two columns.
            if ($id == 'student' && count($bottomheader) == 1) {
                $studentcolspan2 = true;
                $colwidths[$id.'fullname'] = array('numwidth' => '', 'htmlwidth' => '');
                $colborders[$id.'fullname'] = 'border-left:'.$this->reportborder.';border-right:'.$this->reportborder.';';
                $bheaders[$id.'fullname'] = $bottomheader['fullname'];
                $headerwidths[$id] = 0;
                continue;
            }

            foreach ($bottomheader as $bid => $bheader) {

                // Variable used to calculate required width of certain "fixed width" columns.
                $stringwidth = 0;
                $colwidth = '';

                // Handle the student columns.
                if ($id == 'student' && $bid == 'fullname') {
                    $colborders[$id.$bid] = 'border-left:'.$this->reportborder.';';
                }
                if ($id == 'student' && $bid == 'id') {
                    $stringwidth1 = $this->pdf->GetStringWidth('0000000000');
                    $stringwidth2 = $this->pdf->GetStringWidth(get_string('studentidpdf', 'report_ncccscensus'));
                    $stringwidth = max($stringwidth1, $stringwidth2);
                    $stringwidth += 0.1;
                    $coltotalwidth += $stringwidth;
                    $colwidth = 'width:'.$stringwidth.'in;';
                }

                // Handle the activity columns.
                if ($id == 'activity' && $bid == 'name') {
                    $colborders[$id.$bid] = 'border-left:'.$this->reportborder.';';
                }
                if ($id == 'activity' && $bid == 'module') {
                    $stringwidth = $this->pdf->GetStringWidth(get_string('moduleglossary', 'report_ncccscensus'));
                    $stringwidth += 0.1;
                    $coltotalwidth += $stringwidth;
                    $colwidth = 'width:'.$stringwidth.'in;';
                }

                // Handle the submission columns.
                if ($id == 'submission' && $bid == 'status') {
                    $stringwidth = $this->pdf->GetStringWidth(get_string('submissionstatusinprogress', 'report_ncccscensus'));
                    $stringwidth += 0.1;
                    $coltotalwidth += $stringwidth;
                    $colwidth = 'width:'.$stringwidth.'in;';
                    $colborders[$id.$bid] = 'border-left:'.$this->reportborder.';';
                }
                if ($id == 'submission' && $bid == 'date') {
                    $stringwidth = $this->pdf->GetStringWidth('00/00/00');
                    $stringwidth += 0.1;
                    $coltotalwidth += $stringwidth;
                    $colwidth = 'width:'.$stringwidth.'in;';
                }

                // Handle the grade columns.
                if ($id == 'grade' && $bid == 'grade') {
                    $stringwidth = $this->pdf->GetStringWidth(get_string('nograde', 'report_ncccscensus'));
                    $stringwidth += 0.1;
                    $coltotalwidth += $stringwidth;
                    $colwidth = 'width:'.$stringwidth.'in;';
                    $colborders[$id.$bid] = 'border-left:'.$this->reportborder.';';
                }
                if ($id == 'grade' && $bid == 'date') {
                    $stringwidth = $this->pdf->GetStringWidth(get_string('gradedatepdf', 'report_ncccscensus'));
                    $stringwidth += 0.1;
                    $coltotalwidth += $stringwidth;
                    $colwidth = 'width:'.$stringwidth.'in;';
                    $colborders[$id.$bid] = 'border-right:'.$this->reportborder.';';
                }

                if (!isset($headerwidths[$id])) {
                    $headerwidths[$id] = 0;
                }
                $headerwidths[$id] += $stringwidth;
                $colwidths[$id.$bid] = array('numwidth' => $stringwidth, 'htmlwidth' => $colwidth);
                $bheaders[$id.$bid] = $bheader;

            }
        }

        // Calculate column width for variable length columns.
        $studentcolwidth  = ($this->reportwidth - $coltotalwidth) * $this->studentcolratio;
        $activitycolwidth = ($this->reportwidth - $coltotalwidth) * (1 - $this->studentcolratio);

        // Build the second header row.
        $reporthtml  = '<thead>';
        $reporthtml .= '<tr>';
        foreach ($colwidths as $id => $colwidth) {
            if ($id == 'studentfullname') {
                $colwidths[$id] = array('numwidth' => $studentcolwidth, 'htmlwidth' => 'width:'.$studentcolwidth.'in;');
            }
            if ($id == 'activityname') {
                $colwidths[$id] = array('numwidth' => $activitycolwidth, 'htmlwidth' => 'width:'.$activitycolwidth.'in;');
            }
            $reporthtml .= '<th'.($studentcolspan2 ? ' colspan=2' : '').' ';
            $reporthtml .= 'style="'.$colwidths[$id]['htmlwidth'];
            if (isset($colborders[$id])) {
                $reporthtml .= $colborders[$id];
            }
            $reporthtml .= 'border-bottom:'.$this->reportborder.';';
            $reporthtml .= 'text-align:';
            if ($id == 'studentid' || $id == 'submissiondate' || $id == 'gradedate') {
                $reporthtml .= 'right';
            } else {
                $reporthtml .= 'left';
            }
            $reporthtml .= '">';
            $reporthtml .= '<b>'.$bheaders[$id].'</b>';
            $reporthtml .= '</th>';
        }
        $reporthtml .= '</tr>';
        $reporthtml .= '</thead>';
        $keephtml = $reporthtml;

        // Build the top header rows based on the widths of the previously built second-row headers.
        $reporthtml  = '<table border="" cellpadding="'.$this->reportpaddingpixels.'" style="width:'.$this->reportwidth.'in">';
        $reporthtml .= '<thead>';
        $reporthtml .= '<tr>';
        foreach ($this->topheaders as $id => $topheader) {
            if ($id == 'student') {
                $headerwidths[$id] += $studentcolwidth;
            }
            if ($id == 'activity') {
                $headerwidths[$id] += $activitycolwidth;
            }
            $reporthtml .= '<th colspan="2" ';
            $reporthtml .= 'style="width:'.$headerwidths[$id].'in;';
            $reporthtml .= 'border:'.$this->reportborder.';';
            $reporthtml .= 'text-align:center">';
            $reporthtml .= '<b>'.$topheader.'</b>';
            $reporthtml .= '</th>';
        }
        $reporthtml .= '</tr>';
        $reporthtml .= '</thead>';
        $reporthtml .= $keephtml;
        $tableheader = $reporthtml;

        $numrows = count($this->data);
        $rownum = 0;

        // If the header is taller than expected, adjust allowed max rows per page.
        if ($this->pdf->tallheader) {
            $this->maxrowsperpage -= 1;
        }

        $rowheight = 32;
        $tablemaxheight = 640;

        // Build the report data rows.
        foreach ($this->data as $i => $fielddata) {

            // Manually create new page if need be.
            if ($rownum * $rowheight >= $tablemaxheight) {
                $reporthtml .= '</table>';
                $this->pdf->SetX($this->leftmargin, true);
                $this->pdf->writeHTML($reporthtml, false, false, true);
                $this->pdf->AddPage();
                if ($this->pdf->tallheader) {
                    $this->maxrowsperpage -= 1;
                }
                $reporthtml = $tableheader;
                $rownum = 0;
            } else {
                $rownum += 1;
            }

            $fieldarray = $fielddata['data'];
            $reporthtml .= '<tr bgcolor="#'.((($i % 2) == 0) ? 'dddddd' : 'ffffff').'">';
            $nogradeflag = false;
            foreach ($fieldarray as $fieldname => $fieldvalue) {
                $reporthtml .= '<td'.($studentcolspan2 ? ' colspan=2' : '').' ';

                // Colour status and grade cells if grade overridden or no grade.
                if (($fieldname == 'gradegrade' || $fieldname == 'gradedate') && $fielddata['override']) {
                    $reporthtml .= 'bgcolor="'.get_config('report_ncccscensus', 'gradeoverridecolour').'" ';
                } else if (($fieldname == 'submissionstatus' || $fieldname == 'gradegrade' || $fieldname == 'gradedate')
                        && $fielddata['nograde']) {
                    $reporthtml .= 'bgcolor="'.get_config('report_ncccscensus', 'gradenogradecolour').'" ';
                    $nogradeflag = true;
                }

                // Cell styling: borders, alignment, font.
                $reporthtml .= 'style="'.$colwidths[$fieldname]['htmlwidth'];
                if (isset($colborders[$fieldname])) {
                    $reporthtml .= $colborders[$fieldname];
                }
                if (($i >= $this->maxrowsperpage) || ($numrows == $i + 1)) {
                    $reporthtml .= 'border-bottom:'.$this->reportborder.';';
                }
                if (($fieldname == 'submissionstatus' || $fieldname == 'gradegrade') && $nogradeflag) {
                    $reporthtml .= 'font-weight:bold;';
                }
                if ($fieldname == 'studentid' || $fieldname == 'submissiondate' || $fieldname == 'gradedate') {
                    $reporthtml .= 'text-align:right">';
                } else {
                    $reporthtml .= 'text-align:left">';
                }

                // Truncate field values for student or activity name if required.
                if ($fieldname == 'studentfullname') {
                    $fieldvalue = $this->truncate($fieldvalue, $studentcolwidth);
                } else if ($fieldname == 'activityname') {
                    $fieldvalue = $this->truncate($fieldvalue, $activitycolwidth);
                }

                $reporthtml .= $fieldvalue.'</td>';
            }
            $reporthtml .= '</tr>';
        }

        // Write out the final page.
        $reporthtml .= '</table>';
        $this->pdf->SetX($this->leftmargin, true);
        $this->pdf->writeHTML($reporthtml, false, false, true);

        // If last rows of table fill a page, signature/date lines need to appear on empty next page.
        if ($this->maxrowsperpage - ($rownum - 1) <= 6) {
            $this->pdf->AddPage();
            $this->pdf->lastpage = true;
        } else {
            $this->pdf->lastpage = true;
            $this->pdf->outputfooter($this->pdf->GetY() + 0.1);
        }

        $this->pdf->SetX($this->certifyblockxpos, true);

        $certifiedstring = get_string('certified', 'report_ncccscensus');
        $this->pdf->SetFontSize($this->certifyfontsize - 2);
        $signlinewidth = $this->pdf->GetStringWidth($certifiedstring) + 0.1;
        $this->pdf->SetFontSize($this->certifyfontsize);

        if ($this->signatureline) {
            $signaturestring = get_string('signature', 'report_ncccscensus');
            $leftcellwidth = $this->pdf->GetStringWidth($signaturestring) + 0.15;

            $this->pdf->SetX($this->certifyblockxpos + $leftcellwidth + 0.1, true);
            $this->pdf->SetFontSize($this->certifyfontsize - 2);
            $this->pdf->MultiCell(0, 0.2, $certifiedstring, 0, 'L');
            $this->pdf->Ln();

            $this->pdf->SetX($this->certifyblockxpos, true);
            $this->pdf->SetFontSize($this->certifyfontsize);
            $this->pdf->MultiCell($leftcellwidth, 0.2, $signaturestring, 0, 'R');
            $this->pdf->SetX($this->certifyblockxpos, true);
            $x = $this->pdf->GetX() + $leftcellwidth + 0.05;
            $y = $this->pdf->GetY() - 0.02;
            $this->pdf->line($x, $y, $x + $signlinewidth, $y);
            $this->pdf->Ln();
        }

        if ($this->dateline) {
            $datestring = get_string('date');
            $this->pdf->SetX($this->certifyblockxpos, true);
            if (!$this->signatureline) {
                $leftcellwidth = $this->pdf->GetStringWidth($datestring) + 0.05;
            }
            $this->pdf->MultiCell($leftcellwidth, 0.2, $datestring, 0, 'R');
            $this->pdf->SetX($this->certifyblockxpos, true);
            $x = $this->pdf->GetX() + $leftcellwidth + 0.05;
            $y = $this->pdf->GetY() - 0.02;
            $this->pdf->line($x, $y, $x + $signlinewidth, $y);
        }

        if (!$saveas) {
            $this->pdf->Output($this->filename, 'I');
        } else {
            $this->pdf->Output($saveas, 'F');
        }

    }

    /**
     * This function will truncate long student or activity names.
     *
     * @param string $string The string to truncate.
     * @param float $maxlength The length to truncate to, in inches.
     */
    private function truncate($string, $maxlength) {
        $stringlength = $this->pdf->GetStringWidth($string);
        if ($stringlength > $maxlength) {
            $shrinkratio = $maxlength / $stringlength;
            $stringchars = strlen($string);
            $maxchars = floor($stringchars * $shrinkratio);
            $string = substr($string, 0, $maxchars - 3).'...';
        }
        return $string;
    }

}
