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
$pdf->setTitle('KUDM-ADMIN-REPORT');
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

try {
	$cbquery = "SELECT COUNT(*) as booking_count FROM bookings";
	$cbstmt = $conn->prepare($cbquery);
	$cbstmt->execute();
	$row = $cbstmt->fetch(PDO::FETCH_ASSOC);
	$bookingCount = $row['booking_count'];

	$pendingUsersQuery = "SELECT COUNT(*) as pending_user_count FROM bookings WHERE statuspay = 'pending'";
    $pendingUsersStmt = $conn->prepare($pendingUsersQuery);
	$pendingUsersStmt->execute();
	$pendingUserRow = $pendingUsersStmt->fetch(PDO::FETCH_ASSOC);
	$pendingUserCount = $pendingUserRow['pending_user_count'];

	$confirmedUsersQuery = "SELECT COUNT(*) as confirmed_user_count FROM bookings WHERE statuspay = 'confirmed'";
    $confirmedUsersStmt = $conn->prepare($confirmedUsersQuery);
	$confirmedUsersStmt->execute();
	$confirmedUserRow = $confirmedUsersStmt->fetch(PDO::FETCH_ASSOC);
	$confirmedUserCount = $confirmedUserRow['confirmed_user_count'];

	$crquery = "SELECT COUNT(*) as report_count FROM reports";
	$crstmt = $conn->prepare($crquery);
	$crstmt->execute();
	$row = $crstmt->fetch(PDO::FETCH_ASSOC);
	$reportCount = $row['report_count'];

	$waitReportsQuery = "SELECT COUNT(*) as wait_report_count FROM reports WHERE report_status = 'wait'";
    $waitReportsStmt = $conn->prepare($waitReportsQuery);
	$waitReportsStmt->execute();
	$waitReportRow = $waitReportsStmt->fetch(PDO::FETCH_ASSOC);
	$waitReportCount = $waitReportRow['wait_report_count'];

	$progressReportsQuery = "SELECT COUNT(*) as progress_report_count FROM reports WHERE report_status = 'in progress'";
    $progressReportsStmt = $conn->prepare($progressReportsQuery);
	$progressReportsStmt->execute();
	$progressReportRow = $progressReportsStmt->fetch(PDO::FETCH_ASSOC);
	$progressReportCount = $progressReportRow['progress_report_count'];

	$succeedReportsQuery = "SELECT COUNT(*) as succeed_report_count FROM reports WHERE report_status = 'succeed'";
    $succeedReportsStmt = $conn->prepare($succeedReportsQuery);
	$succeedReportsStmt->execute();
	$succeedReportRow = $succeedReportsStmt->fetch(PDO::FETCH_ASSOC);
	$succeedReportCount = $succeedReportRow['succeed_report_count'];

	$csquery = "SELECT COUNT(*) as material_count FROM materials";
	$csstmt = $conn->prepare($csquery);
	$csstmt->execute();
	$row = $csstmt->fetch(PDO::FETCH_ASSOC);
	$materialCount = $row['material_count'];

	$cuquery = "SELECT COUNT(*) as user_count FROM users";
	$custmt = $conn->prepare($cuquery);
	$custmt->execute();
	$row = $custmt->fetch(PDO::FETCH_ASSOC);
	$userCount = $row['user_count'];

	$teamCountQuery = "SELECT COUNT(*) as team_count FROM teams";
	$teamStmt = $conn->prepare($teamCountQuery);
	$teamStmt->execute();
	$teamRow = $teamStmt->fetch(PDO::FETCH_ASSOC);
	$teamCount = $teamRow['team_count'];

	$totalCount = $userCount + $teamCount;

	$maleTeamQuery = "SELECT COUNT(*) as teammale_count FROM teams WHERE gender = 'M'";
	$femaleTeamQuery = "SELECT COUNT(*) as teamfemale_count FROM teams WHERE gender = 'F'";

	$maleTeamStmt = $conn->prepare($maleTeamQuery);
	$maleTeamStmt->execute();
	$maleTeamRow = $maleTeamStmt->fetch(PDO::FETCH_ASSOC);
	$maleTeamCount = $maleTeamRow['teammale_count'];

	$femaleTeamStmt = $conn->prepare($femaleTeamQuery);
	$femaleTeamStmt->execute();
	$femaleTeamRow = $femaleTeamStmt->fetch(PDO::FETCH_ASSOC);
	$femaleTeamCount = $femaleTeamRow['teamfemale_count'];

	$maleQuery = "SELECT COUNT(*) as male_count FROM users WHERE gender = 'M'";
	$femaleQuery = "SELECT COUNT(*) as female_count FROM users WHERE gender = 'F'";

	$maleStmt = $conn->prepare($maleQuery);
	$maleStmt->execute();
	$maleRow = $maleStmt->fetch(PDO::FETCH_ASSOC);
	$maleCount = $maleRow['male_count'];

	$femaleStmt = $conn->prepare($femaleQuery);
	$femaleStmt->execute();
	$femaleRow = $femaleStmt->fetch(PDO::FETCH_ASSOC);
	$femaleCount = $femaleRow['female_count'];

	$tmquery = "SELECT SUM(ItemQuantity) as total_quantity FROM materials";
    $tmstmt = $conn->prepare($tmquery);
    $tmstmt->execute();
    $row = $tmstmt->fetch(PDO::FETCH_ASSOC);
    $materialTotal = $row['total_quantity'];

	$cwquery = "SELECT COUNT(*) as withdrawals_count FROM withdrawals";
    $cwstmt = $conn->prepare($cwquery);
    $cwstmt->execute();
    $row = $cwstmt->fetch(PDO::FETCH_ASSOC);
    $withdrawalsCounts = $row['withdrawals_count'];

	$cdquery = "SELECT COUNT(*) as dorm_count FROM dorms";
    $cdstmt = $conn->prepare($cdquery);
    $cdstmt->execute();
    $row = $cdstmt->fetch(PDO::FETCH_ASSOC);
    $dormCount = $row['dorm_count'];

	$crquery = "SELECT COUNT(*) as room_count FROM rooms";
    $crstmt = $conn->prepare($crquery);
    $crstmt->execute();
    $row = $crstmt->fetch(PDO::FETCH_ASSOC);
    $roomCount = $row['room_count'];

} catch (PDOException $e) {
	$_SESSION['error'] = 'เกิดข้อผิดพลาดในการดึงข้อมูลผู้ใช้';
	header('location: admin.php');
	exit();
}

