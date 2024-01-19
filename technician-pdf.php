<?php 
session_start();
define ('PDF_FONT_NAME_MAIN','thsarabun');

// Include the main TCPDF library (search for installation path).
require_once('tcpdf.php');
require_once './config/db.php';

class MYPDF extends TCPDF {

    //Page header
    public function Header() {
        // Logo
        // Set font
        $this->SetFont('thsarabun', 'B', 12);
        // Title
        $this->Cell(0, 15, 'KU-KPS-Dormitory', 0, false, 'L', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('thsarabun', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'หน้า '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}
// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->setCreator(PDF_CREATOR);
$pdf->setAuthor('Siravich Supaveerasathien');
$pdf->setTitle('KUDM-TECHNICIAN-REPORT');
$pdf->setSubject('Summeries');
$pdf->setKeywords('PDF, project, test');

// remove default header/footer
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
// $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH,'ADMIN-REPORT', 'KU-KPS-Dormitory');
$pdf->SetFooterData();
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);

// set default monospaced font
$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------
$team_id = $_SESSION['technician_login'];
try {
	$crquery = "SELECT COUNT(*) as report_count FROM reports";
	$crstmt = $conn->prepare($crquery);
	$crstmt->execute();
	$row = $crstmt->fetch(PDO::FETCH_ASSOC);
	$reportCount = $row['report_count'];

	$waitCount = "SELECT COUNT(*) as wait_count FROM reports WHERE team_id = :team_id AND report_status = 'wait'";
	$waitCountstmt = $conn->prepare($waitCount);
	$waitCountstmt->bindParam(':team_id', $team_id);
	$waitCountstmt->execute();
	$result = $waitCountstmt->fetch(PDO::FETCH_ASSOC);
	$wait_count = $result['wait_count'];

	$inprogressCount = "SELECT COUNT(*) as inprogress_count FROM reports WHERE team_id = :team_id AND report_status = 'in progress'";
	$inprogressCountstmt = $conn->prepare($inprogressCount);
	$inprogressCountstmt->bindParam(':team_id', $team_id);
	$inprogressCountstmt->execute();
	$result = $inprogressCountstmt->fetch(PDO::FETCH_ASSOC);
	$inprogress_count = $result['inprogress_count'];

	$succeedCount = "SELECT COUNT(*) as succeed_count FROM reports WHERE team_id = :team_id AND report_status = 'succeed'";
	$succeedCountstmt = $conn->prepare($succeedCount);
	$succeedCountstmt->bindParam(':team_id', $team_id);
	$succeedCountstmt->execute();
	$result = $succeedCountstmt->fetch(PDO::FETCH_ASSOC);
	$succeed_count = $result['succeed_count'];
} catch (PDOException $e) {
	echo "Error: " . $e->getMessage();
}

$pdf->setFontSubsetting(false);

$pdf->AddPage('A4');

$part1 = <<<EOD
<br> - จำนวนรายการแจ้งซ่อมบำรุงทั้งหมด $reportCount รายการ
<br> - เป็นรายการแจ้งซ่อมบำรุงที่กำลังรอ $wait_count รายการ
<br> - เป็นรายการแจ้งซ่อมบำรุงที่มอบหมาย $inprogress_count รายการ
<br> - เป็นรายการแจ้งซ่อมบำรุงที่เสร็จสิ้นทั้งหมด $succeed_count รายการ
EOD;

$pdf->SetDrawColor(0, 128, 0);
$pdf->SetLineWidth(0.2);
$pdf->Rect(10, 20, 190, 60);

$pdf->setFont('thsarabun', 'B', 20);
$pdf->Write(0, "สรุปผลภาพรวมทั้งหมด", '', 0, 'C', true, 0, false, false, 0);
$pdf->setFont('thsarabun', 'B', 16);
$pdf->Write(0, "รายการแจ้งซ่อมบำรุง", '', 0, '', true, 0, false, false, 0);
$pdf->setFont('thsarabun', '', 16);
$pdf->writeHTMLCell(0, 0, '', '', $part1, 0, 1, 0, true, '', true);

$pdf->Output('KUDM-TECHNICIAN-REPORT.pdf', 'I');

?>