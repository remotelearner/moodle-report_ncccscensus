<?php
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

require_once($CFG->libdir.'/tcpdf/tcpdf.php');

/**
 * Census report pdf class, extends the fast pdf class.
 *
 * @author    Tyler Bannister <tyler.banniser@remote-learner.net>
 * @copyright 2011 Remote Learner - http://www.remote-learner.net/
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CR2_PDF extends TCPDF {

    /**
     * Flag that determines whether to shorten the max rows if the header is tall.
     *
     * @var bool
     */
    public $tallheader;

    /**
     * Override default header (which is blank)
     *
     * @uses $CFG, $DB
     */
    public function Header() {

        global $CFG, $DB;

        // Unit is inches.
        $this->SetY($this->topmargin);
        $this->SetX($this->leftmargin, true);

        // Calculate maximum width for header labels.
        $maxwidthlabel = 0;
        foreach ($this->top as $headerline) {
            if (($labelwidth = $this->GetStringWidth($headerline[0])) > $maxwidthlabel) {
                $maxwidthlabel = $labelwidth + 0.25;
            }
        }

        // Calculate maximum of width for header values.
        $maxwidthvalue = $this->getPageWidth() - $this->leftmargin - $maxwidthlabel - $this->imagewidth - $this->rightmargin - 0.25;

        // Create the header lines.
        $headerlineshtml = '<table border="">';
        foreach ($this->top as $headerline) {
            $headerlineshtml .= '<tr style="line-height:150%">';
            $headerlineshtml .= '<td style="font-weight:bold;font-size:'.$this->headerfontsize.';width:'.$maxwidthlabel.'in">';
            $headerlineshtml .= $headerline[0];
            $headerlineshtml .= '</td>';
            $headerlineshtml .= '<td style="font-size:'.$this->headerfontsize.';width:'.$maxwidthvalue.'in">';
            $headerlineshtml .= $headerline[1];
            $headerlineshtml .= '</td>';
            $headerlineshtml .= '</tr>';
        }
        $headerlineshtml .= '</table>';
        $this->writeHTML($headerlineshtml, false, false, true);
        $saveypos = $this->GetY();

        $curheaderimg = $DB->get_field('config_plugins', 'value',
                array('plugin' => 'report_ncccscensus', 'name' => 'headerimgname'), IGNORE_MISSING);
        if ($curheaderimg !== '' && file_exists($CFG->dataroot.'/report/ncccscensus/pix/header/'.$curheaderimg)) {
            $this->Image($CFG->dataroot.'/report/ncccscensus/pix/header/'.$curheaderimg, $this->getPageWidth()
                    - $this->rightmargin - $this->imagewidth, $this->topmargin, $this->imagewidth, 1, '', '', 'N');
        }

        $this->tMargin = max($this->GetY(), $saveypos) + 0.1;
        $this->tallheader = false;
        if ($this->tMargin > 1.75) {
            $this->tallheader = true;
        }
        $this->SetX($this->leftmargin, true);

    }

    /**
     * Override the default footer (which is blank)
     */
    public function Footer() {

        // Parts of the footer are not included on the last page.
        if (!$this->lastpage) {
            $this->outputfooter($this->footermargin + 0.05);
        }

        // Create the footer message.
        $this->SetY($this->footermargin + 0.8);
        $this->SetFontSize($this->footerfontsize);
        $this->writeHTML($this->bottom, false, false, false, false, $this->footerbottomhalign);

        // Create "Page x of y" text.
        $this->SetY($this->footermargin + 0.92);
        $this->Cell(0, 0.2, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, 0, 'R');

    }

    public function outputfooter($footermargin) {

        // Create the footnote.
        $this->SetY($footermargin);
        $this->SetX($this->leftmargin, true);
        $this->SetFontSize($this->footerfontsize);
        $this->MultiCell(5, 0.2, $this->footnote, 0, $this->footernotealign);

        // Create the legend.
        $legendwidth = -$this->rightmargin - $this->legendmargin;
        $leftcellwidth = $legendwidth * 0.2;
        $rightcellwidth = $legendwidth - $leftcellwidth;
        $this->SetY($footermargin + 0.05);
        $this->SetX($this->legendmargin, true);
        $legendhtml = '<table width="'.$legendwidth.'in" cellpadding="'.$this->reportpaddingpixels.'">';
        $legendhtml .= '<tr>';
        $legendhtml .= '<td colspan="2" style="text-align:center;font-weight:bold;border:'.$this->reportborder.'">';
        $legendhtml .= get_string('legend', 'report_ncccscensus');
        $legendhtml .= '</td>';
        $legendhtml .= '</tr>';
        $legendhtml .= '<tr>';
        $legendhtml .= '<td style="width:'.$leftcellwidth.'in;border-left:'.$this->reportborder.'" ';
        $legendhtml .= 'bgcolor="'.get_config('report_ncccscensus', 'gradeoverridecolour').'">';
        $legendhtml .= '</td>';
        $legendhtml .= '<td style="width:'.$rightcellwidth.'in;border-right:'.$this->reportborder.'">';
        $legendhtml .= 'Graded with override';
        $legendhtml .= '</td>';
        $legendhtml .= '</tr>';
        $legendhtml .= '<tr>';
        $legendhtml .= '<td style="border-bottom:'.$this->reportborder.';border-left:'.$this->reportborder.'" ';
        $legendhtml .= 'bgcolor="'.get_config('report_ncccscensus', 'gradenogradecolour').'">';
        $legendhtml .= '</td>';
        $legendhtml .= '<td style="border-bottom:'.$this->reportborder.';border-right:'.$this->reportborder.'">';
        $legendhtml .= 'Attempt not completed or graded';
        $legendhtml .= '</td>';
        $legendhtml .= '</tr>';
        $legendhtml .= '</table>';
        $this->writeHTML($legendhtml, true, false, true, false, '');

    }

}