$pdf->setFontSubsetting(false);

$pdf->AddPage('A4');

$part1 = <<<EOD
<br>จำนวนทั้งหมด $totalCount คน
<br>เป็นทีมงานทั้งหมด $teamCount คน
<br>เป็นเพศชาย $maleTeamCount คน และเป็นเพศหญิง $femaleTeamCount คน
<br>เป็นสมาชิกทั้งหมด $userCount คน
<br>เป็นเพศชาย $maleCount คน และเป็นเพศหญิง $femaleCount คน
EOD;

$part2 = <<<EOD
<br>จำนวนหอพักทั้งหมด $dormCount ตึก
<br>รวมห้องพักทั้งหมดมี $roomCount ห้อง
EOD;

$part3 = <<<EOD
<br>จำนวนรายการจองห้องพักทั้งหมด $bookingCount รายการ 
<br>เป็นรายการที่รอชำระเงิน $pendingUserCount รายการ 
<br>เป็นรายการที่ชำระเงินแล้ว $confirmedUserCount รายการ
EOD;

$part4 = <<<EOD
<br>จำนวนรายการการแจ้งซ่อมบำรุงทั้งหมด $reportCount รายการ 
<br>เป็นรายการที่รอการยืนยัน $waitReportCount รายการ
<br>เป็นรายการที่กำลังดำเนินการ $progressReportCount รายการ
<br>เป็นรายการที่เสร็จสิ้นแล้ว $succeedReportCount รายการ
EOD;

$part5 = <<<EOD
<br>จำนวนรายการเบิกวัสดุอุปกรณ์ทั้งหมด $withdrawalsCounts รายการ 
<br>และมีวัสดุและอุปกรณ์ทั้งหมด $materialCount รายการ
<br>จำนวน $materialTotal ชิ้น
EOD;

$pdf->SetDrawColor(0, 128, 0);
$pdf->SetLineWidth(0.2);
$pdf->Rect(10, 20, 190, 180);

$pdf->setFont('thsarabun', 'B', 20);
$pdf->Write(0, "สรุปผลภาพรวมทั้งหมด", '', 0, 'C', true, 0, false, false, 0);
$pdf->setFont('thsarabun', 'B', 16);
$pdf->Write(0, "ผู้ใช้งาน", '', 0, '', true, 0, false, false, 0);
$pdf->setFont('thsarabun', '', 16);
$pdf->writeHTMLCell(0, 0, '', '', $part1, 0, 1, 0, true, '', true);
$pdf->setFont('thsarabun', 'B', 16);
$pdf->Write(0, "อาคารและห้องพัก", '', 0, '', true, 0, false, false, 0);
$pdf->setFont('thsarabun', '', 16);
$pdf->writeHTMLCell(0, 0, '', '', $part2, 0, 1, 0, true, '', true);
$pdf->setFont('thsarabun', 'B', 16);
$pdf->Write(0, "รายการจองห้องพัก", '', 0, '', true, 0, false, false, 0);
$pdf->setFont('thsarabun', '', 16);
$pdf->writeHTMLCell(0, 0, '', '', $part3, 0, 1, 0, true, '', true);
$pdf->setFont('thsarabun', 'B', 16);
$pdf->Write(0, "รายการซ่อมบำรุง", '', 0, '', true, 0, false, false, 0);
$pdf->setFont('thsarabun', '', 16);
$pdf->writeHTMLCell(0, 0, '', '', $part4, 0, 1, 0, true, '', true);
$pdf->setFont('thsarabun', 'B', 16);
$pdf->Write(0, "รายการเบิกวัสดุอุปกรณ์", '', 0, '', true, 0, false, false, 0);
$pdf->setFont('thsarabun', '', 16);
$pdf->writeHTMLCell(0, 0, '', '', $part5, 0, 1, 0, true, '', true);

$pdf->Output('KUDM-ADMIN-REPORT.pdf', 'I');

?>